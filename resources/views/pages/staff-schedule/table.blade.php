<?php /** @var App\Models\StaffSchedule $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <div class="mb-3 flex gap-2 flex-wrap">
            <a href="{{ route('staff-schedule.getImport') }}" class="btn btn-soft gap-1.5"><span class="material-symbols-outlined text-[18px]">upload</span> Import CSV</a>
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
                <th>Karyawan</th>
                <th>Tanggal</th>
                <th>Masuk</th>
                <th>Pulang</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td class="font-medium">{{ $table->hasUser?->name ?? '-' }}</td>
                        <td>{{ formatDate($table->schedule_tanggal) }} <span class="text-on-surface-variant text-xs">({{ $table->hari_label }})</span></td>
                        <td class="font-mono">{{ substr($table->schedule_jam_masuk, 0, 5) }}</td>
                        <td class="font-mono">{{ substr($table->schedule_jam_pulang, 0, 5) }}</td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-2xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <p class="text-sm font-bold text-on-surface truncate">{{ $table->hasUser?->name ?? '-' }}</p>
                            <span class="badge shrink-0">{{ $table->hari_label }}</span>
                        </div>
                        <p class="text-xs text-on-surface-variant mb-1 font-mono">{{ formatDate($table->schedule_tanggal) }}</p>
                        <p class="text-xs text-on-surface-variant mb-3 font-mono">{{ substr($table->schedule_jam_masuk, 0, 5) }} &ndash; {{ substr($table->schedule_jam_pulang, 0, 5) }}</p>
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
