<?php

namespace Database\Seeders;

use App\Models\Laundry;
use App\Models\StaffSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffScheduleSeeder extends Seeder
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
        $userIds = DB::table('laundry_user')->where('laundry_id', $laundry->laundry_id)->pluck('user_id')->values();
        if ($userIds->isEmpty()) {
            $this->command?->warn('No users linked to laundry '.$laundry->laundry_nama.', skipped.');

            return;
        }

        // Dua pola shift bergantian per user: pagi (08-20 Sen-Rab, 10-22 Kam-Sab)
        // dan kantor (08-17 Sen-Sab).
        $patterns = [
            'toko' => [1 => ['08:00', '20:00'], 2 => ['08:00', '20:00'], 3 => ['08:00', '20:00'], 4 => ['10:00', '22:00'], 5 => ['10:00', '22:00'], 6 => ['10:00', '22:00']],
            'kantor' => [1 => ['08:00', '17:00'], 2 => ['08:00', '17:00'], 3 => ['08:00', '17:00'], 4 => ['08:00', '17:00'], 5 => ['08:00', '17:00'], 6 => ['08:00', '17:00']],
        ];

        $count = 0;
        $day = now()->startOfMonth();
        $today = now()->startOfDay();

        while ($day <= $today) {
            $iso = (int) $day->dayOfWeekIso;
            if ($iso <= 6) {
                foreach ($userIds as $idx => $userId) {
                    $pattern = $patterns[$idx % 2 === 0 ? 'toko' : 'kantor'];
                    [$masuk, $pulang] = $pattern[$iso];

                    $exists = StaffSchedule::withoutGlobalScopes()
                        ->where('schedule_id_laundry', $laundry->laundry_id)
                        ->where('schedule_id_user', $userId)
                        ->whereDate('schedule_tanggal', $day->toDateString())
                        ->exists();
                    if ($exists) {
                        continue;
                    }

                    StaffSchedule::create([
                        'schedule_id_laundry' => $laundry->laundry_id,
                        'schedule_id_user' => $userId,
                        'schedule_tanggal' => $day->toDateString(),
                        'schedule_jam_masuk' => $masuk.':00',
                        'schedule_jam_pulang' => $pulang.':00',
                    ]);
                    $count++;
                }
            }

            $day = $day->addDay();
        }

        $this->command?->info('Seeded '.$count.' schedule rows for laundry #'.$laundry->laundry_id.' ('.$laundry->laundry_nama.')');
    }
}
