<?php /** @var App\Models\Inventory $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Kartu Stok']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ moduleRoute('getTable') }}" class="w-9 h-9 bg-surface-container-low rounded-full flex items-center justify-center hover:bg-surface-container transition-colors shrink-0">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div class="flex-1 min-w-0">
                    <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight truncate">Kartu Stok</h1>
                    <p class="text-body-sm text-on-surface-variant truncate">{{ $model->inventory_nama }}</p>
                </div>
                <div class="shrink-0 w-full sm:w-auto sm:ml-auto">
                    <a href="{{ route('inventory-movement.getCreate') }}?movement_id_inventory={{ $model->field_primary }}" class="btn btn-sm btn-primary w-full sm:w-auto text-center">+ Masuk / Keluar</a>
                </div>
            </div>

            <div class="rounded-2xl border border-outline-variant overflow-hidden bg-gradient-to-br from-primary/15 via-surface-container-low to-surface-container-low">
                <div class="p-4 flex flex-col gap-3">
                    <div class="flex items-end justify-between gap-2 flex-wrap">
                        <div class="min-w-0">
                            <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Stok tersedia</p>
                            <p class="text-3xl font-bold text-on-surface">{{ formatAngka($summary['stok']) }} <span class="text-base font-medium text-on-surface-variant">{{ $summary['satuan'] }}</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Nilai total</p>
                            <p class="text-xl font-bold text-primary">Rp&nbsp;{{ formatQty($summary['total']) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-2 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2">
                        <span class="text-body-xs text-on-surface-variant">Harga rata-rata / {{ $summary['satuan'] }}</span>
                        <span class="text-sm font-bold text-on-surface text-right">Rp&nbsp;{{ formatQty($summary['rata_rata']) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant">
                    <h2 class="font-title-md text-title-md text-on-surface leading-tight">Riwayat Pergerakan</h2>
                    <p class="text-body-xs text-on-surface-variant">{{ count($rows) }} transaksi</p>
                </div>
                {{-- Mobile: cards --}}
                <div class="flex flex-col gap-2 p-3 sm:hidden">
                    @forelse ($rows as $row)
                        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest/60 px-3 py-2.5">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="text-sm font-bold text-on-surface">{{ \Carbon\Carbon::parse($row['tanggal'])->format('j/n/Y') }}</span>
                                @if ($row['tipe'] === 'masuk')
                                    <span class="badge badge-success">masuk</span>
                                @else
                                    <span class="badge badge-error">keluar</span>
                                @endif
                            </div>
                            <div class="flex flex-row gap-2">
                                <div class="flex-1">
                                    <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Qty</p>
                                    <p class="text-sm font-bold {{ $row['qty'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $row['qty'] >= 0 ? '+'.$row['qty'] : $row['qty'] }}</p>
                                </div>
                                <div class="flex-1 text-right">
                                    <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Saldo</p>
                                    <p class="text-sm font-bold text-on-surface">{{ formatAngka($row['saldo']) }}</p>
                                </div>
                                <div class="flex-1 text-right">
                                    <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Rata-rata</p>
                                    <p class="text-xs font-medium text-on-surface-variant">Rp&nbsp;{{ formatQty($row['rata_rata']) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Belum ada pergerakan. Tambahkan stok masuk pertama via tombol di atas.</p>
                    @endforelse
                </div>
                {{-- Desktop: table --}}
                <div class="overflow-x-auto hidden sm:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container">
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">#</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Tanggal</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">UOM</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Tipe</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Qty</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Nominal</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Rata-rata</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-outline-variant last:border-0 hover:bg-surface-container-lowest transition-colors">
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ $row['no'] }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ \Carbon\Carbon::parse($row['tanggal'])->format('j/n/Y') }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface-variant">{{ $row['uom'] }}</td>
                                    <td class="px-4 py-3">
                                        @if ($row['tipe'] === 'masuk')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-label-xs font-medium bg-green-100 text-green-700"><span class="material-symbols-outlined text-[14px]">add_circle</span> Masuk</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-label-xs font-medium bg-red-100 text-red-700"><span class="material-symbols-outlined text-[14px]">remove_circle</span> Keluar</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-body-sm font-medium {{ $row['qty'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $row['qty'] >= 0 ? '+'.$row['qty'] : $row['qty'] }}</td>
                                    <td class="px-4 py-3 text-right text-body-sm text-on-surface">Rp&nbsp;{{ formatQty($row['nominal']) }}</td>
                                    <td class="px-4 py-3 text-right text-body-sm text-on-surface-variant">Rp&nbsp;{{ formatQty($row['rata_rata']) }}</td>
                                    <td class="px-4 py-3 text-right text-body-sm font-medium text-on-surface">{{ formatAngka($row['saldo']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Belum ada pergerakan. Tambahkan stok masuk pertama via tombol di atas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
