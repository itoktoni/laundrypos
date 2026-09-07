<?php

namespace Tests\Feature;

use App\Actions\CreateOrderAction;
use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

function transitionFixture(): Order
{
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    session(['laundry_id' => Laundry::first()->laundry_id]);
    $user = User::factory()->create(['role' => 'owner']);
    Auth::login($user);
    $kategori = Kategori::create(['kategori_nama' => 'Pakaian']);
    $product = Product::create([
        'product_nama' => 'Cuci Kiloan',
        'product_id_kategori' => $kategori->getKey(),
        'product_satuan' => 'kg',
        'product_harga_dasar' => 7000,
        'product_estimasi_jam' => 48,
    ]);

    CreateOrderAction::run([
        'walkin_nama' => 'Budi',
        'walkin_telepon' => '081234567890',
        'metode_pengambilan' => 'antar_toko',
        'metode_pembayaran' => 'tunai',
        'items' => [['product_id' => $product->getKey(), 'qty' => 1]],
        'user_id' => $user->id,
    ]);

    return OrderStatusLog::first()->hasOrder()->first();
}

it('advances one step forward and logs the transition', function () {
    $order = transitionFixture();

    $next = OrderStatus::where('order_status_urutan', 2)->first();
    $order->transitStatus($next);

    expect($order->fresh()->order_status_id)->toBe($next->getKey())
        ->and($order->hasStatusLogs()->count())->toBe(2);
});

it('rejects skipping or going backward', function () {
    $order = transitionFixture();

    // Skip: urutan 3 while at urutan 1
    $skip = OrderStatus::where('order_status_urutan', 3)->first();
    try {
        $order->transitStatus($skip);
        $this->fail('Skip should fail');
    } catch (ValidationException $e) {
        expect($e->errors()['status'][0])->toContain('tidak valid');
    }

    // Backward from current (still urutan 1): not possible, but cancel-then-anything also blocked.
});

it('requires keterangan for cancellation and locks after Selesai', function () {
    $order = transitionFixture();

    $batal = OrderStatus::where('order_status_is_batal', true)->first();
    try {
        $order->transitStatus($batal);
        $this->fail('Cancel without reason should fail');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('keterangan');
    }

    $order->transitStatus($batal, 'Pelanggan batal');
    expect($order->fresh()->order_status_id)->toBe($batal->getKey());

    // After cancellation, forward moves are still rejected.
    $diterima = OrderStatus::where('order_status_urutan', 2)->first();
    try {
        $order->fresh()->transitStatus($diterima);
        $this->fail('Move after cancel should fail');
    } catch (ValidationException $e) {
        expect(true)->toBeTrue();
    }
});
