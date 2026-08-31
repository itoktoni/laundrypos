<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LaundryDemoSeeder extends Seeder
{
    public function run(): void
    {
        $laundry = Laundry::firstOrCreate(
            ['laundry_kode' => 'LDY01'],
            [
                'laundry_nama' => 'Laundry Bersih',
                'laundry_alamat' => 'Jl. Merdeka 123',
                'laundry_telepon' => '08123456789',
            ]
        );

        $owner = User::firstOrCreate(
            ['email' => 'owner@laundry.test'],
            [
                'name' => 'Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'verified_at' => now(),
            ]
        );

        $laundry->hasUsers()->syncWithoutDetaching([$owner->id]);

        // Tenant scoping reads from session; provide it for the seeder context.
        session(['laundry_id' => $laundry->laundry_id]);

        $kategori = Kategori::firstOrCreate(['kategori_nama' => 'Pakaian']);
        Kategori::firstOrCreate(['kategori_nama' => 'Sepatu']);

        Product::firstOrCreate(
            ['product_nama' => 'Cuci Kiloan', 'product_id_kategori' => $kategori->getKey()],
            [
                'product_satuan' => 'kg',
                'product_harga_dasar' => 7000,
                'product_estimasi_jam' => 48,
            ]
        );
        Product::firstOrCreate(
            ['product_nama' => 'Cuci Sepatu', 'product_id_kategori' => Kategori::where('kategori_nama', 'Sepatu')->first()->getKey()],
            [
                'product_satuan' => 'pasang',
                'product_harga_dasar' => 25000,
                'product_estimasi_jam' => 72,
            ]
        );

        $this->command?->info("Seeded laundry #{$laundry->laundry_id} + owner@laundry.test / password");
    }
}
