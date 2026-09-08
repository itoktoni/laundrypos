<?php

namespace App\Actions;

use App\Enums\MetodePengambilanEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrderAction
{
    use AsAction;

    /**
     * @param  array  $data  keys: customer_id|null, walkin_nama, walkin_telepon,
     *                       save_walkin_customer(bool), metode_pengambilan, alamat_jemput, slot_waktu,
     *                       metode_pembayaran, catatan, items=[[product_id, qty], ...], user_id(optional)
     */
    public function handle(array $data): Order
    {
        $this->validate($data);

        // Retry on order_code unique collision (max 3 attempts).
        $attempts = 0;
        beginning:
        try {
            return DB::transaction(function () use ($data) {
                $customerId = ! empty($data['customer_id']) ? (int) $data['customer_id'] : null;

                if ($customerId === null && ! empty($data['save_walkin_customer'])) {
                    $customer = Customer::create([
                        'customer_nama' => $data['walkin_nama'],
                        'customer_telepon' => preg_replace('/[^0-9]/', '', (string) $data['walkin_telepon']),
                    ]);
                    $customerId = $customer->getKey();
                }

                $productIds = collect($data['items'])->pluck('product_id')->all();
                $products = Product::whereIn('product_id', $productIds)->get()->keyBy('product_id');

                $lines = [];
                $subtotal = 0.0;
                $estimasiJam = 0;
                foreach ($data['items'] as $item) {
                    $product = $products[$item['product_id']];
                    $qty = normalizeQty($item['qty'], 0) ?? 0;
                    $qty = round($qty, 3);
                    $lineSubtotal = round((float) $product->product_harga_dasar * $qty, 2);
                    $subtotal += $lineSubtotal;
                    $estimasiJam += (int) $product->product_estimasi_jam;

                    $lines[] = [
                        'order_item_id_product' => $product->getKey(),
                        'order_item_nama_product' => $product->product_nama,
                        'order_item_satuan' => (string) $product->product_satuan,
                        'order_item_harga' => $product->product_harga_dasar,
                        'order_item_qty' => $qty,
                        'order_item_subtotal' => $lineSubtotal,
                    ];
                }

                $initialStatus = OrderStatus::orderBy('order_status_urutan')->first();

                $order = Order::create([
                    'order_code' => Order::generateCode(),
                    'order_id_customer' => $customerId,
                    'order_walkin_nama' => $customerId === null ? $data['walkin_nama'] : null,
                    'order_walkin_telepon' => $customerId === null ? $data['walkin_telepon'] : null,
                    'order_id_user' => $data['user_id'] ?? Auth::id(),
                    'order_metode_pengambilan' => $data['metode_pengambilan'],
                    'order_alamat_jemput' => $data['alamat_jemput'] ?? null,
                    'order_slot_waktu' => $data['slot_waktu'] ?? null,
                    'order_metode_pembayaran' => $data['metode_pembayaran'],
                    'order_catatan' => $data['catatan'] ?? null,
                    'order_subtotal' => max(0.01, round($subtotal, 2)),
                    'order_id_discount' => $data['discount_id'] ?? null,
                    'order_diskon' => round($data['diskon'] ?? 0, 2),
                    'order_total' => max(0.01, round($subtotal - ($data['diskon'] ?? 0), 2)),
                    'order_status_id' => $initialStatus->getKey(),
                    'order_estimasi_selesai' => now()->addHours(max(1, $estimasiJam)),
                ]);

                foreach ($lines as &$line) {
                    $line['order_item_id_order'] = $order->getKey();
                }
                unset($line);
                DB::table('order_item')->insert(array_map(function ($line) {
                    return array_merge($line, ['created_at' => now(), 'updated_at' => now()]);
                }, $lines));

                OrderStatusLog::create([
                    'order_status_log_id_order' => $order->getKey(),
                    'order_status_log_id_from' => null,
                    'order_status_log_id_to' => $initialStatus->getKey(),
                    'order_status_log_id_user' => $data['user_id'] ?? Auth::id(),
                    'order_status_log_keterangan' => null,
                ]);

                return $order;
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062 && str_contains($e->getMessage(), 'order_code') && $attempts < 3) {
                $attempts++;
                goto beginning;
            }

            throw $e;
        }
    }

    private function validate(array $data): void
    {
        $errors = [];

        $items = $data['items'] ?? [];
        if (count($items) < 1) {
            $errors['items'] = ['Minimal 1 produk harus dipilih.'];
        }

        $isJemput = ($data['metode_pengambilan'] ?? '') === MetodePengambilanEnum::JEMPUT;
        if ($isJemput) {
            if (empty($data['alamat_jemput'])) {
                $errors['alamat_jemput'] = ['Alamat penjemputan wajib diisi.'];
            } elseif (mb_strlen((string) $data['alamat_jemput']) > 255) {
                $errors['alamat_jemput'] = ['Alamat maksimal 255 karakter.'];
            }
            if (empty($data['slot_waktu'])) {
                $errors['slot_waktu'] = ['Slot waktu penjemputan wajib diisi.'];
            }
        }

        if (empty($data['customer_id'])) {
            if (empty($data['walkin_nama'])) {
                $errors['walkin_nama'] = ['Nama pelanggan wajib diisi.'];
            }
            if (empty($data['walkin_telepon'])) {
                $errors['walkin_telepon'] = ['Telepon pelanggan wajib diisi.'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        // Validate products exist, are active, and belong to current laundry (via global scope).
        $productIds = collect($items)->pluck('product_id')->filter()->unique()->all();
        $found = Product::where('product_is_aktif', true)->whereIn('product_id', $productIds)->pluck('product_id')->all();
        foreach ($items as $index => $item) {
            if (! in_array($item['product_id'], $found)) {
                $errors["items.{$index}"] = ['Produk tidak valid atau tidak aktif.'];

                continue;
            }
            $rawQty = $item['qty'] ?? null;
            $qty = normalizeQty($rawQty);
            if ($qty === null || $qty < 0.001 || $qty > 999) {
                $errors["items.{$index}.qty"] = ['Jumlah harus antara 0.001-999.'];
            } elseif (round($qty, 3) != $qty) {
                // Cek maksimal 3 desimal
                $parts = explode('.', (string) $qty);
                if (isset($parts[1]) && strlen(rtrim($parts[1], '0')) > 3) {
                    $errors["items.{$index}.qty"] = ['Jumlah maksimal 3 angka di belakang koma.'];
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
