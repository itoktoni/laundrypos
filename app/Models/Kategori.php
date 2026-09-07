<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['kategori_nama', 'kategori_deskripsi', 'kategori_is_aktif'])]
class Kategori extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'kategori';

    protected $primaryKey = 'kategori_id';

    public static $filterColumns = ['kategori_nama'];

    public static $sortColumns = ['kategori_nama'];

    public static function field_name(): string
    {
        return 'kategori_nama';
    }

    public function rules(): array
    {
        return [
            'kategori_nama' => [
                'required', 'string', 'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $exists = Kategori::query()
                        ->whereRaw('LOWER(kategori_nama) = ?', [strtolower((string) $value)])
                        ->when(request()->route('id'), fn ($q, $id) => $q->where('kategori_id', '!=', $id))
                        ->exists();

                    if ($exists) {
                        $fail('Nama kategori sudah digunakan.');
                    }
                },
            ],
            'kategori_deskripsi' => ['nullable', 'string', 'max:500'],
            'kategori_is_aktif' => ['nullable', 'boolean'],
        ];
    }

    public function hasProducts()
    {
        return $this->hasMany(Product::class, 'product_id_kategori', 'kategori_id');
    }
}
