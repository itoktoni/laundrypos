<?php

namespace App\Http\Controllers;

use App\Concerns\ReportTrait;
use App\Models\Mesin;
use App\Models\MesinService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportMesinController extends Controller
{
    use ReportTrait;

    protected function services(string $dari, string $sampai)
    {
        return MesinService::with('hasMesin')
            ->whereDate('service_tanggal', '>=', $dari)
            ->whereDate('service_tanggal', '<=', $sampai)
            ->orderBy('service_tanggal')->get();
    }

    public function getIndex(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $mesins = Mesin::orderBy('mesin_nama')->get();
        $services = $this->services($dari, $sampai);

        return view('pages.report.mesin', [
            'mesins' => $mesins,
            'services' => $services,
            'totalBiaya' => (float) $services->sum('service_biaya'),
            'totalNilaiBuku' => (float) $mesins->sum(fn (Mesin $m) => $m->nilai_buku),
            'dari' => $dari,
            'sampai' => $sampai,
        ]);
    }

    public function getPdf(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return Pdf::loadView('pdf.report-mesin', [
            'services' => $this->services($dari, $sampai),
            'dari' => $dari,
            'sampai' => $sampai,
        ])->setPaper('a4', 'landscape')->download("laporan-mesin-{$dari}-{$sampai}.pdf");
    }
}
