<?php

namespace Database\Seeders;

use App\Models\Laundry;
use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['order_status_nama' => 'Menunggu Konfirmasi', 'order_status_urutan' => 1, 'order_status_warna' => '#f59e0b'],
            ['order_status_nama' => 'Diterima', 'order_status_urutan' => 2, 'order_status_warna' => '#3b82f6'],
            ['order_status_nama' => 'Dalam Proses', 'order_status_urutan' => 3, 'order_status_warna' => '#8b5cf6'],
            ['order_status_nama' => 'Selesai Dicuci', 'order_status_urutan' => 4, 'order_status_warna' => '#06b6d4'],
            ['order_status_nama' => 'Siap Diambil', 'order_status_urutan' => 5, 'order_status_warna' => '#10b981'],
            ['order_status_nama' => 'Selesai', 'order_status_urutan' => 6, 'order_status_warna' => '#22c55e', 'order_status_is_selesai' => true],
            ['order_status_nama' => 'Dibatalkan', 'order_status_urutan' => 7, 'order_status_warna' => '#ef4444', 'order_status_is_batal' => true],
        ];

        foreach (Laundry::all() as $laundry) {
            foreach ($statuses as $i => $status) {
                OrderStatus::updateOrCreate(
                    [
                        'order_status_id_laundry' => $laundry->laundry_id,
                        'order_status_urutan' => $status['order_status_urutan'],
                    ],
                    array_merge($status, [
                        'order_status_id_laundry' => $laundry->laundry_id,
                        'order_status_is_batal' => $status['order_status_is_batal'] ?? false,
                        'order_status_is_selesai' => $status['order_status_is_selesai'] ?? false,
                    ])
                );
            }
        }

        $this->command?->info('Seeded '.count($statuses).' order statuses for all laundries.');
    }
}
