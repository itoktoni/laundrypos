<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\OrderStatus;

it('seeds default status flow for each new laundry', function () {
    $laundry = Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);

    session(['laundry_id' => $laundry->laundry_id]);

    $statuses = OrderStatus::get();

    expect($statuses)->toHaveCount(7)
        ->and($statuses->first()->order_status_nama)->toBe('Menunggu Konfirmasi')
        ->and($statuses->first()->order_status_urutan)->toBe(1)
        ->and($statuses->last()->order_status_nama)->toBe('Dibatalkan')
        ->and($statuses->last()->order_status_is_batal)->toBeTrue()
        ->and($statuses->slice(5, 1)->first()->order_status_is_selesai)->toBeTrue();
});
