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

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-1">{{ $table->order_status_nama }}</p>
                        <p class="text-xs font-mono text-on-surface-variant mb-3">Urutan #{{ $table->order_status_urutan }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Warna</p>
                                <p class="text-xs flex items-center gap-1.5"><span class="w-3 h-3 rounded-full border border-outline-variant" style="background: {{ $table->order_status_warna }}"></span> {{ $table->order_status_warna }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Batal / Selesai</p>
                                <p class="text-xs flex gap-1 justify-end"><span class="badge {{ $table->order_status_is_batal ? 'badge-error' : 'badge-ghost' }} text-[10px]">{{ $table->order_status_is_batal ? 'Batal' : '—' }}</span><span class="badge {{ $table->order_status_is_selesai ? 'badge-success' : 'badge-ghost' }} text-[10px]">{{ $table->order_status_is_selesai ? 'Selesai' : '—' }}</span></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="text-[9px] font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">#{{ $table->field_primary }}</span>
                            <div class="flex gap-1" onclick="event.stopPropagation()">
                                <x-table-action :model="$model" :id="$table->field_primary" />
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-slot:mobile>

        </x-table>

        <x-pagination :paginator="$data" />
        <x-action :model="$model" :action="['create', 'delete']"/>
    </div>
</x-layouts::app>
