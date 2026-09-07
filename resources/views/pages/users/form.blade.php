<?php /** @var App\Models\Users $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="name" />
                <x-input col="6" name="email" />
                <x-input col="6" type="password" name="password" />
                <x-select col="6" name="role" :options="$role"/>
                <x-input col="6" type="number" name="gaji_pokok" label="Gaji Pokok (Rp)" />
                <x-input col="6" type="number" name="gaji_absensi" label="Insentif / Hari Hadir (Rp)" />
                <x-input col="6" type="number" name="denda_terlambat" label="Denda Terlambat (Rp)" />
                <x-input col="6" type="number" name="denda_checkout" label="Denda Tanpa Checkout (Rp)" />

                <x-file
                    name="avatar"
                    label="Foto Profil"
                    col="12"
                    accept="image/*"
                    capture="environment"
                    :preview="true"
                    :value="$model?->avatar_url"
                    helper="Ambil foto via kamera di HP atau pilih dari galeri" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
