<?php /** @var App\Models\Product $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="product_nama" />
                <x-select col="6" name="product_id_kategori" :options="$kategoriOptions" />
                <x-select col="6" name="product_satuan" :options="$satuanOptions" />
                <x-input col="6" type="number" step="0.01" min="0.01" name="product_harga_dasar" />
                <x-input col="6" type="number" min="1" max="720" name="product_estimasi_jam" helper="Estimasi durasi pengerjaan (jam)" />
                <x-toggle col="6" name="product_is_aktif" />
                <x-textarea col="12" name="product_deskripsi" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
