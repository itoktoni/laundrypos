<?php /** @var App\Models\Laundry $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Cabang Laundry']]" />
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
                <th>Nama</th>
                <th>Kode</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th>Lokasi</th>
                <th>Aktif</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td class="font-medium">{{ $table->laundry_nama }}</td>
                        <td><span class="font-mono text-xs">{{ $table->laundry_kode }}</span></td>
                        <td class="max-w-xs truncate">{{ $table->laundry_alamat ?? '-' }}</td>
                        <td>{{ $table->laundry_telepon ?? '-' }}</td>
                        <td class="text-xs font-mono">
                            @if($table->laundry_latitude)
                                {{ $table->laundry_latitude }}, {{ $table->laundry_longitude }} ({{ $table->laundry_radius_m }}m)
                            @else
                                <span class="text-on-surface-variant">—</span>
                            @endif
                        </td>
                        <td>@if($table->laundry_is_aktif)<span class="badge badge-success badge-sm">Aktif</span>@else<span class="badge badge-warning badge-sm">Nonaktif</span>@endif</td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->laundry_nama }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Kode</p>
                                <p class="text-xs font-mono font-medium text-primary">{{ $table->laundry_kode }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Telepon</p>
                                <p class="text-xs font-medium text-on-surface">{{ $table->laundry_telepon ?? '-' }}</p>
                            </div>
                            <div class="col-span-2 text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Alamat</p>
                                <p class="text-xs font-medium text-on-surface line-clamp-2">{{ $table->laundry_alamat ?? '-' }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Status</p>
                                <p class="text-xs">@if($table->laundry_is_aktif)<span class="badge badge-success badge-sm">Aktif</span>@else<span class="badge badge-warning badge-sm">Nonaktif</span>@endif</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Lokasi</p>
                                <p class="text-[10px] font-mono text-on-surface-variant truncate">@if($table->laundry_latitude){{ $table->laundry_latitude }}, {{ $table->laundry_longitude }}@else — @endif</p>
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

    <input type="hidden" class="module" value="{{ Str::beforeLast(request()->route()->uri(), '/') }}">
    <script src="/js/table.js"></script>
</x-layouts::app>
