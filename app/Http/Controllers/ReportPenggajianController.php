<?php

namespace App\Http\Controllers;

use App\Concerns\ReportTrait;
use App\Models\StaffAttendance;
use App\Models\StaffSchedule;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

// ponytail: modul penggajian — pilih 1 karyawan + periode + upah harian,
// tampil rekap masuk, detail kehadiran, dan total gaji (hadir × upah).
class ReportPenggajianController extends Controller
{
    use ReportTrait;

    protected function data(Request $request, string $dari, string $sampai): array
    {
        $userId = $request->input('user', '');
        $user = $userId !== '' ? User::find($userId) : null;

        // ponytail: input kosong = pakai config user (fallback default global).
        $configPokok = (float) ($user?->gaji_pokok ?? 1000000);
        $configPotongan = (float) ($user?->gaji_absensi ?? 20000);
        $configDendaTerlambat = (float) ($user?->denda_terlambat ?? 5000);
        $configDendaCheckout = (float) ($user?->denda_checkout ?? 5000);
        $rawPokok = $request->input('pokok', '');
        $rawBonus = $request->input('bonus', '');
        $rawPotongan = $request->input('potongan', '');
        $rawDendaTerlambat = $request->input('denda_terlambat', '');
        $rawDendaCheckout = $request->input('denda_checkout', '');
        $pokok = $rawPokok === '' || $rawPokok === null ? $configPokok : max((float) $rawPokok, 0);
        $bonus = $rawBonus === '' || $rawBonus === null ? 0 : max((float) $rawBonus, 0);
        $potongan = $rawPotongan === '' || $rawPotongan === null ? $configPotongan : max((float) $rawPotongan, 0);
        $dendaTerlambat = $rawDendaTerlambat === '' || $rawDendaTerlambat === null ? $configDendaTerlambat : max((float) $rawDendaTerlambat, 0);
        $dendaCheckout = $rawDendaCheckout === '' || $rawDendaCheckout === null ? $configDendaCheckout : max((float) $rawDendaCheckout, 0);
        $rows = collect();
        if ($user) {
            $rows = StaffAttendance::with('hasUser')
                ->where('attendance_id_user', $user->getKey())
                ->whereDate('attendance_tanggal', '>=', $dari)
                ->whereDate('attendance_tanggal', '<=', $sampai)
                ->orderBy('attendance_tanggal')->get();
        }

        $hadir = $rows->where('attendance_status', 'hadir')->count();
        $izin = $rows->where('attendance_status', 'izin')->count();
        $sakit = $rows->where('attendance_status', 'sakit')->count();

        // ponytail: hari kerja = Senin–Sabtu dalam periode; hari kerja tanpa
        // record hadir otomatis dihitung absen sehingga gaji ikut berkurang.
        $hariKerja = 0;
        $cursor = \Carbon\Carbon::parse($dari);
        $end = \Carbon\Carbon::parse($sampai);
        $guard = 0;
        while ($cursor <= $end && $guard < 400) {
            $guard++;
            if (! $cursor->isSunday()) {
                $hariKerja++;
            }
            $cursor = $cursor->addDay();
        }
        $hariAbsen = $user ? max($hariKerja - $hadir, 0) : 0;
        // ponytail: insentif kehadiran — hadir × rate, ditambahkan ke gaji.
        $insentif = round($hadir * $potongan, 2);

        // ponytail: denda ikut jadwal — terlambat & tanpa checkout (hadir saja).
        $terlambat = 0;
        $noCheckout = 0;
        $lateDates = [];
        foreach ($rows as $row) {
            $eval = StaffSchedule::evaluate($row);
            if ($eval['terlambat']) {
                $terlambat++;
                $lateDates[] = \Carbon\Carbon::parse($row->attendance_tanggal)->toDateString();
            }
            if ($eval['noCheckout']) {
                $noCheckout++;
            }
        }
        $potTerlambat = round($terlambat * $dendaTerlambat, 2);
        $potCheckout = round($noCheckout * $dendaCheckout, 2);

        return [
            'user' => $user,
            'rows' => $rows,
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'pokok' => $pokok,
            'bonus' => $bonus,
            'potongan' => $potongan,
            'configPokok' => $configPokok,
            'configPotongan' => $configPotongan,
            'configDendaTerlambat' => $configDendaTerlambat,
            'configDendaCheckout' => $configDendaCheckout,
            'rawPokok' => is_string($rawPokok) ? $rawPokok : '',
            'rawBonus' => is_string($rawBonus) ? $rawBonus : '',
            'rawPotongan' => is_string($rawPotongan) ? $rawPotongan : '',
            'rawDendaTerlambat' => is_string($rawDendaTerlambat) ? $rawDendaTerlambat : '',
            'rawDendaCheckout' => is_string($rawDendaCheckout) ? $rawDendaCheckout : '',
            'hariAbsen' => $hariAbsen,
            'hariKerja' => $hariKerja,
            'insentif' => $insentif,
            'terlambat' => $terlambat,
            'noCheckout' => $noCheckout,
            'lateDates' => $lateDates,
            'dendaTerlambat' => $dendaTerlambat,
            'dendaCheckout' => $dendaCheckout,
            'potTerlambat' => $potTerlambat,
            'potCheckout' => $potCheckout,
            'total' => round($pokok + $bonus + $insentif - $potTerlambat - $potCheckout, 2),
            'userId' => $userId,
        ];
    }

    // ponytail: dropdown hanya karyawan cabang aktif agar tidak zonk.
    protected function userOptions()
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

    public function getIndex(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return view('pages.report.penggajian', array_merge(
            $this->data($request, $dari, $sampai),
            [
                'dari' => $dari,
                'sampai' => $sampai,
                'userOptions' => $this->userOptions(),
                'cabangNama' => \App\Models\Laundry::find(session('laundry_id'))?->laundry_nama ?? '-',
            ]
        ));
    }

    public function getPdf(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return Pdf::loadView('pdf.report-penggajian', array_merge(
            $this->data($request, $dari, $sampai),
            ['dari' => $dari, 'sampai' => $sampai]
        ))->setPaper('a4', 'landscape')->download("laporan-penggajian-{$dari}-{$sampai}.pdf");
    }
}
