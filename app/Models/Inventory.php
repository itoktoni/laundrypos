<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['inventory_nama', 'inventory_satuan', 'inventory_min_stok', 'inventory_harga', 'inventory_keterangan'])]
class Inventory extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'inventory';

    protected $primaryKey = 'inventory_id';

    public static $filterColumns = ['inventory_nama'];

    public static $sortColumns = ['inventory_nama'];

    public static function field_name(): string
    {
        return 'inventory_nama';
    }

    protected function casts(): array
    {
        return [
            'inventory_min_stok' => 'integer',
            'inventory_harga' => 'decimal:2',
        ];
    }

    public function rules(): array
    {
        return [
            'inventory_nama' => ['required', 'string', 'max:100'],
            'inventory_satuan' => ['required', 'string', 'max:20'],
            'inventory_min_stok' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'inventory_harga' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'inventory_keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public static function satuanOptions(): array
    {
        return [
            'pcs' => 'pcs',
            'pack' => 'pack',
            'sachet' => 'sachet',
            'botol' => 'botol',
            'jerigen' => 'jerigen',
            'liter' => 'liter',
            'ml' => 'ml',
            'gram' => 'gram',
            'kg' => 'kg',
        ];
    }

    public function hasMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'movement_id_inventory', 'inventory_id');
    }

    // ponytail: ledger-only — saldo & avg dihitung on-read dari movements,
    // tidak ada kolom stok tersimpan agar kartu stok jadi satu-satunya kebenaran.
    public function getStokAttribute(): int
    {
        $masuk = (int) $this->hasMovements()->where('movement_tipe', 'masuk')->sum('movement_qty');
        $keluar = (int) $this->hasMovements()->where('movement_tipe', 'keluar')->sum('movement_qty');

        return $masuk - $keluar;
    }

    public function getRataRataAttribute(): float
    {
        $qty = (int) $this->hasMovements()->where('movement_tipe', 'masuk')->sum('movement_qty');
        if ($qty <= 0) {
            return 0;
        }
        $nominal = (float) $this->hasMovements()->where('movement_tipe', 'masuk')->sum('movement_nominal');

        return round($nominal / $qty, 2);
    }

    public function getTotalAttribute(): float
    {
        return round($this->stok * $this->rata_rata, 2);
    }
}
