<?php /** @var App\Models\Mesin $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="mesin_kode" helper="Kode unik per cabang, mis. WSH-01, DRY-02" />
                <x-input col="6" name="mesin_nama" helper="Contoh: Washer LG 15kg Depan" />
                <x-select col="6" name="mesin_jenis" :options="$jenisOptions" />
                <x-select col="6" name="mesin_status" :options="$statusOptions" />
                <x-input col="6" name="mesin_merk" />
                <x-input col="6" name="mesin_kapasitas" helper="Contoh: 15 kg" />
                <x-input col="6" type="number" step="0.01" min="0" name="mesin_harga" helper="Harga perolehan untuk penyusutan garis lurus" />
                <x-input col="3" type="number" min="1" max="50" name="mesin_umur_tahun" helper="Umur manfaat (tahun)" />
                <x-input col="3" type="number" step="0.01" min="0" name="mesin_nilai_residu" helper="Nilai sisa akhir umur" />
                <x-input col="6" type="date" name="mesin_tanggal_beli" />
                <x-input col="6" type="number" min="1" max="3650" name="mesin_interval_hari" helper="Interval service rutin (hari). Default 90" />
                <x-textarea col="12" name="mesin_keterangan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
