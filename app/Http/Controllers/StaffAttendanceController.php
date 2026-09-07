<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Laundry;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffAttendanceController extends Controller
{
    use ControllerTrait;

    public function __construct(StaffAttendance $model)
    {
        $this->model = $model::getModel();
    }

    protected function getData()
    {
        $q = $this->model->with(['hasUser', 'hasLaundry'])->orderByDesc('attendance_tanggal')->orderByDesc('attendance_checkin_at');
        // Staff lihat hanya miliknya
        if ((auth()->user()->role ?? '') === 'editor') {
            $q->where('attendance_id_user', auth()->id());
        }
        return $q;
    }

    public function getCheckin(Request $request)
    {
        $laundryId = session('laundry_id');
        $laundry = Laundry::find($laundryId);
        $today = now()->toDateString();
        $existing = StaffAttendance::where('attendance_id_laundry', $laundryId)
            ->where('attendance_id_user', auth()->id())
            ->where('attendance_tanggal', $today)->first();

        $storeLat = $laundry?->laundry_latitude ?? config('website.store_latitude', '-6.2000000');
        $storeLng = $laundry?->laundry_longitude ?? config('website.store_longitude', '106.8166660');
        $radius = $laundry?->laundry_radius_m ?? config('website.store_radius', 50);

        return $this->views('pages.staff-attendance.checkin', [
            'laundry' => $laundry,
            'existing' => $existing,
            'storeLat' => $storeLat,
            'storeLng' => $storeLng,
            'radius' => $radius,
        ]);
    }

    public function postCheckin(Request $request)
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $laundryId = session('laundry_id');
        $laundry = Laundry::findOrFail($laundryId);
        $storeLat = (float) ($laundry->laundry_latitude ?? config('website.store_latitude'));
        $storeLng = (float) ($laundry->laundry_longitude ?? config('website.store_longitude'));
        $radius = (int) ($laundry->laundry_radius_m ?? config('website.store_radius', 50));

        $lat = (float) $request->input('lat');
        $lng = (float) $request->input('lng');
        $jarak = StaffAttendance::hitungJarak($storeLat, $storeLng, $lat, $lng);
        $valid = $jarak <= $radius;

        if (! $valid) {
            return back()->withErrors(['jarak' => "Jarak $jarak m melebihi radius $radius m. Dekatkan ke toko."])->withInput();
        }

        $today = now()->toDateString();
        $existing = StaffAttendance::where('attendance_id_laundry', $laundryId)
            ->where('attendance_id_user', auth()->id())
            ->where('attendance_tanggal', $today)->first();

        if ($existing && $existing->attendance_checkin_at) {
            return back()->withErrors(['checkin' => 'Sudah check-in hari ini.']);
        }

        DB::transaction(function () use ($laundryId, $today, $lat, $lng, $jarak, $valid, $existing) {
            if ($existing) {
                $existing->update([
                    'attendance_checkin_at' => now(),
                    'attendance_checkin_lat' => $lat,
                    'attendance_checkin_lng' => $lng,
                    'attendance_checkin_jarak' => $jarak,
                    'attendance_checkin_valid' => $valid,
                ]);
            } else {
                StaffAttendance::create([
                    'attendance_id_laundry' => $laundryId,
                    'attendance_id_user' => auth()->id(),
                    'attendance_tanggal' => $today,
                    'attendance_checkin_at' => now(),
                    'attendance_checkin_lat' => $lat,
                    'attendance_checkin_lng' => $lng,
                    'attendance_checkin_jarak' => $jarak,
                    'attendance_checkin_valid' => $valid,
                    'attendance_status' => 'hadir',
                ]);
            }
        });

        flash()->success('Check-in berhasil — jarak ' . $jarak . ' m (valid).');
        return redirect()->route('staff-attendance.getTable');
    }

    public function postCheckout(Request $request)
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $laundryId = session('laundry_id');
        $laundry = Laundry::findOrFail($laundryId);
        $storeLat = (float) ($laundry->laundry_latitude ?? config('website.store_latitude'));
        $storeLng = (float) ($laundry->laundry_longitude ?? config('website.store_longitude'));
        $radius = (int) ($laundry->laundry_radius_m ?? config('website.store_radius', 50));

        $lat = (float) $request->input('lat');
        $lng = (float) $request->input('lng');
        $jarak = StaffAttendance::hitungJarak($storeLat, $storeLng, $lat, $lng);
        $valid = $jarak <= $radius;

        if (! $valid) {
            return back()->withErrors(['jarak' => "Jarak $jarak m melebihi radius $radius m."])->withInput();
        }

        $today = now()->toDateString();
        $att = StaffAttendance::where('attendance_id_laundry', $laundryId)
            ->where('attendance_id_user', auth()->id())
            ->where('attendance_tanggal', $today)->first();

        if (! $att || ! $att->attendance_checkin_at) {
            return back()->withErrors(['checkout' => 'Belum check-in hari ini.']);
        }
        if ($att->attendance_checkout_at) {
            return back()->withErrors(['checkout' => 'Sudah checkout hari ini.']);
        }

        $att->update([
            'attendance_checkout_at' => now(),
            'attendance_checkout_lat' => $lat,
            'attendance_checkout_lng' => $lng,
            'attendance_checkout_jarak' => $jarak,
            'attendance_checkout_valid' => $valid,
        ]);

        flash()->success('Check-out berhasil — jarak ' . $jarak . ' m.');
        return redirect()->route('staff-attendance.getTable');
    }
}
