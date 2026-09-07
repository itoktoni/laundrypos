<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Enums\MesinJenisEnum;
use App\Enums\MesinStatusEnum;
use App\Enums\ServiceJenisEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\Mesin;
use Illuminate\Support\Facades\DB;

class MesinController extends Controller
{
    use ControllerTrait;

    public function __construct(Mesin $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'jenisOptions' => MesinJenisEnum::getOptions(),
            'statusOptions' => MesinStatusEnum::getOptions(),
        ], $data);
    }

    // Jadwal service: jatuh tempo = rutin terakhir + interval, overdue paling atas.
    public function getJadwal(GeneralRequest $request)
    {
        $mesin = $this->model->with('hasLastRutin')->filter()->sort()->get();

        $rows = $mesin->map(function (Mesin $m) {
            return [
                'mesin' => $m,
                'terakhir' => $m->tanggal_terakhir,
                'tempo' => $m->jatuh_tempo,
                'sisa' => $m->sisa_hari,
                'status' => $m->status_service,
            ];
        })->sortBy(function ($row) {
            // tanpa jadwal paling bawah, terlambat paling atas
            return $row['sisa'] ?? 999999;
        })->values();

        return $this->views($this->template('jadwal'), [
            'rows' => $rows,
        ]);
    }

    // Detail penyusutan garis lurus per mesin.
    public function getSusut(GeneralRequest $request, $id)
    {
        $mesin = $this->model->findOrFail($id);

        return $this->views($this->template('susut'), [
            'item' => $mesin,
        ]);
    }

    // Lapor cepat: set mesin rusak + auto-buat WO darurat (open).
    public function postRusak(GeneralRequest $request, $id)
    {
        $validated = $request->validate([
            'keluhan' => ['nullable', 'string', 'max:500'],
        ]);

        $response = DB::transaction(function () use ($id, $validated) {
            $mesin = $this->model->findOrFail($id);
            $mesin->update(['mesin_status' => MesinStatusEnum::RUSAK]);

            $wo = $mesin->hasServices()->create([
                'service_tanggal' => now()->toDateString(),
                'service_jenis' => ServiceJenisEnum::DARURAT,
                'service_keluhan' => $validated['keluhan'] ?? 'Lapor cepat: mesin bermasalah',
                'service_is_selesai' => false,
            ]);

            return $this->payload(TOAST_SUCCESS, $wo);
        });

        return $this->response($response);
    }
}
