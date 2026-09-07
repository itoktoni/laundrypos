<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['schedule_id_laundry', 'schedule_id_user', 'schedule_tanggal', 'schedule_jam_masuk', 'schedule_jam_pulang'])]
class StaffSchedule extends BaseModel
{
    protected static function booted(): void
    {
        static::addGlobalScope('laundry', function ($builder): void {
            $laundryId = session('laundry_id');
            if ($laundryId) {
                $builder->where('schedule_id_laundry', $laundryId);
            }
        });
        static::creating(function ($model): void {
            if (! $model->schedule_id_laundry && session('laundry_id')) {
                $model->schedule_id_laundry = session('laundry_id');
            }
        });
    }

    protected $table = 'staff_schedule';

    protected $primaryKey = 'schedule_id';

    public static $filterColumns = ['schedule_tanggal'];

    public static $sortColumns = ['schedule_id_user', 'schedule_tanggal'];

    public static function field_name(): string
    {
        return 'schedule_tanggal';
    }

    public function rules(): array
    {
        return [
            'schedule_id_user' => ['required', 'integer', 'exists:users,id'],
            'schedule_tanggal' => ['required', 'date'],
            'schedule_jam_masuk' => ['required', 'date_format:H:i'],
            'schedule_jam_pulang' => ['required', 'date_format:H:i'],
        ];
    }

    protected function casts(): array
    {
        return [
            'schedule_tanggal' => 'date',
        ];
    }

    public static function hariOptions(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }

    public function getHariLabelAttribute(): string
    {
        if (! $this->schedule_tanggal) {
            return '-';
        }

        return static::hariOptions()[(int) \Carbon\Carbon::parse($this->schedule_tanggal)->dayOfWeekIso] ?? '-';
    }

    public function hasUser()
    {
        return $this->belongsTo(User::class, 'schedule_id_user', 'id');
    }

    public function hasLaundry()
    {
        return $this->belongsTo(Laundry::class, 'schedule_id_laundry', 'laundry_id');
    }

    // ponytail: nilai absen terhadap jadwal — terlambat bila check-in lewat
    // jam masuk; tanpa-checkout hanya dihitung bila sudah lewat waktunya
    // (tanggal lampau, atau hari ini setelah jam pulang).
    public static function evaluate(StaffAttendance $row, $jadwal = null): array
    {
        $jadwal ??= static::withoutGlobalScopes()
            ->where('schedule_id_laundry', $row->attendance_id_laundry)
            ->where('schedule_id_user', $row->attendance_id_user)
            ->whereDate('schedule_tanggal', $row->attendance_tanggal)
            ->first();

        if (! $jadwal || $row->attendance_status !== 'hadir') {
            return ['terlambat' => false, 'menitTerlambat' => 0, 'noCheckout' => false, 'jadwal' => $jadwal];
        }

        $masuk = substr((string) $jadwal->schedule_jam_masuk, 0, 5);
        $pulang = substr((string) $jadwal->schedule_jam_pulang, 0, 5);
        $menitTerlambat = 0;
        $terlambat = false;
        if ($row->attendance_checkin_at) {
            $ci = $row->attendance_checkin_at->format('H:i');
            if ($ci > $masuk) {
                $terlambat = true;
                [$mh, $mm] = array_map('intval', explode(':', $masuk));
                [$ch, $cm] = array_map('intval', explode(':', $ci));
                $menitTerlambat = max(($ch * 60 + $cm) - ($mh * 60 + $mm), 0);
            }
        }

        $today = now()->toDateString();
        $tgl = \Carbon\Carbon::parse($row->attendance_tanggal)->toDateString();
        $due = $tgl < $today || ($tgl === $today && now()->format('H:i') > $pulang);
        $noCheckout = $due && $row->attendance_checkout_at === null;

        return ['terlambat' => (bool) $terlambat, 'menitTerlambat' => $menitTerlambat, 'noCheckout' => (bool) $noCheckout, 'jadwal' => $jadwal];
    }
}
