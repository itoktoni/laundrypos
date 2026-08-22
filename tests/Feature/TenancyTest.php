<?php

namespace Tests\Feature;

use App\Models\Laundry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

it('redirects to picker when no laundry selected', function () {
    $user = User::factory()->create(['role' => 'owner', 'verified_at' => now()]);
    Auth::login($user);

    $this->get('/dashboard')->assertRedirect(route('laundry.picker'));
});

it('allows dashboard when laundry in session and user is owner', function () {
    $laundry = Laundry::create(['laundry_nama' => 'L1', 'laundry_kode' => 'L1']);
    $user = User::factory()->create(['role' => 'owner', 'verified_at' => now()]);
    Auth::login($user);

    $this->withSession(['laundry_id' => $laundry->laundry_id])
        ->get('/dashboard')->assertOk();
});

it('rejects non-member using unassigned laundry', function () {
    $laundry = Laundry::create(['laundry_nama' => 'L2', 'laundry_kode' => 'L2']);
    $user = User::factory()->create(['role' => 'karyawan', 'verified_at' => now()]);
    Auth::login($user);

    $this->withSession(['laundry_id' => $laundry->laundry_id])
        ->get('/dashboard')->assertRedirect(route('laundry.picker'));
});

it('allows member using assigned laundry', function () {
    $laundry = Laundry::create(['laundry_nama' => 'L3', 'laundry_kode' => 'L3']);
    $user = User::factory()->create(['role' => 'karyawan', 'verified_at' => now()]);
    DB::table('laundry_user')->insert([
        'laundry_id' => $laundry->laundry_id,
        'user_id' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    Auth::login($user);

    $this->withSession(['laundry_id' => $laundry->laundry_id])
        ->get('/dashboard')->assertOk();
});
