<?php

namespace Tests\Feature;

use App\Enums\SatuanEnum;
use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

function makeProductLaundry(): Laundry
{
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    session(['laundry_id' => Laundry::first()->laundry_id]);

    return Laundry::first();
}

it('creates product scoped to laundry with valid data', function () {
    $laundry = makeProductLaundry();
    $kategori = Kategori::create(['kategori_nama' => 'Pakaian']);

    $p = Product::create([
        'product_nama' => 'Cuci Kiloan',
        'product_id_kategori' => $kategori->getKey(),
        'product_satuan' => SatuanEnum::KG,
        'product_harga_dasar' => 7000,
        'product_estimasi_jam' => 48,
    ]);

    expect($p->product_id_laundry)->toBe($laundry->laundry_id)
        ->and(Product::count())->toBe(1);
});

it('rejects invalid product rules', function () {
    makeProductLaundry();
    $kategori = Kategori::create(['kategori_nama' => 'Pakaian']);

    $rules = (new Product)->rules();

    $badPrice = Validator::make(
        ['product_nama' => 'X', 'product_id_kategori' => $kategori->getKey(), 'product_satuan' => 'kg', 'product_harga_dasar' => 0, 'product_estimasi_jam' => 24],
        $rules
    );
    expect($badPrice->fails())->toBeTrue();

    $badEstimasi = Validator::make(
        ['product_nama' => 'X', 'product_id_kategori' => $kategori->getKey(), 'product_satuan' => 'kg', 'product_harga_dasar' => 1000, 'product_estimasi_jam' => 9999],
        $rules
    );
    expect($badEstimasi->fails())->toBeTrue();

    $badSatuan = Validator::make(
        ['product_nama' => 'X', 'product_id_kategori' => $kategori->getKey(), 'product_satuan' => 'meter', 'product_harga_dasar' => 1000, 'product_estimasi_jam' => 24],
        $rules
    );
    expect($badSatuan->fails())->toBeTrue();
});

it('shows product table page', function () {
    makeProductLaundry();
    Auth::login(User::factory()->create(['role' => 'owner', 'verified_at' => now()]));

    $this->get('/product/table')->assertOk();
});
