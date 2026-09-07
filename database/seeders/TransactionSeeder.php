<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Laundry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use App\Models\Product;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $laundries = Laundry::all();
        if ($laundries->isEmpty()) {
            $this->command->warn('No laundry found, run LaundryDemoSeeder first.');

            return;
        }

        foreach ($laundries as $laundry) {
            if (Order::where('order_id_laundry', $laundry->laundry_id)->exists()) {
                $this->command->info('Orders already exist for laundry '.$laundry->laundry_nama.', skipped.');

                continue;
            }

            $statuses = OrderStatus::where('order_status_id_laundry', $laundry->laundry_id)->orderBy('order_status_urutan')->get();
            if ($statuses->isEmpty()) {
                $this->command->warn('No order_status found for laundry '.$laundry->laundry_nama.', skipped.');

                continue;
            }
            $statusByName = $statuses->keyBy('order_status_nama');
            $selesai = $statuses->firstWhere('order_status_is_selesai', true);
            $batal = $statuses->firstWhere('order_status_is_batal', true);

            $customers = Customer::where('customer_id_laundry', $laundry->laundry_id)->get();
            $products = Product::where('product_id_laundry', $laundry->laundry_id)->where('product_is_aktif', true)->get();
            $users = DB::table('laundry_user')->where('laundry_id', $laundry->laundry_id)->pluck('user_id');
            $ownerId = $users->first();

            // Hapus transaksi lama demo jika re-seed (opsional: comment jika ingin append)
            // OrderItem::whereIn('order_item_id_order', Order::where('order_id_laundry', $laundry->laundry_id)->pluck('order_id'))->delete();
            // OrderStatusLog::whereIn('order_status_log_id_order', Order::where('order_id_laundry', $laundry->laundry_id)->pluck('order_id'))->delete();
            // Order::where('order_id_laundry', $laundry->laundry_id)->delete();
            // Expense::where('expense_id_laundry', $laundry->laundry_id)->delete();

            $faker = Factory::create('id_ID');

            // 50 orders dalam 30 hari terakhir
            for ($i = 0; $i < 50; $i++) {
                $daysAgo = rand(0, 30);
                $createdAt = now()->subDays($daysAgo)->setTime(rand(8, 19), rand(0, 59));
                $customer = $customers->random();
                $useWalkin = rand(0, 10) < 2; // 20% walk-in

                // 1-4 items per order
                $itemProducts = $products->random(rand(1, 4));
                if (! $itemProducts instanceof Collection) {
                    $itemProducts = collect([$itemProducts]);
                }

                $subtotal = 0;
                $items = [];
                foreach ($itemProducts as $prod) {
                    $qty = $prod->product_satuan->value === 'kg' ? rand(1, 5) : rand(1, 3);
                    $harga = (float) $prod->product_harga_dasar;
                    $sub = $harga * $qty;
                    $subtotal += $sub;
                    $items[] = [
                        'product' => $prod,
                        'qty' => $qty,
                        'harga' => $harga,
                        'subtotal' => $sub,
                    ];
                }

                $total = $subtotal; // diskon diabaikan untuk simplicity
                $code = 'LDY-'.$createdAt->format('Ymd').'-'.str_pad((string) (1000 + $i + rand(1, 9000)), 4, '0', STR_PAD_LEFT);
                // Pastikan unique
                while (Order::where('order_code', $code)->exists()) {
                    $code = 'LDY-'.$createdAt->format('Ymd').'-'.Str::upper(Str::random(4));
                }

                $metodeBayar = collect(['tunai', 'transfer', 'dompet_digital'])->random();
                $metodeAmbil = collect(['antar_toko', 'jemput'])->random();

                // Tentukan status akhir simulasi
                $rand = rand(1, 100);
                if ($rand <= 5) {
                    $finalStatus = $batal;
                } elseif ($rand <= 35) {
                    $finalStatus = $selesai;
                } elseif ($rand <= 55) {
                    $finalStatus = $statusByName['Siap Diambil'] ?? $statuses[4];
                } elseif ($rand <= 75) {
                    $finalStatus = $statusByName['Dalam Proses'] ?? $statuses[2];
                } else {
                    $finalStatus = $statusByName['Diterima'] ?? $statuses[1];
                }

                $orderId = DB::table('order')->insertGetId([
                    'order_id_laundry' => $laundry->laundry_id,
                    'order_code' => $code,
                    'order_id_customer' => $useWalkin ? null : $customer->customer_id,
                    'order_walkin_nama' => $useWalkin ? $faker->name : null,
                    'order_walkin_telepon' => $useWalkin ? '08'.rand(1111111111, 9999999999) : null,
                    'order_id_user' => $ownerId,
                    'order_metode_pengambilan' => $metodeAmbil,
                    'order_alamat_jemput' => $metodeAmbil === 'jemput' ? $faker->address : null,
                    'order_slot_waktu' => $metodeAmbil === 'jemput' ? $createdAt->copy()->addDays(1)->format('Y-m-d H:i:s') : null,
                    'order_metode_pembayaran' => $metodeBayar,
                    'order_catatan' => rand(0, 3) === 0 ? $faker->sentence(6) : null,
                    'order_subtotal' => $subtotal,
                    'order_total' => $total,
                    'order_status_id' => $finalStatus->order_status_id,
                    'order_estimasi_selesai' => $createdAt->copy()->addHours($itemProducts->first()->product_estimasi_jam ?? 48),
                    'offline_uuid' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->copy()->addHours(rand(1, 48)),
                ], 'order_id');

                foreach ($items as $it) {
                    DB::table('order_item')->insert([
                        'order_item_id_order' => $orderId,
                        'order_item_id_product' => $it['product']->product_id,
                        'order_item_nama_product' => $it['product']->product_nama,
                        'order_item_satuan' => $it['product']->product_satuan->value,
                        'order_item_harga' => $it['harga'],
                        'order_item_qty' => $it['qty'],
                        'order_item_subtotal' => $it['subtotal'],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                // Status log: buat jejak dari awal sampai final
                $orderedStatuses = $statuses->where('order_status_is_batal', false)->values();
                $finalIdx = $orderedStatuses->search(fn ($s) => $s->order_status_id === $finalStatus->order_status_id);
                if ($finalStatus->order_status_is_batal) {
                    // batal langsung dari status pertama
                    DB::table('order_status_log')->insert([
                        'order_status_log_id_order' => $orderId,
                        'order_status_log_id_from' => $orderedStatuses[0]->order_status_id,
                        'order_status_log_id_to' => $batal->order_status_id,
                        'order_status_log_id_user' => $ownerId,
                        'order_status_log_keterangan' => 'Pelanggan membatalkan',
                        'created_at' => $createdAt->copy()->addHours(2),
                        'updated_at' => $createdAt->copy()->addHours(2),
                    ]);
                } elseif ($finalIdx !== false && $finalIdx > 0) {
                    $cursorTime = $createdAt->copy();
                    for ($s = 0; $s < $finalIdx; $s++) {
                        $cursorTime = $cursorTime->copy()->addHours(rand(3, 12));
                        DB::table('order_status_log')->insert([
                            'order_status_log_id_order' => $orderId,
                            'order_status_log_id_from' => $orderedStatuses[$s]->order_status_id,
                            'order_status_log_id_to' => $orderedStatuses[$s + 1]->order_status_id,
                            'order_status_log_id_user' => $ownerId,
                            'order_status_log_keterangan' => null,
                            'created_at' => $cursorTime,
                            'updated_at' => $cursorTime,
                        ]);
                    }
                }
            }

            // Expenses 25 data
            $kategoriExp = ['Listrik', 'Air', 'Gaji', 'Sewa', 'Perlengkapan', 'Operasional'];
            for ($i = 0; $i < 25; $i++) {
                $daysAgo = rand(0, 30);
                $tgl = now()->subDays($daysAgo)->toDateString();
                DB::table('expense')->insert([
                    'expense_id_laundry' => $laundry->laundry_id,
                    'expense_nama' => $faker->sentence(3),
                    'expense_kategori' => collect($kategoriExp)->random(),
                    'expense_nominal' => rand(50000, 1500000),
                    'expense_tanggal' => $tgl,
                    'expense_catatan' => $faker->sentence(8),
                    'expense_metode_pembayaran' => collect(['tunai', 'transfer', 'dompet_digital'])->random(),
                    'created_at' => now()->subDays($daysAgo),
                    'updated_at' => now()->subDays($daysAgo),
                ]);
            }

            $this->command->info('Seeded 50 orders + 25 expenses for laundry '.$laundry->laundry_nama);
        }
    }
}
