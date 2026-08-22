<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Fillable(['laundry_nama', 'laundry_kode', 'laundry_alamat', 'laundry_telepon', 'laundry_is_aktif'])]
class Laundry extends BaseModel
{
    protected $table = 'laundry';
    protected $primaryKey = 'laundry_id';

    public static $filterColumns = ['laundry_nama'];
    public static $sortColumns = ['laundry_nama'];

    public static function field_name(): string
    {
        return 'laundry_nama';
    }

    public function rules(): array
    {
        return [
            'laundry_nama' => ['required', 'string', 'max:100'],
            'laundry_kode' => ['required', 'string', 'max:20'],
        ];
    }

    public function hasUsers()
    {
        return $this->belongsToMany(User::class, 'laundry_user', 'laundry_id', 'id', 'laundry_id', 'id');
    }

    protected static function booted(): void
    {
        // Seed default status flow for each new laundry.
        // ponytail: guarded with hasTable because the hook fires in tests/migrations
        // before order_status exists (Task ordering in the laundry-pos plan).
        static::created(function (self $laundry): void {
            if (! Schema::hasTable('order_status')) {
                return;
            }

            $defaults = [
                ['Menunggu Konfirmasi', false, false],
                ['Diterima', false, false],
                ['Dalam Proses', false, false],
                ['Selesai Dicuci', false, false],
                ['Siap Diambil', false, false],
                ['Selesai', false, true],
                ['Dibatalkan', true, false],
            ];

            foreach ($defaults as $i => [$nama, $batal, $selesai]) {
                DB::table('order_status')->insert([
                    'order_status_id_laundry' => $laundry->laundry_id,
                    'order_status_nama' => $nama,
                    'order_status_urutan' => $i + 1,
                    'order_status_is_batal' => $batal,
                    'order_status_is_selesai' => $selesai,
                    'order_status_warna' => $batal ? '#dc2626' : '#2563eb',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
