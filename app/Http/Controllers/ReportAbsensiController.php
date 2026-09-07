<?php

namespace App\Http\Controllers;

use App\Concerns\ReportTrait;
use App\Models\StaffAttendance;
use App\Models\StaffSchedule;
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

    // ponytail: preload jadwal (user|tanggal) agar evaluasi per baris
    // tidak N+1; dipakai untuk badge terlambat & tanpa-checkout.
    protected function jadwalMap($rows): array
    {
        $keys = $rows->map(fn ($r) => [
            'user' => $r->attendance_id_user,
            'date' => \Carbon\Carbon::parse($r->attendance_tanggal)->toDateString(),
            'laundry' => $r->attendance_id_laundry,
        ]);
        if ($keys->isEmpty()) {
            return [];
        }

        $map = [];
        foreach ($keys->groupBy('laundry') as $laundryId => $group) {
            $schedules = StaffSchedule::withoutGlobalScopes()
                ->where('schedule_id_laundry', $laundryId)
                ->whereIn('schedule_id_user', $group->pluck('user')->unique())
                ->whereDate('schedule_tanggal', '>=', $group->pluck('date')->min())
                ->whereDate('schedule_tanggal', '<=', $group->pluck('date')->max())
                ->get()
                ->keyBy(fn ($s) => $s->schedule_id_user.'|'.\Carbon\Carbon::parse($s->schedule_tanggal)->toDateString());
            foreach ($schedules as $k => $s) {
                $map[$k] = $s;
            }
        }

        return $map;
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

    // ponytail: evaluasi per baris (terlambat/menit/no-checkout) ikut jadwal.
    protected function evaluated($rows, array $jadwalMap)
    {
        return $rows->map(function ($row) use ($jadwalMap) {
            $key = $row->attendance_id_user.'|'.\Carbon\Carbon::parse($row->attendance_tanggal)->toDateString();
            $row->evaluasi = StaffSchedule::evaluate($row, $jadwalMap[$key] ?? null);

            return $row;
        });
    }

    public function getIndex(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $base = $this->query($request, $dari, $sampai)->get();
        $rows = $this->evaluated($base, $this->jadwalMap($base));
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
        $base = $this->query($request, $dari, $sampai)->get();
        $rows = $this->evaluated($base, $this->jadwalMap($base));
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
