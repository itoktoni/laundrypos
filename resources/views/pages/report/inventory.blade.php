<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Laporan Inventory']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex-1 min-w-0">
                    <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight">Laporan Inventory</h1>
                    <p class="text-body-sm text-on-surface-variant">Mutasi {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
                </div>
                <div class="shrink-0 w-full sm:w-auto sm:ml-auto">
                    <a href="{{ route('report.inventory.getPdf', ['dari' => $dari, 'sampai' => $sampai]) }}" class="btn btn-sm btn-primary w-full sm:w-auto text-center">PDF</a>
                </div>
            </div>

            <div class="rounded-2xl border border-outline-variant overflow-hidden bg-gradient-to-br from-primary/15 via-surface-container-low to-surface-container-low">
                <div class="p-4 flex flex-col gap-3">
                    <div class="min-w-0">
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Total nilai stok</p>
                        <p class="text-3xl font-bold text-on-surface break-words">Rp&nbsp;{{ formatQty($totalNilai) }}</p>
                    </div>
                    <div class="flex flex-row gap-2">
                        <div class="flex-1 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-wide text-green-600 mb-0.5">Masuk</p>
                            <p class="text-sm font-bold text-on-surface">+{{ formatAngka($qtyMasuk) }}</p>
                            <p class="text-[11px] text-on-surface-variant">Rp&nbsp;{{ formatQty($masuk) }}</p>
                        </div>
                        <div class="flex-1 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-wide text-red-600 mb-0.5">Keluar</p>
                            <p class="text-sm font-bold text-on-surface">-{{ formatAngka($qtyKeluar) }}</p>
                            <p class="text-[11px] text-on-surface-variant">periode ini</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('report.inventory.getIndex') }}" class="bg-surface-container-low rounded-xl border border-outline-variant p-3 flex flex-col gap-2">
                <p class="text-body-sm font-bold text-on-surface">Filter</p>
                <div class="flex flex-row gap-2">
                    <label class="flex-1 min-w-0 text-body-xs text-on-surface-variant">Dari
                        <input type="date" name="dari" value="{{ $dari }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                    </label>
                    <label class="flex-1 min-w-0 text-body-xs text-on-surface-variant">Sampai
                        <input type="date" name="sampai" value="{{ $sampai }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                    </label>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
            </form>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant">
                    <h2 class="font-title-md text-title-md text-on-surface leading-tight">Posisi Stok</h2>
                    <p class="text-body-xs text-on-surface-variant">{{ $rows->count() }} barang</p>
                </div>
                {{-- Mobile: cards --}}
                <div class="flex flex-col gap-2 p-3 sm:hidden">
                    @forelse ($rows as $row)
                        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest/60 px-3 py-2.5">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-bold text-on-surface truncate">{{ $row['nama'] }}</span>
                                @if ($row['min_stok'] > 0 && $row['stok'] <= $row['min_stok'])
                                    <span class="badge badge-error shrink-0">Menipis</span>
                                @endif
                            </div>
                            <div class="flex flex-row gap-2">
                                <div class="flex-1">
                                    <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Stok</p>
                                    <p class="text-sm font-bold text-on-surface">{{ formatAngka($row['stok']) }} {{ $row['satuan'] }}</p>
                                </div>
                                <div class="flex-1 text-right">
                                    <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Nilai</p>
                                    <p class="text-sm font-bold text-primary">Rp&nbsp;{{ formatQty($row['total']) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Belum ada barang.</p>
                    @endforelse
                </div>
                {{-- Desktop: table --}}
                <div class="overflow-x-auto hidden sm:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container">
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Barang</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Stok</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Rata-rata</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-outline-variant last:border-0 hover:bg-surface-container-lowest transition-colors">
                                    <td class="px-4 py-3 text-body-sm text-on-surface">
                                        {{ $row['nama'] }}
                                        @if ($row['min_stok'] > 0 && $row['stok'] <= $row['min_stok'])
                                            <span class="badge badge-error ml-1">Menipis</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-body-sm font-medium text-on-surface">{{ formatAngka($row['stok']) }} {{ $row['satuan'] }}</td>
                                    <td class="px-4 py-3 text-right text-body-sm text-on-surface-variant">Rp&nbsp;{{ formatQty($row['rata_rata']) }}</td>
                                    <td class="px-4 py-3 text-right text-body-sm font-medium text-on-surface">Rp&nbsp;{{ formatQty($row['total']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Belum ada barang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
