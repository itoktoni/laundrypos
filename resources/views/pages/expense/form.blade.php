<?php /** @var App\Models\Expense $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="expense_nama" label="Nama Pengeluaran" />
                <x-select col="6" name="expense_kategori" :options="$kategoriOptions" label="Kategori" />
                <x-input col="6" name="expense_nominal" type="number" step="0.01" label="Nominal (Rp)" />
                <x-input col="6" name="expense_tanggal" type="date" label="Tanggal" />
                <x-select col="6" name="expense_metode_pembayaran" :options="$metodeOptions" label="Metode Pembayaran" />
                <x-textarea col="12" name="expense_catatan" label="Catatan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
