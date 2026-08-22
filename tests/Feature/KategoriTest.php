<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Laundry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

it('creates kategori scoped to active laundry', function () {
    $l1 = Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    $l2 = Laundry::create(['laundry_nama' => 'B', 'laundry_kode' => 'B']);

    session(['laundry_id' => $l1->laundry_id]);
    $k = Kategori::create(['kategori_nama' => 'Pakaian']);

    session(['laundry_id' => $l2->laundry_id]);
    expect(Kategori::count())->toBe(0)
        ->and(Kategori::withoutGlobalScopes()->find($k->getKey())->kategori_nama)->toBe('Pakaian');
});

it('rejects duplicate kategori nama case-insensitive within laundry', function () {
    Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    session(['laundry_id' => Laundry::first()->laundry_id]);
    Kategori::create(['kategori_nama' => 'Pakaian']);

    $validator = Validator::make(
        ['kategori_nama' => 'pakaian'],
        (new Kategori)->rules()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('kategori_nama'))->toContain('sudah digunakan');
});

it('shows kategori page scoped per laundry', function () {
    $l1 = Laundry::create(['laundry_nama' => 'A', 'laundry_kode' => 'A']);
    $l2 = Laundry::create(['laundry_nama' => 'B', 'laundry_kode' => 'B']);
    session(['laundry_id' => $l1->laundry_id]);
    Kategori::create(['kategori_nama' => 'Pakaian']);

    Auth::login(User::factory()->create(['role' => 'owner', 'verified_at' => now()]));
    session(['laundry_id' => $l1->laundry_id]);
    $this->get('/kategori/table')->assertOk();
});
