<?php /** @var App\Models\Discount $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <x-table>
            <x-slot:head>
                <th>Actions</th>
                <x-table-sort field="discount_nama" label="Nama" />
                <x-table-sort field="discount_kode" label="Kode" />
                <th>Tipe</th>
                <th>Nilai</th>
                <th>Min. Beli</th>
                <th>Aktif</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td>{{ $table->discount_nama }}</td>
                        <td><span class="badge badge-outline">{{ $table->discount_kode }}</span></td>
                        <td>{{ $table->discount_tipe === 'persen' ? 'Persen' : 'Nominal' }}</td>
                        <td>{{ $table->discount_tipe === 'persen' ? $table->discount_nilai.'%' : formatAngka($table->discount_nilai) }}</td>
                        <td>{{ formatAngka($table->discount_min_pembelian) }}</td>
                        <td>
                            <span class="badge {{ $table->discount_is_aktif ? 'badge-success' : 'badge-error' }}">
                                {{ $table->discount_is_aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>

        <x-pagination :paginator="$data" />
        <x-action :model="$model" :action="['create', 'delete']"/>
    </div>
</x-layouts::app>
