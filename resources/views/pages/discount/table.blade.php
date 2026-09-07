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

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->discount_nama }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Kode</p>
                                <p class="text-xs font-mono font-medium text-primary">{{ $table->discount_kode }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Tipe</p>
                                <p class="text-xs font-medium text-on-surface">{{ $table->discount_tipe === 'persen' ? 'Persen' : 'Nominal' }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Nilai</p>
                                <p class="text-xs font-mono font-semibold text-on-surface">{{ $table->discount_tipe === 'persen' ? $table->discount_nilai.'%' : formatAngka($table->discount_nilai) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Min. Beli</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatAngka($table->discount_min_pembelian) }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Status</p>
                                <p class="text-xs"><span class="badge {{ $table->discount_is_aktif ? 'badge-success' : 'badge-error' }}">{{ $table->discount_is_aktif ? 'Aktif' : 'Nonaktif' }}</span></p>
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
