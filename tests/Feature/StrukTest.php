<?php

namespace Tests\Feature;

use App\Actions\CreateOrderAction;
use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

function strukFixture(): array
{
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    session(['laundry_id' => Laundry::first()->laundry_id]);
    $user = User::factory()->create(['role' => 'owner', 'verified_at' => now()]);
    Auth::login($user);
    $kategori = Kategori::create(['kategori_nama' => 'Pakaian']);
    $product = Product::create([
        'product_nama' => 'Cuci Kiloan',
        'product_id_kategori' => $kategori->getKey(),
        'product_satuan' => 'kg',
        'product_harga_dasar' => 7000,
        'product_estimasi_jam' => 48,
    ]);

    $order = CreateOrderAction::run([
        'walkin_nama' => 'Budi',
        'walkin_telepon' => '081234567890',
        'metode_pengambilan' => 'antar_toko',
        'metode_pembayaran' => 'tunai',
        'items' => [['product_id' => $product->getKey(), 'qty' => 2]],
        'user_id' => $user->id,
    ]);

    return [$user, $order];
}

it('renders show page with order code and timeline', function () {
    [$user, $order] = strukFixture();

    $this->actingAs($user)->withSession(['laundry_id' => Laundry::first()->laundry_id])
        ->get(route('order.getShow', ['id' => $order->getKey()]))
        ->assertOk()
        ->assertSee($order->order_code);
});

it('downloads struk pdf', function () {
    [$user, $order] = strukFixture();

    $response = $this->actingAs($user)->withSession(['laundry_id' => Laundry::first()->laundry_id])
        ->get(route('order.strukpdf', ['id' => $order->getKey()]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('pdf');
});

it('prints in browser and thermal modes', function () {
    [$user, $order] = strukFixture();

    foreach (['browser', 'thermal'] as $mode) {
        $this->actingAs($user)->withSession(['laundry_id' => Laundry::first()->laundry_id])
            ->get(route('order.print', ['id' => $order->getKey(), 'mode' => $mode]))
            ->assertOk()
            ->assertSee($order->order_code);
    }
});
