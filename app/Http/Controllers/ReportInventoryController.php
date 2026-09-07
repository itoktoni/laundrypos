<?php

namespace App\Http\Controllers;

use App\Concerns\ReportTrait;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportInventoryController extends Controller
{
    use ReportTrait;

    protected function rows()
    {
        return Inventory::orderBy('inventory_nama')->get()
            ->map(fn (Inventory $inv) => [
                'nama' => $inv->inventory_nama,
                'satuan' => $inv->inventory_satuan,
                'stok' => $inv->stok,
                'rata_rata' => $inv->rata_rata,
                'total' => round($inv->stok * $inv->rata_rata, 2),
                'min_stok' => $inv->inventory_min_stok,
            ]);
    }

    public function getIndex(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $rows = $this->rows();

        $masukQuery = fn () => InventoryMovement::where('movement_tipe', 'masuk')
            ->whereDate('movement_tanggal', '>=', $dari)
            ->whereDate('movement_tanggal', '<=', $sampai);
        $keluarQuery = fn () => InventoryMovement::where('movement_tipe', 'keluar')
            ->whereDate('movement_tanggal', '>=', $dari)
            ->whereDate('movement_tanggal', '<=', $sampai);

        return view('pages.report.inventory', [
            'rows' => $rows,
            'totalNilai' => (float) $rows->sum('total'),
            'masuk' => (float) $masukQuery()->sum('movement_nominal'),
            'qtyMasuk' => (int) $masukQuery()->sum('movement_qty'),
            'qtyKeluar' => (int) $keluarQuery()->sum('movement_qty'),
            'dari' => $dari,
            'sampai' => $sampai,
        ]);
    }

    public function getPdf(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return Pdf::loadView('pdf.report-inventory', [
            'rows' => $this->rows(),
            'dari' => $dari,
            'sampai' => $sampai,
        ])->setPaper('a4', 'landscape')->download("laporan-inventory-{$dari}-{$sampai}.pdf");
    }
}
