<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Laundry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

it('scopes customer per laundry', function () {
    $l1 = Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    $l2 = Laundry::create(['laundry_nama' => 'B', 'laundry_kode' => 'B']);

    session(['laundry_id' => $l1->laundry_id]);
    Customer::create(['customer_nama' => 'Andi', 'customer_telepon' => '081234567890']);

    session(['laundry_id' => $l2->laundry_id]);
    expect(Customer::count())->toBe(0);
});

it('rejects duplicate telepon and invalid telepon format', function () {
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    session(['laundry_id' => Laundry::first()->laundry_id]);
    Customer::create(['customer_nama' => 'Andi', 'customer_telepon' => '081234567890']);

    $rules = (new Customer)->rules();

    $dup = Validator::make(['customer_telepon' => '081234567890'], $rules);
    expect($dup->fails())->toBeTrue();

    $short = Validator::make(['customer_telepon' => '123'], $rules);
    expect($short->fails())->toBeTrue();
});

it('shows customer table page', function () {
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    session(['laundry_id' => Laundry::first()->laundry_id]);
    Auth::login(User::factory()->create(['role' => 'owner', 'verified_at' => now()]));

    $this->get('/customer/table')->assertOk();
});
