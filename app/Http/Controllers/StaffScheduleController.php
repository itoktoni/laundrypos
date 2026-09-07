<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\StaffSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// ponytail: jadwal kerja per user per hari (1=Senin..7=Minggu) + import CSV.
class StaffScheduleController extends Controller
{
    use ControllerTrait;

    public function __construct(StaffSchedule $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $laundryId = session('laundry_id');
        $userQuery = User::orderBy('name');
        if ($laundryId) {
            $ids = DB::table('laundry_user')->where('laundry_id', $laundryId)->pluck('user_id');
            $userQuery->whereIn('id', $ids);
        }

        return array_merge([
            'model' => $this->model,
            'userOptions' => $userQuery->pluck('name', 'id')->all(),
            'hariOptions' => StaffSchedule::hariOptions(),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->with('hasUser')->filter()->sort()->orderBy('schedule_tanggal')->orderBy('schedule_id_user');
    }

    public function getImport(Request $request)
    {
        return $this->views($this->template('import'), []);
    }

    public function getTemplate(Request $request)
    {
        $csv = "email,tanggal,jam_masuk,jam_pulang\n";
        $csv .= "kasir@laundry.test,2026-09-08,08:00,20:00\n";
        $csv .= "kasir@laundry.test,2026-09-11,10:00,22:00\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-jadwal.csv"',
        ]);
    }

    // ponytail: terima Y-m-d, d/m/Y, d-m-Y — normalisasi ke Y-m-d.
    protected function parseTanggal(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'] as $fmt) {
            try {
                $dt = \Carbon\Carbon::createFromFormat($fmt, $raw);
                if ($dt && $dt->format($fmt) === $raw) {
                    return $dt->toDateString();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    public function postImport(GeneralRequest $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $laundryId = session('laundry_id');
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return $this->response($this->payload(TOAST_FAILED, 'File tidak bisa dibaca.'));
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $rowNum = 0;

        while (($cols = fgetcsv($handle, 0, ',')) !== false) {
            $rowNum++;
            $cols = array_map('trim', $cols);
            if ($rowNum === 1 && isset($cols[0]) && ! filter_var($cols[0], FILTER_VALIDATE_EMAIL) && ! is_numeric($cols[0])) {
                continue; // lewati baris header
            }
            if (count(array_filter($cols)) === 0) {
                continue; // baris kosong
            }

            $tanggal = $this->parseTanggal($cols[1] ?? '');
            $validator = Validator::make(
                ['email' => $cols[0] ?? '', 'masuk' => $cols[2] ?? '', 'pulang' => $cols[3] ?? ''],
                ['email' => ['required', 'email'], 'masuk' => ['required', 'date_format:H:i'], 'pulang' => ['required', 'date_format:H:i']]
            );
            if ($tanggal === null) {
                $errors[] = "Baris {$rowNum}: tanggal '{$cols[1]}' tidak valid (pakai YYYY-MM-DD / DD/MM/YYYY).";

                continue;
            }
            if ($validator->fails()) {
                $errors[] = "Baris {$rowNum}: ".implode(', ', $validator->errors()->all());

                continue;
            }

            $user = User::where('email', $cols[0])->first();
            if (! $user) {
                $errors[] = "Baris {$rowNum}: user '{$cols[0]}' tidak ditemukan.";

                continue;
            }

            $existing = StaffSchedule::withoutGlobalScopes()
                ->where('schedule_id_laundry', $laundryId)
                ->where('schedule_id_user', $user->getKey())
                ->whereDate('schedule_tanggal', $tanggal)
                ->first();

            $payload = [
                'schedule_id_laundry' => $laundryId,
                'schedule_id_user' => $user->getKey(),
                'schedule_tanggal' => $tanggal,
                'schedule_jam_masuk' => $cols[2].':00',
                'schedule_jam_pulang' => $cols[3].':00',
            ];

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                StaffSchedule::create($payload);
                $created++;
            }
        }
        fclose($handle);

        $message = "Import selesai: {$created} baru, {$updated} diperbarui.";
        if (! empty($errors)) {
            return $this->response($this->payload(TOAST_FAILED, $message.' Gagal: '.implode(' | ', array_slice($errors, 0, 5))));
        }

        flash()->success($message);

        return redirect()->route('staff-schedule.getTable');
    }
}
