<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['order_status_id_laundry', 'order_status_nama', 'order_status_urutan', 'order_status_is_batal', 'order_status_is_selesai', 'order_status_warna'])]
class OrderStatus extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'order_status';

    protected $primaryKey = 'order_status_id';

    public static $sortColumns = ['order_status_urutan'];

    public static $filterColumns = ['order_status_nama'];

    public static function field_name(): string
    {
        return 'order_status_nama';
    }

    protected function casts(): array
    {
        return [
            'order_status_is_batal' => 'boolean',
            'order_status_is_selesai' => 'boolean',
            'order_status_urutan' => 'integer',
        ];
    }

    public function rules(): array
    {
        return [
            'order_status_nama' => ['required', 'string', 'max:50'],
            'order_status_urutan' => ['required', 'integer', 'min:1'],
            'order_status_warna' => ['nullable', 'string', 'max:7'],
        ];
    }

    public static function booted(): void
    {
        // Default ordering by flow sequence everywhere.
        static::addGlobalScope('ordered', function ($builder): void {
            $builder->orderBy('order_status_urutan');
        });
    }

    public function hasLaundry()
    {
        return $this->belongsTo(Laundry::class, 'order_status_id_laundry', 'laundry_id');
    }
}
