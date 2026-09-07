<?php

namespace Tests\Feature;

use App\Actions\CreateOrderAction;
use App\Models\Customer;
use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

function orderFixture(): array
{
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    $laundry = Laundry::first();
    session(['laundry_id' => $laundry->laundry_id]);
    $user = User::factory()->create(['role' => 'owner']);
    $kategori = Kategori::create(['kategori_nama' => 'Pakaian']);
    $product = Product::create([
        'product_nama' => 'Cuci Kiloan',
        'product_id_kategori' => $kategori->getKey(),
        'product_satuan' => 'kg',
        'product_harga_dasar' => 7000,
        'product_estimasi_jam' => 48,
    ]);

    return [$laundry, $user, $product];
}

it('creates walk-in order and saves customer when requested', function () {
    [$laundry, $user, $product] = orderFixture();

    $order = CreateOrderAction::run([
        'walkin_nama' => 'Budi',
        'walkin_telepon' => '081234567890',
        'save_walkin_customer' => true,
        'metode_pengambilan' => 'antar_toko',
        'metode_pembayaran' => 'tunai',
        'items' => [['product_id' => $product->getKey(), 'qty' => 2]],
        'user_id' => $user->id,
    ]);

    expect($order->order_code)->toMatch('/^LDY-\d{8}-0001$/')
        ->and($order->order_total)->toBe('14000.00')
        ->and($order->order_id_customer)->not->toBeNull()
        ->and(Customer::count())->toBe(1)
        ->and($order->hasStatusLogs()->count())->toBe(1)
        ->and(Order::count())->toBe(1);
});

it('requires alamat and slot for jemput', function () {
    [, , $product] = orderFixture();

    CreateOrderAction::run([
        'walkin_nama' => 'Budi',
        'walkin_telepon' => '081234567890',
        'metode_pengambilan' => 'jemput',
        'metode_pembayaran' => 'tunai',
        'items' => [['product_id' => $product->getKey(), 'qty' => 1]],
    ]);
})->throws(ValidationException::class);

it('rejects inactive product', function () {
    [, $user, $product] = orderFixture();
    $product->update(['product_is_aktif' => false]);

    CreateOrderAction::run([
        'walkin_nama' => 'Budi',
        'walkin_telepon' => '081234567890',
        'metode_pengambilan' => 'antar_toko',
        'metode_pembayaran' => 'tunai',
        'items' => [['product_id' => $product->getKey(), 'qty' => 1]],
        'user_id' => $user->id,
    ]);
})->throws(ValidationException::class);

it('snapshots price and computes estimation from product durations', function () {
    [, , $product] = orderFixture();

    $order = CreateOrderAction::run([
        'walkin_nama' => 'Budi',
        'walkin_telepon' => '081234567890',
        'metode_pengambilan' => 'antar_toko',
        'metode_pembayaran' => 'tunai',
        'items' => [['product_id' => $product->getKey(), 'qty' => 3]],
        'user_id' => 1,
    ]);

    // Price change after the fact must not affect stored snapshot.
    $product->update(['product_harga_dasar' => 9000]);

    $item = $order->hasItems()->first();

    expect($item->order_item_harga)->toBe('7000.00')
        ->and($item->order_item_subtotal)->toBe('21000.00')
        ->and((int) abs($order->order_estimasi_selesai->diffInHours($order->created_at)))->toBe(48);
});
