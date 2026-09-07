<?php

namespace App\Http\Controllers;

use App\Concerns\ReportTrait;
use App\Models\StaffAttendance;
use App\Models\StaffSchedule;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ponytail: roster jadwal per karyawan — kalender tanggal + jam terjadwal
// disandingkan realisasi absen (hadir/terlambat/absen).
class ReportJadwalController extends Controller
{
    use ReportTrait;

    protected function data(Request $request, string $dari, string $sampai): array
    {
        $userId = $request->input('user', '');
        $user = $userId !== '' ? User::find($userId) : null;

        $schedules = collect();
        $rows = collect();
        if ($user) {
            $schedules = StaffSchedule::withoutGlobalScopes()
                ->where('schedule_id_laundry', session('laundry_id'))
                ->where('schedule_id_user', $user->getKey())
                ->whereDate('schedule_tanggal', '>=', $dari)
                ->whereDate('schedule_tanggal', '<=', $sampai)
                ->orderBy('schedule_tanggal')->get()
                ->keyBy(fn ($s) => \Carbon\Carbon::parse($s->schedule_tanggal)->toDateString());
            $rows = StaffAttendance::with('hasUser')
                ->where('attendance_id_user', $user->getKey())
                ->whereDate('attendance_tanggal', '>=', $dari)
                ->whereDate('attendance_tanggal', '<=', $sampai)
                ->orderBy('attendance_tanggal')->get()
                ->keyBy(fn ($r) => \Carbon\Carbon::parse($r->attendance_tanggal)->toDateString());
        }

        return [
            'user' => $user,
            'schedules' => $schedules,
            'rows' => $rows,
            'dari' => $dari,
            'sampai' => $sampai,
            'userId' => $userId,
        ];
    }

    protected function userOptions()
    {
        $query = User::orderBy('name');
        $laundryId = session('laundry_id');
        if ($laundryId) {
            $ids = DB::table('laundry_user')->where('laundry_id', $laundryId)->pluck('user_id');
            $query->whereIn('id', $ids);
        }

        return $query->pluck('name', 'id');
    }

    public function getIndex(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return view('pages.report.jadwal', array_merge(
            $this->data($request, $dari, $sampai),
            ['userOptions' => $this->userOptions()]
        ));
    }

    public function getPdf(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $data = $this->data($request, $dari, $sampai);

        $lines = [];
        $cursor = \Carbon\Carbon::parse($dari);
        $end = \Carbon\Carbon::parse($sampai);
        $guard = 0;
        while ($cursor <= $end && $guard < 400) {
            $guard++;
            $key = $cursor->toDateString();
            $sched = $data['schedules'][$key] ?? null;
            $row = $data['rows'][$key] ?? null;
            $eval = $row ? StaffSchedule::evaluate($row, $sched) : ['terlambat' => false, 'menitTerlambat' => 0, 'noCheckout' => false];
            $lines[] = [
                'tanggal' => $key,
                'jadwal' => $sched ? substr((string) $sched->schedule_jam_masuk, 0, 5).'–'.substr((string) $sched->schedule_jam_pulang, 0, 5) : '-',
                'checkin' => $row?->attendance_checkin_at?->format('H:i') ?? '-',
                'checkout' => $row?->attendance_checkout_at?->format('H:i') ?? '-',
                'status' => $row ? ucfirst($row->attendance_status) : ($sched ? 'Absen' : '-'),
                'ket' => $eval['terlambat'] ? 'Telat '.$eval['menitTerlambat'].' mnt' : ($eval['noCheckout'] ? 'Tanpa checkout' : ''),
            ];
            $cursor = $cursor->addDay();
        }

        return Pdf::loadView('pdf.report-jadwal', array_merge($data, ['lines' => $lines]))
            ->setPaper('a4', 'landscape')->download("laporan-jadwal-{$dari}-{$sampai}.pdf");
    }
}
