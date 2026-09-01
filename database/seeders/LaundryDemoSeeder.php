<?php

namespace Database\Seeders;

use App\Enums\SatuanEnum;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaundryDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1 laundry utama (auto-create order_status via Laundry::created hook)
        $laundry = Laundry::firstOrCreate(
            ['laundry_kode' => 'LDRY001'],
            ['laundry_nama' => 'Laundry Bersih Kilat', 'laundry_alamat' => 'Jl. Mawar No. 10, Jakarta', 'laundry_telepon' => '081234567890']
        );

        $laundry2 = Laundry::firstOrCreate(
            ['laundry_kode' => 'LDRY002'],
            ['laundry_nama' => 'Laundry Express 2', 'laundry_alamat' => 'Jl. Melati No. 5, Bekasi', 'laundry_telepon' => '081234567891']
        );

        foreach ([$laundry, $laundry2] as $ld) {
            $this->seedKategoriProducts($ld);
            $this->seedCustomers($ld);
            $this->seedDiscounts($ld);
        }
    }

    private function seedKategoriProducts(Laundry $laundry): void
    {
        $kats = [
            ['Cuci Kering', 'Pakaian harian cuci kering lipat'],
            ['Cuci Setrika', 'Cuci + setrika rapi'],
            ['Setrika Saja', 'Hanya setrika'],
            ['Cuci Karpet', 'Karpet & selimut'],
            ['Satuan Premium', 'Jas, kebaya, gorden'],
        ];

        $kategoriIds = [];
        foreach ($kats as [$nama, $desc]) {
            $k = Kategori::firstOrCreate(
                ['kategori_nama' => $nama, 'kategori_id_laundry' => $laundry->laundry_id],
                ['kategori_deskripsi' => $desc]
            );
            $kategoriIds[$nama] = $k->kategori_id;
        }

        $products = [
            ['Cuci Kering Reguler', 'Cuci Kering', SatuanEnum::KG, 8000, 48],
            ['Cuci Kering Express 1 Hari', 'Cuci Kering', SatuanEnum::KG, 14000, 24],
            ['Cuci Setrika Reguler', 'Cuci Setrika', SatuanEnum::KG, 12000, 72],
            ['Cuci Setrika Express', 'Cuci Setrika', SatuanEnum::KG, 18000, 24],
            ['Setrika Saja', 'Setrika Saja', SatuanEnum::KG, 7000, 48],
            ['Karpet Tipis', 'Cuci Karpet', SatuanEnum::PASANG, 25000, 72],
            ['Karpet Tebal / Selimut', 'Cuci Karpet', SatuanEnum::PASANG, 40000, 96],
            ['Jas / Blazer', 'Satuan Premium', SatuanEnum::ITEM, 35000, 72],
            ['Kebaya', 'Satuan Premium', SatuanEnum::ITEM, 40000, 72],
            ['Gorden', 'Satuan Premium', SatuanEnum::ITEM, 30000, 96],
            ['Bed Cover', 'Cuci Karpet', SatuanEnum::ITEM, 35000, 72],
            ['Sepatu Sneaker', 'Satuan Premium', SatuanEnum::PASANG, 30000, 72],
        ];

        foreach ($products as [$nama, $kat, $satuan, $harga, $jam]) {
            Product::firstOrCreate(
                ['product_nama' => $nama, 'product_id_laundry' => $laundry->laundry_id],
                [
                    'product_id_kategori' => $kategoriIds[$kat],
                    'product_satuan' => $satuan,
                    'product_harga_dasar' => $harga,
                    'product_estimasi_jam' => $jam,
                    'product_deskripsi' => $nama . ' - ' . $laundry->laundry_nama,
                    'product_is_aktif' => true,
                ]
            );
        }
    }

    private function seedCustomers(Laundry $laundry): void
    {
        $customers = [
            ['Budi Santoso', '081211112222', 'budi@test.com'],
            ['Siti Aminah', '081211113333', 'siti@test.com'],
            ['Rina Wati', '081211114444', null],
            ['Joko Anwar', '081211115555', null],
            ['Dewi Lestari', '081211116666', 'dewi@test.com'],
            ['Agus Prasetyo', '081211117777', null],
            ['Maya Sari', '081211118888', null],
            ['Hendro Wijaya', '081211119999', null],
        ];

        foreach ($customers as [$nama, $telp, $email]) {
            Customer::firstOrCreate(
                ['customer_telepon' => $telp, 'customer_id_laundry' => $laundry->laundry_id],
                ['customer_nama' => $nama, 'customer_email' => $email, 'customer_alamat' => 'Jl. Contoh No. ' . rand(1, 100)]
            );
        }
    }

    private function seedDiscounts(Laundry $laundry): void
    {
        $discounts = [
            ['Promo Gajian 10%', 'GAJIAN10', 'persen', 10, 50000, 15000],
            ['Potongan 5K', 'HEMAT5K', 'nominal', 5000, 30000, null],
            ['Member 15%', 'MEMBER15', 'persen', 15, 0, 25000],
        ];

        foreach ($discounts as [$nama, $kode, $tipe, $nilai, $min, $max]) {
            Discount::firstOrCreate(
                ['discount_kode' => $kode, 'discount_id_laundry' => $laundry->laundry_id],
                [
                    'discount_nama' => $nama,
                    'discount_tipe' => $tipe,
                    'discount_nilai' => $nilai,
                    'discount_min_pembelian' => $min,
                    'discount_max_diskon' => $max,
                    'discount_mulai' => now()->subDays(5),
                    'discount_selesai' => now()->addDays(30),
                    'discount_is_aktif' => true,
                ]
            );
        }
    }
}
