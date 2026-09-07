<?php /** @var App\Models\OrderStatus $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="order_status_nama" />
                <x-input col="6" name="order_status_urutan" type="number" min="1" />
                <x-input col="6" name="order_status_warna" type="color" />
                <x-toggle col="6" name="order_status_is_batal" />
                <x-toggle col="6" name="order_status_is_selesai" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
