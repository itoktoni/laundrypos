<?php

namespace App\Http\Controllers;

use App\Concerns\ReportTrait;
use App\Models\StaffAttendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportAbsensiController extends Controller
{
    use ReportTrait;

    protected function query(Request $request, string $dari, string $sampai)
    {
        $query = StaffAttendance::with('hasUser')
            ->whereDate('attendance_tanggal', '>=', $dari)
            ->whereDate('attendance_tanggal', '<=', $sampai);

        if ($request->filled('user')) {
            $query->where('attendance_id_user', $request->input('user'));
        }

        return $query->orderBy('attendance_tanggal');
    }

    // ponytail: ringkasan penggajian per karyawan — hadir × upah harian.
    protected function payrolls($rows, float $upah): array
    {
        return $rows->groupBy('attendance_id_user')->map(function ($items) use ($upah) {
            $hadir = $items->where('attendance_status', 'hadir')->count();

            return [
                'nama' => $items->first()->hasUser?->name ?? '-',
                'hadir' => $hadir,
                'izin' => $items->where('attendance_status', 'izin')->count(),
                'sakit' => $items->where('attendance_status', 'sakit')->count(),
                'total' => round($hadir * $upah, 2),
            ];
        })->values()->all();
    }

    public function getIndex(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $rows = $this->query($request, $dari, $sampai)->get();
        $upah = max((float) $request->input('upah', 0), 0);

        return view('pages.report.absensi', [
            'rows' => $rows,
            'hadir' => $rows->where('attendance_status', 'hadir')->count(),
            'izin' => $rows->where('attendance_status', 'izin')->count(),
            'sakit' => $rows->where('attendance_status', 'sakit')->count(),
            'payrolls' => $this->payrolls($rows, $upah),
            'upah' => $upah,
            'dari' => $dari,
            'sampai' => $sampai,
            'userOptions' => $this->branchUserOptions(),
            'userId' => $request->input('user', ''),
        ]);
    }

    // ponytail: dropdown hanya karyawan cabang aktif agar tidak zonk.
    protected function branchUserOptions()
    {
        $query = User::orderBy('name');
        $laundryId = session('laundry_id');
        if ($laundryId) {
            $ids = \Illuminate\Support\Facades\DB::table('laundry_user')
                ->where('laundry_id', $laundryId)->pluck('user_id');
            $query->whereIn('id', $ids);
        }

        return $query->pluck('name', 'id');
    }

    public function getPdf(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $rows = $this->query($request, $dari, $sampai)->get();
        $upah = max((float) $request->input('upah', 0), 0);

        return Pdf::loadView('pdf.report-absensi', [
            'rows' => $rows,
            'payrolls' => $this->payrolls($rows, $upah),
            'upah' => $upah,
            'dari' => $dari,
            'sampai' => $sampai,
        ])->setPaper('a4', 'landscape')->download("laporan-absensi-{$dari}-{$sampai}.pdf");
    }
}
