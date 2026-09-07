<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use App\Enums\InventoryTipeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['movement_id_inventory', 'movement_tanggal', 'movement_tipe', 'movement_uom', 'movement_qty', 'movement_nominal', 'movement_keterangan'])]
class InventoryMovement extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'inventory_movement';

    protected $primaryKey = 'movement_id';

    public static $filterColumns = ['movement_tipe', 'movement_keterangan'];

    public static $sortColumns = ['movement_tanggal', 'movement_id'];

    public static function field_name(): string
    {
        return 'movement_keterangan';
    }

    protected function casts(): array
    {
        return [
            'movement_tanggal' => 'date',
            'movement_qty' => 'integer',
            'movement_nominal' => 'decimal:2',
        ];
    }

    public function rules(): array
    {
        return [
            'movement_id_inventory' => ['required', 'integer', 'exists:inventory,inventory_id'],
            'movement_tanggal' => ['required', 'date'],
            'movement_tipe' => ['required', 'string', 'in:'.implode(',', InventoryTipeEnum::getValues())],
            'movement_uom' => ['required', 'string', 'max:20'],
            'movement_qty' => ['required', 'integer', 'min:1', 'max:1000000'],
            'movement_nominal' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'movement_keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function hasInventory()
    {
        return $this->belongsTo(Inventory::class, 'movement_id_inventory', 'inventory_id');
    }
}
