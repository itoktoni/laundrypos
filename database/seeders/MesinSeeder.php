<?php

namespace Database\Seeders;

use App\Models\Laundry;
use App\Models\Mesin;
use Illuminate\Database\Seeder;

class MesinSeeder extends Seeder
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
        $units = [
            ['kode' => 'WSH-01', 'nama' => 'Washer LG 15kg Depan', 'jenis' => 'washer', 'merk' => 'LG', 'kapasitas' => '15 kg', 'harga' => 18000000, 'umur' => 5, 'residu' => 1000000, 'beli_bln_lalu' => 26, 'interval' => 90],
            ['kode' => 'WSH-02', 'nama' => 'Washer Samsung 14kg Atas', 'jenis' => 'washer', 'merk' => 'Samsung', 'kapasitas' => '14 kg', 'harga' => 15000000, 'umur' => 5, 'residu' => 1000000, 'beli_bln_lalu' => 14, 'interval' => 90],
            ['kode' => 'DRY-01', 'nama' => 'Dryer Electrolux 10kg', 'jenis' => 'dryer', 'merk' => 'Electrolux', 'kapasitas' => '10 kg', 'harga' => 12000000, 'umur' => 5, 'residu' => 0, 'beli_bln_lalu' => 14, 'interval' => 90],
            ['kode' => 'DRY-02', 'nama' => 'Dryer Modena 8kg', 'jenis' => 'dryer', 'merk' => 'Modena', 'kapasitas' => '8 kg', 'harga' => 9500000, 'umur' => 5, 'residu' => 0, 'beli_bln_lalu' => 6, 'interval' => 90],
            ['kode' => 'STR-01', 'nama' => 'Setrika Uap Laundry 3L', 'jenis' => 'setrika', 'merk' => 'Nankai', 'kapasitas' => '3 L', 'harga' => 2500000, 'umur' => 4, 'residu' => 0, 'beli_bln_lalu' => 10, 'interval' => 60],
            ['kode' => 'BLR-01', 'nama' => 'Boiler Gas 15L', 'jenis' => 'boiler', 'merk' => 'Wipro', 'kapasitas' => '15 L', 'harga' => 6500000, 'umur' => 6, 'residu' => 500000, 'beli_bln_lalu' => 20, 'interval' => 30],
        ];

        foreach ($units as $unit) {
            $mesin = Mesin::firstOrCreate(
                ['mesin_kode' => $unit['kode'], 'mesin_id_laundry' => $laundry->laundry_id],
                [
                    'mesin_nama' => $unit['nama'],
                    'mesin_jenis' => $unit['jenis'],
                    'mesin_merk' => $unit['merk'],
                    'mesin_kapasitas' => $unit['kapasitas'],
                    'mesin_harga' => $unit['harga'],
                    'mesin_umur_tahun' => $unit['umur'],
                    'mesin_nilai_residu' => $unit['residu'],
                    'mesin_tanggal_beli' => now()->subMonths($unit['beli_bln_lalu'])->toDateString(),
                    'mesin_interval_hari' => $unit['interval'],
                ]
            );

            // Riwayat service demo (idempotent): 1 rutin lampau + perbaikan.
            if ($mesin->hasServices()->count() > 0) {
                continue;
            }

            $now = now();
            $mesin->hasServices()->create([
                'service_tanggal' => $now->copy()->subDays($unit['interval'] + 10)->toDateString(),
                'service_jenis' => 'rutin',
                'service_tindakan' => 'Pembersihan filter + cek vanbelt',
                'service_teknisi' => 'Budi Santoso',
                'service_biaya' => 150000,
                'service_is_selesai' => true,
            ]);
            $mesin->hasServices()->create([
                'service_tanggal' => $now->copy()->subDays(12)->toDateString(),
                'service_jenis' => 'perbaikan',
                'service_keluhan' => 'Suara berisik saat spin',
                'service_tindakan' => 'Ganti bearing + balancing tabung',
                'service_teknisi' => 'Agus Wijaya',
                'service_biaya' => 750000,
                'service_is_selesai' => true,
            ]);
        }

        // Satu contoh WO darurat open di WSH-02.
        $rusak = Mesin::where('mesin_kode', 'WSH-02')->first();
        if ($rusak && $rusak->hasServices()->where('service_is_selesai', false)->count() === 0) {
            $rusak->update(['mesin_status' => 'rusak']);
            $rusak->hasServices()->create([
                'service_tanggal' => now()->toDateString(),
                'service_jenis' => 'darurat',
                'service_keluhan' => 'Tidak berputar, bau gosong dari dinamo',
                'service_is_selesai' => false,
            ]);
        }

        $this->command?->info('Seeded mesin with service history for laundry #'.$laundry->laundry_id.' ('.$laundry->laundry_nama.')');
    }
}
