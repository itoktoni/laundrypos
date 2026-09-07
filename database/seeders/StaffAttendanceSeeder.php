<?php

namespace Database\Seeders;

use App\Models\Laundry;
use App\Models\StaffAttendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $laundries = Laundry::all();
        if ($laundries->isEmpty()) {
            $this->command?->warn('No laundry found. Run LaundryDemoSeeder first.');

            return;
        }

        foreach ($laundries as $laundry) {
            session(['laundry_id' => $laundry->laundry_id]);
            $this->seedForLaundry($laundry);
        }
    }

    private function seedForLaundry(Laundry $laundry): void
    {
        $userIds = DB::table('laundry_user')->where('laundry_id', $laundry->laundry_id)->pluck('user_id');
        if ($userIds->isEmpty()) {
            $this->command?->warn('No users linked to laundry '.$laundry->laundry_nama.', skipped.');

            return;
        }

        // Titik cabang untuk simulasi GPS (Monas) — jarak acak 5–80 m.
        $lat = -6.1754;
        $lng = 106.8272;
        $count = 0;

        // Sebulan penuh sampai hari ini; Minggu libur; status bervariasi
        // agar kalender penggajian terlihat hidup (hadir/izin/sakit).
        $day = now()->startOfMonth();
        $today = now()->startOfDay();

        while ($day <= $today) {
            if (! $day->isSunday()) {
                foreach ($userIds as $userId) {
                    $exists = StaffAttendance::where('attendance_id_laundry', $laundry->laundry_id)
                        ->where('attendance_id_user', $userId)
                        ->whereDate('attendance_tanggal', $day->toDateString())
                        ->exists();
                    if ($exists) {
                        continue;
                    }

                    $roll = rand(1, 100);
                    $status = $roll <= 85 ? 'hadir' : ($roll <= 93 ? 'izin' : 'sakit');

                    $payload = [
                        'attendance_id_user' => $userId,
                        'attendance_tanggal' => $day->toDateString(),
                        'attendance_status' => $status,
                    ];

                    if ($status === 'hadir') {
                        $payload += [
                            'attendance_checkin_at' => $this->timeAt($day, 7, 50, 80),
                            'attendance_checkin_lat' => $lat + rand(-5, 5) / 10000,
                            'attendance_checkin_lng' => $lng + rand(-5, 5) / 10000,
                            'attendance_checkin_jarak' => rand(5, 80),
                            'attendance_checkin_valid' => true,
                            'attendance_checkout_at' => $this->timeAt($day, 17, 0, 20),
                            'attendance_checkout_lat' => $lat + rand(-5, 5) / 10000,
                            'attendance_checkout_lng' => $lng + rand(-5, 5) / 10000,
                            'attendance_checkout_jarak' => rand(5, 80),
                            'attendance_checkout_valid' => true,
                        ];
                    }

                    StaffAttendance::create($payload);
                    $count++;
                }
            }

            $day = $day->addDay();
        }

        $this->command?->info('Seeded '.$count.' attendance rows for laundry #'.$laundry->laundry_id.' ('.$laundry->laundry_nama.')');
    }

    private function timeAt($day, int $hour, int $minBase, int $spread)
    {
        return $day->copy()->setTime($hour, $minBase)->addMinutes(rand(0, $spread));
    }
}
