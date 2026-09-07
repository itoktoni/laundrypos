<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Laundry;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $laundries = Laundry::all();
        if ($laundries->isEmpty()) {
            $this->command?->warn('No laundry found. Run LaundryDemoSeeder first.');

            return;
        }

        foreach ($laundries as $laundry) {
            session(['laundry_id' => $laundry->laundry_id]);
            $this->seedForLaundry($laundry);
        }
    }

    private function seedForLaundry(Laundry $laundry): void
    {
        $items = [
            ['nama' => 'Deterjen Rinso Cair', 'satuan' => 'jerigen', 'harga' => 85000, 'min' => 2, 'awal' => 8, 'awal_hari_lalu' => 60],
            ['nama' => 'Deterjen Bubuk Attack', 'satuan' => 'pack', 'harga' => 42000, 'min' => 5, 'awal' => 20, 'awal_hari_lalu' => 60],
            ['nama' => 'Pewangi Downy', 'satuan' => 'botol', 'harga' => 28000, 'min' => 6, 'awal' => 24, 'awal_hari_lalu' => 45],
            ['nama' => 'Pelembut Molto', 'satuan' => 'botol', 'harga' => 25000, 'min' => 6, 'awal' => 24, 'awal_hari_lalu' => 45],
            ['nama' => 'Pemutih Bayclin', 'satuan' => 'botol', 'harga' => 22000, 'min' => 4, 'awal' => 12, 'awal_hari_lalu' => 30],
            ['nama' => 'Plastik Packing L', 'satuan' => 'pack', 'harga' => 35000, 'min' => 10, 'awal' => 50, 'awal_hari_lalu' => 30],
            ['nama' => 'Plastik Packing XL', 'satuan' => 'pack', 'harga' => 45000, 'min' => 10, 'awal' => 40, 'awal_hari_lalu' => 30],
            ['nama' => 'Hanger Kawat', 'satuan' => 'pcs', 'harga' => 1500, 'min' => 100, 'awal' => 500, 'awal_hari_lalu' => 20],
        ];

        foreach ($items as $item) {
            $inventory = Inventory::firstOrCreate(
                ['inventory_nama' => $item['nama'], 'inventory_id_laundry' => $laundry->laundry_id],
                [
                    'inventory_satuan' => $item['satuan'],
                    'inventory_harga' => $item['harga'],
                    'inventory_min_stok' => $item['min'],
                ]
            );

            // Riwayat demo: stok awal + 1 pembelian + 2 pemakaian (idempotent).
            if ($inventory->hasMovements()->count() > 0) {
                continue;
            }

            $now = now();
            $rows = [
                ['tipe' => 'masuk', 'qty' => $item['awal'], 'nominal' => $item['awal'] * $item['harga'], 'tgl' => $now->copy()->subDays($item['awal_hari_lalu']), 'ket' => 'Stok awal'],
                ['tipe' => 'keluar', 'qty' => (int) ceil($item['awal'] * 0.3), 'nominal' => 0, 'tgl' => $now->copy()->subDays(15), 'ket' => 'Pemakaian operasional'],
                ['tipe' => 'masuk', 'qty' => (int) ceil($item['awal'] * 0.5), 'nominal' => (int) ceil($item['awal'] * 0.5) * $item['harga'], 'tgl' => $now->copy()->subDays(7), 'ket' => 'Pembelian supplier'],
                ['tipe' => 'keluar', 'qty' => (int) ceil($item['awal'] * 0.2), 'nominal' => 0, 'tgl' => $now->copy()->subDays(2), 'ket' => 'Pemakaian operasional'],
            ];

            foreach ($rows as $row) {
                $inventory->hasMovements()->create([
                    'movement_tanggal' => $row['tgl']->toDateString(),
                    'movement_tipe' => $row['tipe'],
                    'movement_uom' => $item['satuan'],
                    'movement_qty' => max($row['qty'], 1),
                    'movement_nominal' => $row['nominal'],
                    'movement_keterangan' => $row['ket'],
                ]);
            }
        }

        $this->command?->info('Seeded inventory items with movements for laundry #'.$laundry->laundry_id.' ('.$laundry->laundry_nama.')');
    }
}
