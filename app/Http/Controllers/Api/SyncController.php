<?php

namespace App\Http\Controllers\Api;

use App\Enums\SatuanEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\SyncOrdersRequest;
use App\Models\Customer;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    private function getLaundryId(): int
    {
        return Auth::user()->laundry_id ?? Auth::user()->currentLaundry->laundry_id ?? 0;
    }

    public function pullProducts(): JsonResponse
    {
        $laundryId = $this->getLaundryId();

        $products = Product::where('order_id_laundry', $laundryId)
            ->select([
                'product_id', 'product_nama', 'product_harga_dasar',
                'product_id_kategori', 'product_satuan', 'product_estimasi_jam',
                'product_deskripsi', 'product_is_aktif', 'updated_at',
            ])
            ->get()
            ->map(function ($p) {
                return [
                    'product_id' => $p->product_id,
                    'product_nama' => $p->product_nama,
                    'product_harga_jual' => (float) $p->product_harga_dasar,
                    'product_id_kategori' => $p->product_id_kategori,
                    'product_satuan' => $p->product_satuan,
                    'product_estimasi_jam' => $p->product_estimasi_jam,
                    'product_deskripsi' => $p->product_deskripsi,
                    'product_is_aktif' => $p->product_is_aktif,
                    'updated_at' => $p->updated_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'products' => $products,
            'last_sync_at' => now()->toIso8601String(),
            'sync_version' => $products->max('product_id'),
        ]);
    }

    public function pullCategories(): JsonResponse
    {
        $laundryId = $this->getLaundryId();

        $categories = Kategori::where('kategori_id_laundry', $laundryId)
            ->select(['kategori_id', 'kategori_nama', 'updated_at'])
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->kategori_id,
                    'name' => $c->kategori_nama,
                    'updated_at' => $c->updated_at?->toIso8601String(),
                ];
            });

        return response()->json(['categories' => $categories]);
    }

    public function pullSatuan(): JsonResponse
    {
        $satuan = collect(SatuanEnum::cases())->map(fn ($s) => [
            'id' => $s->value,
            'name' => $s->label,
            'updated_at' => null,
        ]);

        return response()->json(['satuan' => $satuan]);
    }

    public function pullCustomers(): JsonResponse
    {
        $laundryId = $this->getLaundryId();

        $customers = Customer::where('customer_id_laundry', $laundryId)
            ->select(['customer_id', 'customer_nama', 'customer_telepon', 'customer_alamat', 'updated_at'])
            ->get();

        return response()->json(['customers' => $customers]);
    }

    public function syncStatus(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'last_sync_at' => now()->toIso8601String(),
            'sync_version' => Product::where('order_id_laundry', $user->laundry_id ?? 0)->max('product_id'),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function pushOrders(SyncOrdersRequest $request): JsonResponse
    {
        $user = Auth::user();
        $laundryId = $user->laundry_id ?? $user->currentLaundry?->laundry_id ?? 0;

        if (! $laundryId) {
            return response()->json(['error' => 'No laundry context'], 400);
        }

        // Get default "Menunggu Konfirmasi" status for this laundry
        $defaultStatus = OrderStatus::where('order_id_laundry', $laundryId)
            ->orderBy('order_status_urutan')
            ->first();

        if (! $defaultStatus) {
            return response()->json(['error' => 'No order status configured'], 500);
        }

        $synced = [];
        $conflicts = [];
        $failed = [];

        foreach ($request->orders as $orderData) {
            try {
                // UUID dedup check
                $existing = Order::where('offline_uuid', $orderData['id'])->first();
                if ($existing) {
                    $synced[] = [
                        'client_id' => $orderData['id'],
                        'server_order_id' => $existing->order_id,
                        'status' => 'already_synced',
                    ];

                    continue;
                }

                DB::beginTransaction();

                // Generate order code
                $order = new Order;
                $orderCode = $order->generateCode();

                // Calculate subtotal and total
                $subtotal = collect($orderData['items'])->sum(fn ($item) => $item['subtotal'] ?? ($item['price'] * $item['qty']));
                $discount = $orderData['discount'] ?? 0;
                $total = $subtotal - $discount;

                // Map payment method
                $paymentMethod = $orderData['payment_method'] ?? 'tunai';

                // Create order - bypass BelongsToLaundry trait by setting attributes directly
                $order = Order::create([
                    'order_id_laundry' => $laundryId,
                    'order_code' => $orderCode,
                    'order_id_customer' => $orderData['customer_id'] ?? null,
                    'order_id_user' => $user->id,
                    'order_metode_pembayaran' => $paymentMethod,
                    'order_catatan' => $orderData['notes'] ?? null,
                    'order_subtotal' => $subtotal,
                    'order_total' => $total,
                    'order_status_id' => $defaultStatus->order_status_id,
                    'offline_uuid' => $orderData['id'],
                ]);

                // Create order items
                foreach ($orderData['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    $satuan = $product?->product_satuan ?? 'item';

                    OrderItem::create([
                        'order_item_id_order' => $order->order_id,
                        'order_item_id_product' => $item['product_id'],
                        'order_item_nama_product' => $item['product_nama'],
                        'order_item_satuan' => $satuan,
                        'order_item_harga' => $item['price'],
                        'order_item_qty' => $item['qty'],
                        'order_item_subtotal' => $item['subtotal'] ?? ($item['price'] * $item['qty']),
                    ]);
                }

                DB::commit();

                $synced[] = [
                    'client_id' => $orderData['id'],
                    'server_order_id' => $order->order_id,
                    'status' => 'created',
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                $failed[] = [
                    'client_id' => $orderData['id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'synced' => $synced,
            'conflicts' => $conflicts,
            'failed' => $failed,
        ]);
    }
}
