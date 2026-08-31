<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['discount_nama', 'discount_kode', 'discount_tipe', 'discount_nilai', 'discount_min_pembelian', 'discount_max_diskon', 'discount_mulai', 'discount_selesai', 'discount_is_aktif'])]
class Discount extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'discount';

    protected $primaryKey = 'discount_id';

    public static $filterColumns = ['discount_nama', 'discount_kode'];

    public static $sortColumns = ['discount_nama', 'discount_kode'];

    public static function field_name(): string
    {
        return 'discount_nama';
    }

    protected function casts(): array
    {
        return [
            'discount_nilai' => 'decimal:2',
            'discount_min_pembelian' => 'decimal:2',
            'discount_max_diskon' => 'decimal:2',
            'discount_mulai' => 'date',
            'discount_selesai' => 'date',
            'discount_is_aktif' => 'boolean',
        ];
    }

    public function rules(): array
    {
        return [
            'discount_nama' => ['required', 'string', 'max:100'],
            'discount_kode' => ['required', 'string', 'max:30', 'uppercase'],
            'discount_tipe' => ['required', 'in:persen,nominal'],
            'discount_nilai' => ['required', 'numeric', 'min:0.01'],
            'discount_min_pembelian' => ['nullable', 'numeric', 'min:0'],
            'discount_max_diskon' => ['nullable', 'numeric', 'min:0'],
            'discount_mulai' => ['nullable', 'date'],
            'discount_selesai' => ['nullable', 'date', 'after_or_equal:discount_mulai'],
            'discount_is_aktif' => ['nullable', 'boolean'],
        ];
    }

    public function hitungDiskon(float $subtotal): float
    {
        if ($subtotal < $this->discount_min_pembelian) {
            return 0;
        }

        $diskon = $this->discount_tipe === 'persen'
            ? $subtotal * ($this->discount_nilai / 100)
            : $this->discount_nilai;

        if ($this->discount_max_diskon !== null) {
            $diskon = min($diskon, $this->discount_max_diskon);
        }

        return min($diskon, $subtotal);
    }

    public function scopeAktif($query)
    {
        return $query->where('discount_is_aktif', true)
            ->where(function ($q) {
                $q->whereNull('discount_mulai')->orWhere('discount_mulai', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('discount_selesai')->orWhere('discount_selesai', '>=', now());
            });
    }
}
