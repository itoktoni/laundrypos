<?php

namespace Tests\Feature;

use App\Livewire\Pos\PosTerminal;
use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Order;
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

it('clamps qty between 0.5 and 999 and removes lines', function () {
    [, $product] = posFixture();

    Livewire::test(PosTerminal::class)
        ->call('addToCart', $product->getKey())
        ->call('bumpQty', 0, 5000)
        ->assertSet('cart.0.qty', 999)
        ->call('bumpQty', 0, -100000)
        ->assertSet('cart.0.qty', 0.5)
        ->call('removeLine', 0)
        ->assertSet('cart', []);
});

it('supports decimal qty 2.3 kg and comma input 2,3 with 0.5 step', function () {
    [, $product] = posFixture();

    Livewire::test(PosTerminal::class)
        ->call('addToCart', $product->getKey())
        ->call('setQty', 0, '2,3')
        ->assertSet('cart.0.qty', 2.3)
        ->call('setQty', 0, '2.3')
        ->assertSet('cart.0.qty', 2.3)
        ->call('bumpQty', 0, 0.5)
        ->assertSet('cart.0.qty', 2.8)
        ->call('bumpQty', 0, -0.5)
        ->assertSet('cart.0.qty', 2.3);
});

it('confirms walk-in order via POS and shows QR modal', function () {
    [$user, $product] = posFixture();

    $test = Livewire::actingAs($user)
        ->test(PosTerminal::class)
        ->set('walkinNama', 'Budi')
        ->set('walkinTelepon', '081234567890')
        ->call('addToCart', $product->getKey())
        ->call('confirmOrder');

    $order = Order::latest('order_id')->first();

    $test->assertSet('showQr', true)
        ->assertSet('qrOrderId', (string) $order->getKey())
        ->assertSet('qrOrderCode', $order->order_code)
        ->assertSet('qrTotal', (float) $order->order_total);
});
