<?php /** @var App\Models\OrderStatus $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <x-table>
            <x-slot:head>
                <th>Actions</th>
                <th>Urutan</th>
                <th>Nama Status</th>
                <th>Warna</th>
                <th>Batal</th>
                <th>Selesai</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td>{{ $table->order_status_urutan }}</td>
                        <td class="font-medium">{{ $table->order_status_nama }}</td>
                        <td>
                            <span class="inline-flex items-center gap-1">
                                <span class="w-4 h-4 rounded-full border border-outline-variant" style="background: {{ $table->order_status_warna }}"></span>
                                <span class="text-xs text-on-surface-variant">{{ $table->order_status_warna }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $table->order_status_is_batal ? 'badge-error' : 'badge-ghost' }}">
                                {{ $table->order_status_is_batal ? 'Ya' : 'Tidak' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $table->order_status_is_selesai ? 'badge-success' : 'badge-ghost' }}">
                                {{ $table->order_status_is_selesai ? 'Ya' : 'Tidak' }}
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
