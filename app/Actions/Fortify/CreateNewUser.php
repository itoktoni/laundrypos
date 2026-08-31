<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Laundry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'laundry_nama' => ['required', 'string', 'max:100'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $laundry = Laundry::create([
                'laundry_nama' => $input['laundry_nama'],
                'laundry_kode' => 'LDY'.str_pad(unicNumber(4), 4, '0', STR_PAD_LEFT),
            ]);

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'owner',
            ]);

            $laundry->hasUsers()->attach($user->id);

            session(['laundry_id' => $laundry->laundry_id]);

            return $user;
        });
    }
}
