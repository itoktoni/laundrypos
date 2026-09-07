<?php

namespace Database\Seeders;

use App\Models\Laundry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            [
                'name' => 'Owner',
                'email' => 'owner@laundry.test',
                'role' => 'developer', // super admin / owner cabang
                'phone' => '081200000001',
            ],
            [
                'name' => 'Admin Laundry',
                'email' => 'admin@laundry.test',
                'role' => 'admin',
                'phone' => '081200000002',
            ],
            [
                'name' => 'Staff Kasir',
                'email' => 'staff@laundry.test',
                'role' => 'editor',
                'phone' => '081200000003',
            ],
            [
                'name' => 'Customer Demo',
                'email' => 'customer@laundry.test',
                'role' => 'user',
                'phone' => '081200000004',
            ],
        ];

        // Laundry utama (buat jika belum ada)
        $laundry = Laundry::firstOrCreate(
            ['laundry_kode' => 'LDRY001'],
            ['laundry_nama' => 'Laundry Bersih Kilat', 'laundry_alamat' => 'Jl. Mawar No. 10, Jakarta', 'laundry_telepon' => '081234567890']
        );

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'phone' => $u['phone'],
                    'password' => $password,
                    'verified_at' => now(),
                    'email_verified_at' => now(),
                ]
            );

            // Attach ke laundry (pivot) jika belum
            $exists = DB::table('laundry_user')->where('laundry_id', $laundry->laundry_id)->where('user_id', $user->id)->exists();
            if (! $exists) {
                DB::table('laundry_user')->insert([
                    'laundry_id' => $laundry->laundry_id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Staff & admin juga attach ke cabang 2 untuk demo multi-laundry
            if (in_array($u['role'], ['admin', 'editor', 'developer'])) {
                $laundry2 = Laundry::where('laundry_kode', 'LDRY002')->first();
                if ($laundry2) {
                    $exists2 = DB::table('laundry_user')->where('laundry_id', $laundry2->laundry_id)->where('user_id', $user->id)->exists();
                    if (! $exists2) {
                        DB::table('laundry_user')->insert([
                            'laundry_id' => $laundry2->laundry_id,
                            'user_id' => $user->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Seeded users: owner@laundry.test / admin@laundry.test / staff@laundry.test / customer@laundry.test (pass: password)');
    }
}
