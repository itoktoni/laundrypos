<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use App\Enums\MesinJenisEnum;
use App\Enums\MesinStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Carbon;

#[Fillable(['mesin_kode', 'mesin_nama', 'mesin_jenis', 'mesin_merk', 'mesin_kapasitas', 'mesin_harga', 'mesin_umur_tahun', 'mesin_nilai_residu', 'mesin_tanggal_beli', 'mesin_interval_hari', 'mesin_status', 'mesin_keterangan'])]
class Mesin extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'mesin';

    protected $primaryKey = 'mesin_id';

    protected $attributes = [
        'mesin_status' => 'aktif',
        'mesin_interval_hari' => 90,
        'mesin_harga' => 0,
        'mesin_umur_tahun' => 5,
        'mesin_nilai_residu' => 0,
    ];

    public static $filterColumns = ['mesin_kode', 'mesin_nama', 'mesin_jenis', 'mesin_status'];

    public static $sortColumns = ['mesin_kode', 'mesin_nama', 'mesin_tanggal_beli'];

    public static function field_name(): string
    {
        return 'mesin_nama';
    }

    protected function casts(): array
    {
        return [
            'mesin_jenis' => MesinJenisEnum::class,
            'mesin_status' => MesinStatusEnum::class,
            'mesin_harga' => 'decimal:2',
            'mesin_umur_tahun' => 'integer',
            'mesin_nilai_residu' => 'decimal:2',
            'mesin_tanggal_beli' => 'date',
            'mesin_interval_hari' => 'integer',
        ];
    }

    public function rules(): array
    {
        return [
            'mesin_kode' => ['required', 'string', 'max:30'],
            'mesin_nama' => ['required', 'string', 'max:100'],
            'mesin_jenis' => ['required', 'string', 'in:'.implode(',', MesinJenisEnum::getValues())],
            'mesin_merk' => ['nullable', 'string', 'max:100'],
            'mesin_kapasitas' => ['nullable', 'string', 'max:30'],
            'mesin_harga' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'mesin_umur_tahun' => ['nullable', 'integer', 'min:1', 'max:50'],
            'mesin_nilai_residu' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'mesin_tanggal_beli' => ['nullable', 'date'],
            'mesin_interval_hari' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'mesin_status' => ['nullable', 'string', 'in:'.implode(',', MesinStatusEnum::getValues())],
            'mesin_keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function hasServices()
    {
        return $this->hasMany(MesinService::class, 'service_id_mesin', 'mesin_id');
    }

    public function hasLastRutin()
    {
        return $this->hasOne(MesinService::class, 'service_id_mesin', 'mesin_id')
            ->where('service_jenis', 'rutin')->latestOfMany('service_tanggal');
    }

    // ponytail: jadwal interval-based on-read — acuan = rutin terakhir
    // (fallback tanggal beli), tanpa tabel jadwal & tanpa cron.
    public function getTanggalTerakhirAttribute(): ?string
    {
        $last = $this->hasLastRutin()->value('service_tanggal');

        return $last ?? $this->mesin_tanggal_beli?->toDateString();
    }

    public function getJatuhTempoAttribute(): ?string
    {
        $acuan = $this->tanggal_terakhir;
        $interval = (int) ($this->mesin_interval_hari ?? 0);
        if (! $acuan || $interval <= 0) {
            return null;
        }

        return Carbon::parse($acuan)->addDays($interval)->toDateString();
    }

    public function getSisaHariAttribute(): ?int
    {
        $tempo = $this->jatuh_tempo;
        if (! $tempo) {
            return null;
        }

        return (int) Carbon::today()->diffInDays(Carbon::parse($tempo), false);
    }

    public function getStatusServiceAttribute(): string
    {
        $sisa = $this->sisa_hari;
        if ($sisa === null) {
            return 'tanpa_jadwal';
        }
        if ($sisa < 0) {
            return 'terlambat';
        }
        if ($sisa <= 7) {
            return 'jatuh_tempo';
        }

        return 'tepat_waktu';
    }

    // ponytail: penyusutan garis lurus on-read —
    // (harga - residu) / umur, diakui per bulan sejak tanggal beli,
    // nilai buku tidak pernah di bawah residu.
    public function getSusutPerTahunAttribute(): float
    {
        $umur = (int) ($this->mesin_umur_tahun ?? 0);
        if ($umur <= 0) {
            return 0;
        }

        return round(max((float) ($this->mesin_harga ?? 0) - (float) ($this->mesin_nilai_residu ?? 0), 0) / $umur, 2);
    }

    public function getSusutPerBulanAttribute(): float
    {
        return round($this->susut_per_tahun / 12, 2);
    }

    public function getBulanBerjalanAttribute(): int
    {
        if (! $this->mesin_tanggal_beli || (int) ($this->mesin_umur_tahun ?? 0) <= 0) {
            return 0;
        }

        $lewat = Carbon::parse($this->mesin_tanggal_beli->toDateString())->startOfMonth()
            ->diffInMonths(Carbon::today()->startOfMonth());

        return min(max((int) $lewat, 0), (int) $this->mesin_umur_tahun * 12);
    }

    public function getAkumulasiSusutAttribute(): float
    {
        return round($this->susut_per_bulan * $this->bulan_berjalan, 2);
    }

    public function getNilaiBukuAttribute(): float
    {
        $harga = (float) ($this->mesin_harga ?? 0);
        $residu = (float) ($this->mesin_nilai_residu ?? 0);

        return round(max($harga - $this->akumulasi_susut, min($residu, $harga)), 2);
    }
}
