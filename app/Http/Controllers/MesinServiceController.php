<?php

namespace App\Http\Controllers;

use App\Actions\CreateAction;
use App\Actions\UpdateAction;
use App\Concerns\ControllerTrait;
use App\Enums\MesinStatusEnum;
use App\Enums\ServiceJenisEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\Mesin;
use App\Models\MesinService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MesinServiceController extends Controller
{
    // ponytail: tanpa trait aliasing — method alias publik ikut terdaftar
    // sebagai route oleh Route::auto, jadi body trait di-inline.
    use ControllerTrait;

    public function __construct(MesinService $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $selectedId = request()->input('service_id_mesin', request()->route('id'));

        return array_merge([
            'model' => $this->model,
            'mesinOptions' => Mesin::orderBy('mesin_nama')->pluck('mesin_nama', 'mesin_id')->all(),
            'jenisOptions' => ServiceJenisEnum::getOptions(),
            'selectedMesin' => $selectedId ? Mesin::find($selectedId) : null,
        ], $data);
    }

    protected function getData()
    {
        return $this->model->leftJoinRelationship('hasMesin')->filter()->sort()->latest('mesin_service.service_tanggal');
    }

    public function postCreate(GeneralRequest $request)
    {
        if (! $request->input('service_tanggal')) {
            $request->merge(['service_tanggal' => now()->toDateString()]);
        }

        $foto = $this->handleFoto($request, null);
        if ($foto !== null) {
            $request->merge(['service_foto' => $foto]);
        }

        $response = CreateAction::run($request, $this->model);

        return $this->response($response);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $service = $this->model->findOrFail($id);
        $existing = $service->service_foto ?? null;

        $foto = $this->handleFoto($request, $existing);
        if ($foto !== $existing) {
            $request->merge(['service_foto' => $foto]);
        }

        $response = UpdateAction::run($request, $id, $this->model);

        return $this->response($response);
    }

    // Selesaikan WO: wajib tindakan + teknisi, kembalikan mesin ke aktif.
    public function postSelesai(GeneralRequest $request, $id)
    {
        $validated = $request->validate([
            'service_tindakan' => ['required', 'string', 'max:500'],
            'service_teknisi' => ['required', 'string', 'max:100'],
            'service_biaya' => ['nullable', 'numeric', 'min:0'],
        ]);

        $response = DB::transaction(function () use ($id, $validated, $request) {
            /** @var MesinService $service */
            $service = $this->model->findOrFail($id);

            $foto = $this->handleFoto($request, $service->service_foto ?? null);

            $service->update(array_merge($validated, [
                'service_foto' => $foto ?? $service->service_foto,
                'service_is_selesai' => true,
            ]));

            $service->hasMesin()->update(['mesin_status' => MesinStatusEnum::AKTIF]);

            return $this->payload(TOAST_SUCCESS, $service->fresh());
        });

        return $this->response($response);
    }

    private function handleFoto(GeneralRequest $request, ?string $existing): ?string
    {
        if ($request->hasFile('service_foto')) {
            try {
                $path = uploadFile($request->file('service_foto'), 'mesin-service', ['max_size' => 2048]);
                $this->deleteServiceFile($existing);

                return $path;
            } catch (\InvalidArgumentException $e) {
                throw ValidationException::withMessages(['service_foto' => $e->getMessage()]);
            }
        }

        if ($request->boolean('remove_service_foto')) {
            $this->deleteServiceFile($existing);

            return null;
        }

        return $existing;
    }

    private function deleteServiceFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }
        $file = storage_path('app/public/'.$path);
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
