<?php /** @var App\Models\Order $model */ ?>

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
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th class="text-right">Total</th>
                <th>Tanggal</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    @php $warna = $table->hasStatus?->order_status_warna ?? '#9aa0a6'; @endphp
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td><a class="link font-mono" href="{{ route('order.getShow', ['id' => $table->field_primary]) }}">{{ $table->order_code }}</a></td>
                        <td>{{ $table->hasCustomer?->customer_nama ?? $table->order_walkin_nama ?? '-' }}</td>
                        <td>
                            <span class="badge font-medium" style="border: 1px solid {{ $warna }}; background: color-mix(in srgb, {{ $warna }} 12%, transparent); color: {{ $warna }}">
                                {{ $table->hasStatus?->order_status_nama ?? '-' }}
                            </span>
                        </td>
                        <td class="text-right font-bold">{{ formatAngka($table->order_total) }}</td>
                        <td class="text-on-surface-variant">{{ formatDate($table->created_at, true) }}</td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    @php $warna = $table->hasStatus?->order_status_warna ?? '#9aa0a6'; @endphp
                    <div class="border border-outline-variant rounded-2xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <a class="text-sm font-bold text-primary truncate" href="{{ route('order.getShow', ['id' => $table->field_primary]) }}">{{ $table->order_code }}</a>
                            <p class="text-sm font-bold text-on-surface shrink-0">{{ formatAngka($table->order_total) }}</p>
                        </div>
                        <p class="text-xs text-on-surface-variant truncate mb-3">{{ $table->hasCustomer?->customer_nama ?? $table->order_walkin_nama ?? '-' }}</p>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="badge font-medium" style="border: 1px solid {{ $warna }}; background: color-mix(in srgb, {{ $warna }} 12%, transparent); color: {{ $warna }}">{{ $table->hasStatus?->order_status_nama ?? '-' }}</span>
                            <p class="text-xs text-on-surface-variant shrink-0">{{ formatDate($table->created_at, true) }}</p>
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
