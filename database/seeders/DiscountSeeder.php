<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Laundry;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $laundry = Laundry::first();
        if (! $laundry) {
            $this->command?->warn('No laundry found. Run LaundryDemoSeeder first.');

            return;
        }

        $discounts = [
            ['discount_nama' => 'Hemat 10%', 'discount_kode' => 'HEMAT10', 'discount_tipe' => 'persen', 'discount_nilai' => 10, 'discount_min_pembelian' => 50000],
            ['discount_nama' => 'Baru 15%', 'discount_kode' => 'BARU15', 'discount_tipe' => 'persen', 'discount_nilai' => 15, 'discount_min_pembelian' => 100000, 'discount_max_diskon' => 30000],
            ['discount_nama' => 'Weekend Diskon', 'discount_kode' => 'WEEKEND', 'discount_tipe' => 'persen', 'discount_nilai' => 10, 'discount_min_pembelian' => 0],
            ['discount_nama' => 'Cashback 5rb', 'discount_kode' => 'CASHBACK5', 'discount_tipe' => 'nominal', 'discount_nilai' => 5000, 'discount_min_pembelian' => 30000],
            ['discount_nama' => 'Grand Opening', 'discount_kode' => 'GRANDOPENING', 'discount_tipe' => 'persen', 'discount_nilai' => 20, 'discount_min_pembelian' => 100000, 'discount_max_diskon' => 50000],
        ];

        foreach ($discounts as $discount) {
            Discount::updateOrCreate(
                ['discount_kode' => $discount['discount_kode'], 'discount_id_laundry' => $laundry->laundry_id],
                array_merge($discount, ['discount_id_laundry' => $laundry->laundry_id, 'discount_is_aktif' => true])
            );
        }

        $this->command?->info('Seeded '.count($discounts).' discounts for laundry #'.$laundry->laundry_id);
    }
}
