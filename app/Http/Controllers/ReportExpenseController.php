<?php

namespace App\Http\Controllers;

use App\Concerns\ReportTrait;
use App\Models\Expense;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportExpenseController extends Controller
{
    use ReportTrait;

    protected function query(Request $request, string $dari, string $sampai)
    {
        $query = Expense::whereDate('expense_tanggal', '>=', $dari)
            ->whereDate('expense_tanggal', '<=', $sampai);

        if ($request->filled('kategori')) {
            $query->where('expense_kategori', $request->input('kategori'));
        }

        return $query->orderBy('expense_tanggal');
    }

    public function getIndex(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $expenses = $this->query($request, $dari, $sampai)->get();

        return view('pages.report.expense', [
            'expenses' => $expenses,
            'total' => (float) $expenses->sum('expense_nominal'),
            'perKategori' => $expenses->groupBy('expense_kategori')
                ->map(fn ($rows) => (float) $rows->sum('expense_nominal'))
                ->sortDesc(),
            'dari' => $dari,
            'sampai' => $sampai,
            'kategoriOptions' => Expense::kategoriOptions(),
            'kategori' => $request->input('kategori', ''),
        ]);
    }

    public function getPdf(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return Pdf::loadView('pdf.report-expense', [
            'expenses' => $this->query($request, $dari, $sampai)->get(),
            'dari' => $dari,
            'sampai' => $sampai,
        ])->setPaper('a4', 'landscape')->download("laporan-pengeluaran-{$dari}-{$sampai}.pdf");
    }
}
