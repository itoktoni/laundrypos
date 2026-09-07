<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'attendance_id_laundry', 'attendance_id_user', 'attendance_tanggal',
    'attendance_checkin_at', 'attendance_checkin_lat', 'attendance_checkin_lng', 'attendance_checkin_jarak', 'attendance_checkin_valid',
    'attendance_checkout_at', 'attendance_checkout_lat', 'attendance_checkout_lng', 'attendance_checkout_jarak', 'attendance_checkout_valid',
    'attendance_status',
])]
class StaffAttendance extends BaseModel
{
    protected static function booted(): void
    {
        static::addGlobalScope('laundry', function ($builder): void {
            $laundryId = session('laundry_id');
            if ($laundryId) {
                $builder->where('attendance_id_laundry', $laundryId);
            }
        });
        static::creating(function ($model): void {
            if (! $model->attendance_id_laundry && session('laundry_id')) {
                $model->attendance_id_laundry = session('laundry_id');
            }
        });
    }

    protected $table = 'staff_attendance';
    protected $primaryKey = 'attendance_id';

    public static $filterColumns = ['attendance_tanggal', 'attendance_status'];
    public static $sortColumns = ['attendance_tanggal', 'attendance_checkin_at'];

    protected function casts(): array
    {
        return [
            'attendance_tanggal' => 'date',
            'attendance_checkin_at' => 'datetime',
            'attendance_checkout_at' => 'datetime',
            'attendance_checkin_lat' => 'decimal:7',
            'attendance_checkin_lng' => 'decimal:7',
            'attendance_checkout_lat' => 'decimal:7',
            'attendance_checkout_lng' => 'decimal:7',
            'attendance_checkin_valid' => 'boolean',
            'attendance_checkout_valid' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'attendance_tanggal';
    }

    public function rules(): array
    {
        return [
            'attendance_tanggal' => ['required', 'date'],
            'attendance_status' => ['required', 'in:hadir,izin,sakit'],
        ];
    }

    public function hasUser()
    {
        return $this->belongsTo(User::class, 'attendance_id_user', 'id');
    }

    public function hasLaundry()
    {
        return $this->belongsTo(Laundry::class, 'attendance_id_laundry', 'laundry_id');
    }

    /** Haversine jarak meter */
    public static function hitungJarak(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (int) round($earth * $c);
    }

    public function getIsSudahCheckoutAttribute(): bool
    {
        return $this->attendance_checkout_at !== null;
    }
}
