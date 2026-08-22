<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use App\Enums\SatuanEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['product_nama', 'product_id_kategori', 'product_satuan', 'product_harga_dasar', 'product_estimasi_jam', 'product_deskripsi', 'product_is_aktif'])]
class Product extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'product';

    protected $primaryKey = 'product_id';

    public static $filterColumns = ['product_nama'];

    public static $sortColumns = ['product_nama', 'product_harga_dasar'];

    public static function field_name(): string
    {
        return 'product_nama';
    }

    protected function casts(): array
    {
        return [
            'product_satuan' => SatuanEnum::class,
            'product_harga_dasar' => 'decimal:2',
            'product_is_aktif' => 'boolean',
        ];
    }

    public function rules(): array
    {
        return [
            'product_nama' => ['required', 'string', 'max:100'],
            'product_id_kategori' => ['required', 'integer'],
            'product_satuan' => ['required', 'string', 'in:'.implode(',', SatuanEnum::getValues())],
            'product_harga_dasar' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'product_estimasi_jam' => ['required', 'integer', 'min:1', 'max:720'],
            'product_deskripsi' => ['nullable', 'string', 'max:500'],
            'product_is_aktif' => ['nullable', 'boolean'],
        ];
    }

    public function hasKategori()
    {
        return $this->belongsTo(Kategori::class, 'product_id_kategori', 'kategori_id');
    }
}
