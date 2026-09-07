<?php /** @var App\Models\Inventory $model */ ?>

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
                <th>Nama</th>
                <th>Satuan</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Stok</th>
                <th class="text-right">Hrg Rata-rata</th>
                <th class="text-right">Hrg Total</th>
                <th>Kartu</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    @php
                        $qtyMasuk = (int) ($table->ledger_qty_masuk ?? 0);
                        $avg = $qtyMasuk > 0 ? round((float) ($table->ledger_nominal_masuk ?? 0) / $qtyMasuk, 2) : 0;
                        $stok = (int) ($table->ledger_stok ?? 0);
                        $total = round($stok * $avg, 2);
                        $low = $table->inventory_min_stok > 0 && $stok <= $table->inventory_min_stok;
                    @endphp
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td>
                            {{ $table->inventory_nama }}
                            @if ($low)
                                <span class="badge badge-error ml-1">Menipis</span>
                            @endif
                        </td>
                        <td>{{ $table->inventory_satuan }}</td>
                        <td class="text-right">{{ formatQty($table->inventory_harga) }}</td>
                        <td class="text-right font-medium">{{ formatAngka($stok) }}</td>
                        <td class="text-right">{{ formatQty($avg) }}</td>
                        <td class="text-right font-medium">{{ formatQty($total) }}</td>
                        <td>
                            <a href="{{ route('inventory.getKartuStok', $table->field_primary) }}" class="btn btn-sm btn-soft">Kartu Stok</a>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    @php
                        $qtyMasuk = (int) ($table->ledger_qty_masuk ?? 0);
                        $avg = $qtyMasuk > 0 ? round((float) ($table->ledger_nominal_masuk ?? 0) / $qtyMasuk, 2) : 0;
                        $stok = (int) ($table->ledger_stok ?? 0);
                    @endphp
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->inventory_nama }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Stok</p>
                                <p class="text-xs font-medium text-primary">{{ formatAngka($stok) }} {{ $table->inventory_satuan }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Rata-rata</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatQty($avg) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <a href="{{ route('inventory.getKartuStok', $table->field_primary) }}" class="btn btn-sm btn-soft">Kartu Stok</a>
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
