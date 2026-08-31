<?php /** @var App\Models\Discount $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="discount_nama" />
                <x-input col="6" name="discount_kode" />
                <x-select col="6" name="discount_tipe" :options="['persen' => 'Persen (%)', 'nominal' => 'Nominal (Rp)']" />
                <x-input col="6" name="discount_nilai" type="number" step="0.01" />
                <x-input col="6" name="discount_min_pembelian" type="number" step="0.01" />
                <x-input col="6" name="discount_max_diskon" type="number" step="0.01" />
                <x-input col="6" name="discount_mulai" type="date" />
                <x-input col="6" name="discount_selesai" type="date" />
                <x-toggle col="6" name="discount_is_aktif" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
