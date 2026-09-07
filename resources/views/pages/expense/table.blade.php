<?php /** @var App\Models\Expense $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <x-table>
            <x-slot:head>
                <th>Actions</th>
                <x-table-sort field="expense_nama" label="Nama" />
                <th>Kategori</th>
                <x-table-sort field="expense_nominal" label="Nominal" />
                <x-table-sort field="expense_tanggal" label="Tanggal" />
                <th>Metode</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($data as $table)
                    <tr>
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td>{{ $table->expense_nama }}</td>
                        <td><span class="badge badge-soft badge-info">{{ $table->expense_kategori }}</span></td>
                        <td class="font-mono">{{ formatAngka($table->expense_nominal, 'Rp ') }}</td>
                        <td>{{ formatDate($table->expense_tanggal) }}</td>
                        <td>{{ ucfirst($table->expense_metode_pembayaran) }}</td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->expense_nama }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Kategori</p>
                                <p class="text-xs font-medium"><span class="badge badge-soft badge-info">{{ $table->expense_kategori }}</span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Nominal</p>
                                <p class="text-xs font-mono font-semibold text-on-surface">{{ formatAngka($table->expense_nominal, 'Rp ') }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Tanggal</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatDate($table->expense_tanggal) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Metode</p>
                                <p class="text-xs font-medium text-on-surface">{{ ucfirst($table->expense_metode_pembayaran) }}</p>
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
