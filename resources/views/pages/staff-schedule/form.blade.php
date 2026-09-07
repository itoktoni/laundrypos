<?php /** @var App\Models\StaffSchedule $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-select col="12" name="schedule_id_user" label="Karyawan" :options="$userOptions" />
                <x-input col="12" type="date" name="schedule_tanggal" label="Tanggal" />
                <x-input col="6" type="time" name="schedule_jam_masuk" label="Jam Masuk" />
                <x-input col="6" type="time" name="schedule_jam_pulang" label="Jam Pulang" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
