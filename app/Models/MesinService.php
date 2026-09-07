<?php

namespace App\Models;

use App\Concerns\BelongsToLaundry;
use App\Enums\ServiceJenisEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['service_id_mesin', 'service_nomor', 'service_tanggal', 'service_jenis', 'service_keluhan', 'service_tindakan', 'service_teknisi', 'service_biaya', 'service_foto', 'service_is_selesai', 'service_keterangan'])]
class MesinService extends BaseModel
{
    use BelongsToLaundry;

    protected $table = 'mesin_service';

    protected $primaryKey = 'service_id';

    protected $attributes = [
        'service_biaya' => 0,
        'service_is_selesai' => false,
    ];

    public static $filterColumns = ['service_nomor', 'service_jenis', 'service_teknisi'];

    public static $sortColumns = ['service_tanggal', 'service_id'];

    public static function field_name(): string
    {
        return 'service_nomor';
    }

    protected function casts(): array
    {
        return [
            'service_jenis' => ServiceJenisEnum::class,
            'service_tanggal' => 'date',
            'service_biaya' => 'decimal:2',
            'service_is_selesai' => 'boolean',
        ];
    }

    public function rules(): array
    {
        return [
            'service_id_mesin' => ['required', 'integer', 'exists:mesin,mesin_id'],
            'service_nomor' => ['nullable', 'string', 'max:30'],
            'service_tanggal' => ['required', 'date'],
            'service_jenis' => ['required', 'string', 'in:'.implode(',', ServiceJenisEnum::getValues())],
            'service_keluhan' => ['nullable', 'string', 'max:500'],
            'service_tindakan' => ['nullable', 'string', 'max:500'],
            'service_teknisi' => ['nullable', 'string', 'max:100'],
            'service_biaya' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'service_foto' => ['nullable', 'string', 'max:255'],
            'service_is_selesai' => ['nullable', 'boolean'],
            'service_keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected static function booted(): void
    {
        // Nomor WO otomatis bila kosong; rutin selalu langsung selesai.
        static::creating(function (self $model) {
            if (empty($model->service_nomor)) {
                $model->service_nomor = 'WO-'.now()->format('ymd').'-'.unicString(4);
            }
            if ($model->service_jenis?->value === ServiceJenisEnum::RUTIN && ! $model->service_is_selesai) {
                $model->service_is_selesai = true;
            }
        });
    }

    public function hasMesin()
    {
        return $this->belongsTo(Mesin::class, 'service_id_mesin', 'mesin_id');
    }

    public function getFotoUrlAttribute(): string
    {
        return fileUrl($this->service_foto);
    }
}
