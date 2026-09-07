<?php /** @var App\Models\Inventory $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="inventory_nama" helper="Contoh: Deterjen Rinso, Pewangi, Pelembut, Plastik" />
                <x-select col="6" name="inventory_satuan" :options="$satuanOptions" />
                <x-input col="6" type="number" step="0.01" min="0" name="inventory_harga" helper="Harga beli per satuan (jadi acuan nominal movement)" />
                @if (!isset($model) || !$model->exists)
                    <x-input col="6" type="number" min="0" name="stok_awal" helper="Stok awal langsung tercatat sebagai Masuk pertama (qty × harga)" />
                @endif
                <x-input col="6" type="number" min="0" name="inventory_min_stok" helper="Batas minimum untuk badge Menipis" />
                <x-textarea col="6" name="inventory_keterangan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
