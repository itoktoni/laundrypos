<?php /** @var App\Models\Mesin $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <div class="mb-3 flex gap-2">
            <a href="{{ route('mesin.getJadwal') }}" class="btn btn-soft">Jadwal Service</a>
        </div>

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
                <th>Kode</th>
                <th>Nama</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Nilai Buku</th>
                <th>Status</th>
                <th class="text-right">Service Berikutnya</th>
                <th>Aksi Cepat</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td class="font-mono">{{ $table->mesin_kode }}</td>
                        <td>{{ $table->mesin_nama }}</td>
                        <td class="text-right">{{ formatQty($table->mesin_harga) }}</td>
                        <td class="text-right font-medium" title="Susut {{ formatQty($table->susut_per_bulan) }}/bln × {{ $table->bulan_berjalan }} bln"><a href="{{ route('mesin.getSusut', $table->field_primary) }}" class="link link-primary">{{ formatQty($table->nilai_buku) }}</a></td>
                        <td>
                            <span class="badge {{ $table->mesin_status?->value === 'aktif' ? 'badge-success' : ($table->mesin_status?->value === 'rusak' ? 'badge-error' : 'badge-warning') }}">
                                {{ $table->mesin_status?->description ?? $table->mesin_status }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if ($table->jatuh_tempo)
                                {{ formatDate($table->jatuh_tempo) }}
                            @else
                                <span class="text-on-surface-variant">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-1 whitespace-nowrap">
                                <a href="{{ route('mesin.getSusut', $table->field_primary) }}" class="btn btn-sm btn-soft">Susut</a>
                                @if ($table->mesin_status?->value === 'aktif')
                                    <form method="POST" action="{{ route('mesin.postRusak', $table->field_primary) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-soft" onclick="return confirm('Tandai mesin ini RUSAK dan buat WO darurat?')">Rusak</button>
                                    </form>
                                @else
                                    <a href="{{ route('mesin-service.getCreate') }}?service_id_mesin={{ $table->field_primary }}" class="btn btn-sm btn-primary">Service</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->mesin_nama }} <span class="font-mono font-normal text-on-surface-variant">{{ $table->mesin_kode }}</span></p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Status</p>
                                <p class="text-xs font-medium"><span class="badge {{ $table->mesin_status?->value === 'aktif' ? 'badge-success' : 'badge-error' }}">{{ $table->mesin_status?->description ?? $table->mesin_status }}</span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Service Berikutnya</p>
                                <p class="text-xs font-medium text-on-surface">{{ $table->jatuh_tempo ? formatDate($table->jatuh_tempo) : '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <div class="flex gap-1">
                                <a href="{{ route('mesin.getSusut', $table->field_primary) }}" class="btn btn-sm btn-soft">Susut</a>
                                @if ($table->mesin_status?->value === 'aktif')
                                    <form method="POST" action="{{ route('mesin.postRusak', $table->field_primary) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-soft" onclick="return confirm('Tandai mesin ini RUSAK dan buat WO darurat?')">Rusak</button>
                                    </form>
                                @else
                                    <a href="{{ route('mesin-service.getCreate') }}?service_id_mesin={{ $table->field_primary }}" class="btn btn-sm btn-primary">Service</a>
                                @endif
                            </div>
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

    <input type="hidden" class="module" value="{{ Str::beforeLast(request()->route()->uri(), '/') }}">
    <script src="/js/table.js"></script>
</x-layouts::app>
