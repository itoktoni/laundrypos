<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

it('generates order codes with daily reset', function () {
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    session(['laundry_id' => Laundry::first()->laundry_id]);

    $code1 = Order::generateCode();
    expect($code1)->toMatch('/^LDY-\d{8}-0001$/');

    DB::table('order')->insert([
        'order_id_laundry' => Laundry::first()->laundry_id,
        'order_code' => $code1,
        'order_id_user' => 1,
        'order_metode_pengambilan' => 'antar_toko',
        'order_metode_pembayaran' => 'tunai',
        'order_subtotal' => 0.01,
        'order_total' => 0.01,
        'order_status_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(Order::generateCode())->toMatch('/^LDY-\d{8}-0002$/');

    Carbon::setTestNow(now()->addDay());
    expect(Order::generateCode())->toMatch('/^LDY-\d{8}-0001$/');
    Carbon::setTestNow();
});
