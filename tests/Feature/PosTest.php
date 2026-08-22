<?php

namespace Tests\Feature;

use App\Livewire\Pos\PosTerminal;
use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

function posFixture(): array
{
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    session(['laundry_id' => Laundry::first()->laundry_id]);
    $user = User::factory()->create(['role' => 'owner', 'verified_at' => now()]);
    $kategori = Kategori::create(['kategori_nama' => 'Pakaian']);
    $product = Product::create([
        'product_nama' => 'Cuci Kiloan',
        'product_id_kategori' => $kategori->getKey(),
        'product_satuan' => 'kg',
        'product_harga_dasar' => 7000,
        'product_estimasi_jam' => 48,
    ]);

    return [$user, $product];
}

it('adds products to cart and merges duplicates', function () {
    [, $product] = posFixture();

    Livewire::test(PosTerminal::class)
        ->call('addToCart', $product->getKey())
        ->call('addToCart', $product->getKey())
        ->assertSet('cart.0.qty', 2);
});

it('clamps qty between 1 and 999 and removes lines', function () {
    [, $product] = posFixture();

    Livewire::test(PosTerminal::class)
        ->call('addToCart', $product->getKey())
        ->call('bumpQty', 0, 5000)
        ->assertSet('cart.0.qty', 999)
        ->call('bumpQty', 0, -100000)
        ->assertSet('cart.0.qty', 1)
        ->call('removeLine', 0)
        ->assertSet('cart', []);
});

it('confirms walk-in order via POS and redirects to struk', function () {
    [$user, $product] = posFixture();

    Livewire::actingAs($user)
        ->test(PosTerminal::class)
        ->set('walkinMode', true)
        ->set('walkinNama', 'Budi')
        ->set('walkinTelepon', '081234567890')
        ->call('addToCart', $product->getKey())
        ->call('confirmOrder')
        ->assertRedirect(route('order.getShow', ['id' => 1]));
});
