<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use App\Concerns\BelongsToLaundry;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['expense_nama', 'expense_kategori', 'expense_nominal', 'expense_tanggal', 'expense_catatan', 'expense_metode_pembayaran'])]
class Expense extends BaseModel
{
    use BelongsToLaundry, Filterable, Sortable;

    protected $table = 'expense';

    protected $primaryKey = 'expense_id';

    public static $filterColumns = ['expense_nama', 'expense_kategori'];

    public static $sortColumns = ['expense_nama', 'expense_tanggal', 'expense_nominal'];

    public static function field_name(): string
    {
        return 'expense_nama';
    }

    public function rules(): array
    {
        return [
            'expense_nama' => 'required|string|max:255',
            'expense_kategori' => 'required|string|max:100',
            'expense_nominal' => 'required|numeric|min:0',
            'expense_tanggal' => 'required|date',
            'expense_catatan' => 'nullable|string|max:500',
            'expense_metode_pembayaran' => 'required|in:tunai,transfer,dompet_digital',
        ];
    }

    public static function kategoriOptions(): array
    {
        return [
            'Listrik' => 'Listrik',
            'Air' => 'Air',
            'Gaji' => 'Gaji',
            'Sewa' => 'Sewa',
            'Perlengkapan' => 'Perlengkapan',
            'Operasional' => 'Operasional',
            'Marketing' => 'Marketing',
            'Maintenance' => 'Maintenance',
            'Lainnya' => 'Lainnya',
        ];
    }
}
