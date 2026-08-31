<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $laundry = Laundry::first();
        if (! $laundry) {
            $this->command?->warn('No laundry found. Run LaundryDemoSeeder first.');

            return;
        }

        session(['laundry_id' => $laundry->laundry_id]);

        $kategoriPakaian = Kategori::firstOrCreate(['kategori_nama' => 'Pakaian']);
        $kategoriSepatu = Kategori::firstOrCreate(['kategori_nama' => 'Sepatu']);
        $kategoriSelimut = Kategori::firstOrCreate(['kategori_nama' => 'Selimut & Bed Cover']);
        $kategoriGorden = Kategori::firstOrCreate(['kategori_nama' => 'Gorden']);
        $kategoriTas = Kategori::firstOrCreate(['kategori_nama' => 'Tas & Sepatu Premium']);

        $products = [
            // Pakaian
            ['product_nama' => 'Cuci Kiloan', 'product_id_kategori' => $kategoriPakaian->kategori_id, 'product_satuan' => 'kg', 'product_harga_dasar' => 7000, 'product_estimasi_jam' => 48, 'product_deskripsi' => 'Cuci + setrika per kilogram'],
            ['product_nama' => 'Cuci Setrika', 'product_id_kategori' => $kategoriPakaian->kategori_id, 'product_satuan' => 'kg', 'product_harga_dasar' => 5000, 'product_estimasi_jam' => 24, 'product_deskripsi' => 'Cuci + setrika kiloan'],
            ['product_nama' => 'Setrika Saja', 'product_id_kategori' => $kategoriPakaian->kategori_id, 'product_satuan' => 'kg', 'product_harga_dasar' => 3000, 'product_estimasi_jam' => 12, 'product_deskripsi' => 'Setrika tanpa cuci'],
            ['product_nama' => 'Cuci Satuan - Baju', 'product_id_kategori' => $kategoriPakaian->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 5000, 'product_estimasi_jam' => 48, 'product_deskripsi' => 'Cuci + setrika per baju'],
            ['product_nama' => 'Cuci Satuan - Celana', 'product_id_kategori' => $kategoriPakaian->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 6000, 'product_estimasi_jam' => 48, 'product_deskripsi' => 'Cuci + setrika per celana'],
            ['product_nama' => 'Cuci Satuan - Gaun', 'product_id_kategori' => $kategoriPakaian->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 15000, 'product_estimasi_jam' => 72, 'product_deskripsi' => 'Cuci + setrika gaun'],
            ['product_nama' => 'Cuci Satuan - Jas', 'product_id_kategori' => $kategoriPakaian->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 20000, 'product_estimasi_jam' => 72, 'product_deskripsi' => 'Cuci + setrika jas'],
            ['product_nama' => 'Cuci Satuan - Kemeja', 'product_id_kategori' => $kategoriPakaian->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 6000, 'product_estimasi_jam' => 48, 'product_deskripsi' => 'Cuci + setrika kemeja'],

            // Sepatu
            ['product_nama' => 'Cuci Sepatu Standar', 'product_id_kategori' => $kategoriSepatu->kategori_id, 'product_satuan' => 'pasang', 'product_harga_dasar' => 25000, 'product_estimasi_jam' => 72, 'product_deskripsi' => 'Cuci sepatu canvas/kain'],
            ['product_nama' => 'Cuci Sepatu Premium', 'product_id_kategori' => $kategoriSepatu->kategori_id, 'product_satuan' => 'pasang', 'product_harga_dasar' => 40000, 'product_estimasi_jam' => 72, 'product_deskripsi' => 'Cuci sepatu kulit/suede'],
            ['product_nama' => 'Cuci Sneakers', 'product_id_kategori' => $kategoriSepatu->kategori_id, 'product_satuan' => 'pasang', 'product_harga_dasar' => 35000, 'product_estimasi_jam' => 48, 'product_deskripsi' => 'Cuci + deep clean sneakers'],

            // Selimut & Bed Cover
            ['product_nama' => 'Cuci Selimut', 'product_id_kategori' => $kategoriSelimut->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 15000, 'product_estimasi_jam' => 48, 'product_deskripsi' => 'Cuci selimut standar'],
            ['product_nama' => 'Cuci Bed Cover', 'product_id_kategori' => $kategoriSelimut->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 20000, 'product_estimasi_jam' => 48, 'product_deskripsi' => 'Cuci bed cover ukuran queen'],
            ['product_nama' => 'Cuci Boneka', 'product_id_kategori' => $kategoriSelimut->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 10000, 'product_estimasi_jam' => 48, 'product_deskripsi' => 'Cuci boneka ukuran sedang'],

            // Gorden
            ['product_nama' => 'Cuci Gorden Standar', 'product_id_kategori' => $kategoriGorden->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 15000, 'product_estimasi_jam' => 72, 'product_deskripsi' => 'Cuci gorden per panel'],
            ['product_nama' => 'Cuci Gorden Premium', 'product_id_kategori' => $kategoriGorden->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 25000, 'product_estimasi_jam' => 72, 'product_deskripsi' => 'Cuci gorden tebal/double faced'],

            // Tas & Sepatu Premium
            ['product_nama' => 'Cuci Tas', 'product_id_kategori' => $kategoriTas->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 30000, 'product_estimasi_jam' => 96, 'product_deskripsi' => 'Cuci tas canvas/kain'],
            ['product_nama' => 'Cuci Tas Kulit', 'product_id_kategori' => $kategoriTas->kategori_id, 'product_satuan' => 'item', 'product_harga_dasar' => 50000, 'product_estimasi_jam' => 96, 'product_deskripsi' => 'Cuci + treatment tas kulit'],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['product_nama' => $product['product_nama'], 'product_id_kategori' => $product['product_id_kategori']],
                $product
            );
        }

        $this->command?->info('Seeded '.count($products).' products for laundry #'.$laundry->laundry_id);
    }
}
