<?php /** @var App\Models\Kategori $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <x-filter :per-page="25" :fields="$fields">
            <x-slot:advanced>
                @foreach ($fields as $key => $advance)
                    <x-filter-item :label="$advance" :name="$key"/>
                @endforeach

                <x-button variant="primary" class="btn-block" onclick="applyAdvanced()">Apply</x-button>
                <x-button variant="soft" class="btn-block" onclick="resetAdvanced()">Reset</x-button>
            </x-slot:advanced>
        </x-filter>

        <x-table>
            <x-slot:head>
                <th>Actions</th>
                <x-table-sort field="kategori_nama" label="Nama" />
                <th>Aktif</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td>{{ $table->kategori_nama }}</td>
                        <td>
                            <span class="badge {{ $table->kategori_is_aktif ? 'badge-success' : 'badge-error' }}">
                                {{ $table->kategori_is_aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>

        {{ $data->links() }}
    </div>
</x-layouts::app>
