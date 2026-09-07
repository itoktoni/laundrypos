<?php /** @var App\Models\InventoryMovement $model */ ?>

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
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Tipe</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Nominal</th>
                <th>Keterangan</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td>{{ formatDate($table->movement_tanggal) }}</td>
                        <td>
                            <a href="{{ route('inventory.getKartuStok', $table->movement_id_inventory) }}" class="link link-primary">{{ $table->hasInventory?->inventory_nama ?? '-' }}</a>
                        </td>
                        <td>
                            @if ($table->movement_tipe === 'masuk')
                                <span class="badge badge-success">Masuk</span>
                            @else
                                <span class="badge badge-error">Keluar</span>
                            @endif
                        </td>
                        <td class="text-right font-medium">{{ $table->movement_tipe === 'masuk' ? '+' : '-' }}{{ formatAngka($table->movement_qty) }} {{ $table->movement_uom }}</td>
                        <td class="text-right">{{ formatQty($table->movement_nominal) }}</td>
                        <td>{{ $table->movement_keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->hasInventory?->inventory_nama ?? '-' }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Tanggal</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatDate($table->movement_tanggal) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Qty</p>
                                <p class="text-xs font-medium text-on-surface">{{ $table->movement_tipe === 'masuk' ? '+' : '-' }}{{ formatAngka($table->movement_qty) }} {{ $table->movement_uom }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="badge {{ $table->movement_tipe === 'masuk' ? 'badge-success' : 'badge-error' }}">{{ $table->movement_tipe }}</span>
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
