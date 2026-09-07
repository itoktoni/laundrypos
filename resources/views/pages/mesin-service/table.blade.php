<?php /** @var App\Models\MesinService $model */ ?>

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
                <th>No. WO</th>
                <th>Tanggal</th>
                <th>Mesin</th>
                <th>Jenis</th>
                <th>Teknisi</th>
                <th class="text-right">Biaya</th>
                <th>Status</th>
                <th>Selesai</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td class="font-mono">{{ $table->service_nomor }}</td>
                        <td>{{ formatDate($table->service_tanggal) }}</td>
                        <td>
                            <a href="{{ route('mesin.getTable') }}" class="link link-primary">{{ $table->hasMesin?->mesin_nama ?? '-' }}</a>
                        </td>
                        <td>
                            <span class="badge {{ $table->service_jenis?->value === 'rutin' ? 'badge-success' : ($table->service_jenis?->value === 'darurat' ? 'badge-error' : 'badge-warning') }}">
                                {{ $table->service_jenis?->description ?? $table->service_jenis }}
                            </span>
                        </td>
                        <td>{{ $table->service_teknisi ?? '-' }}</td>
                        <td class="text-right">{{ formatQty($table->service_biaya) }}</td>
                        <td>
                            @if ($table->service_is_selesai)
                                <span class="badge badge-success">Selesai</span>
                            @else
                                <span class="badge badge-warning">Open</span>
                            @endif
                        </td>
                        <td>
                            @if (!$table->service_is_selesai)
                                <a href="{{ route('mesin-service.getUpdate', $table->field_primary) }}" class="btn btn-sm btn-primary">Selesaikan</a>
                            @else
                                <span class="text-on-surface-variant">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->service_nomor }} <span class="font-normal text-on-surface-variant">{{ $table->hasMesin?->mesin_nama ?? '' }}</span></p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Tanggal</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatDate($table->service_tanggal) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Biaya</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatQty($table->service_biaya) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            @if ($table->service_is_selesai)
                                <span class="badge badge-success">Selesai</span>
                            @else
                                <span class="badge badge-warning">Open</span>
                            @endif
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
