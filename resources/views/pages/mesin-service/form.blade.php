<?php /** @var App\Models\MesinService $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-select col="6" name="service_id_mesin" :options="$mesinOptions" />
                <x-input col="6" type="date" name="service_tanggal" />
                <x-select col="6" name="service_jenis" :options="$jenisOptions" />
                <x-input col="6" name="service_teknisi" helper="Nama teknisi / vendor" />
                <x-textarea col="6" name="service_keluhan" />
                <x-textarea col="6" name="service_tindakan" />
                <x-input col="6" type="number" step="0.01" min="0" name="service_biaya" />
                <x-file name="service_foto" col="6" accept="image/*" capture="environment"
                    :preview="true" :value="$model?->foto_url"
                    helper="Foto kondisi mesin / nota via kamera HP" />
                <x-textarea col="12" name="service_keterangan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>

    @if (isset($model) && $model->exists && !$model->service_is_selesai)
        <form method="POST" action="{{ route('mesin-service.postSelesai', $model->field_primary) }}" class="mt-3">
            @csrf
            <x-card label="Selesaikan WO">
                <x-textarea col="6" name="service_tindakan" />
                <x-input col="3" name="service_teknisi" />
                <x-input col="3" type="number" step="0.01" min="0" name="service_biaya" />
                <div class="col-span-12">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Tandai WO selesai dan kembalikan mesin ke Aktif?')">Selesaikan WO & Aktifkan Mesin</button>
                </div>
            </x-card>
        </form>
    @endif

    @if (!empty($selectedMesin))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var sel = document.querySelector('[name="service_id_mesin"]');
                if (sel && !sel.value) { sel.value = "{{ $selectedMesin->field_primary }}"; sel.dispatchEvent(new Event('change')); }
            });
        </script>
    @endif
</x-layouts::app>
