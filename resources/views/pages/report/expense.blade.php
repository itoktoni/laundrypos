<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Laporan Pengeluaran']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex-1 min-w-0">
                    <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight">Laporan Pengeluaran</h1>
                    <p class="text-body-sm text-on-surface-variant">{{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
                </div>
                <div class="shrink-0 w-full sm:w-auto sm:ml-auto">
                    <a href="{{ route('report.expense.getPdf', ['dari' => $dari, 'sampai' => $sampai, 'kategori' => $kategori]) }}" class="btn btn-sm btn-primary w-full sm:w-auto text-center">PDF</a>
                </div>
            </div>

            <div class="rounded-2xl border border-outline-variant overflow-hidden bg-gradient-to-br from-primary/15 via-surface-container-low to-surface-container-low">
                <div class="p-4 flex flex-col gap-3">
                    <div class="min-w-0">
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Total pengeluaran</p>
                        <p class="text-3xl font-bold text-on-surface break-words">Rp&nbsp;{{ formatQty($total) }}</p>
                    </div>
                    <div class="flex flex-col divide-y divide-outline-variant/60 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 overflow-hidden">
                        @forelse ($perKategori->take(4) as $nama => $nominal)
                            <div class="flex items-center justify-between gap-2 px-3 py-2">
                                <span class="text-body-xs text-on-surface-variant truncate">{{ $nama }}</span>
                                <span class="text-xs font-bold text-on-surface text-right shrink-0">Rp&nbsp;{{ formatQty($nominal) }}</span>
                            </div>
                        @empty
                            <p class="px-3 py-4 text-center text-body-xs text-on-surface-variant">Belum ada pengeluaran.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('report.expense.getIndex') }}" class="bg-surface-container-low rounded-xl border border-outline-variant p-3 flex flex-col gap-2">
                <p class="text-body-sm font-bold text-on-surface">Filter</p>
                <div class="flex flex-col gap-2">
                    <div class="flex flex-row gap-2">
                        <label class="flex-1 min-w-0 text-body-xs text-on-surface-variant">Dari
                            <input type="date" name="dari" value="{{ $dari }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                        <label class="flex-1 min-w-0 text-body-xs text-on-surface-variant">Sampai
                            <input type="date" name="sampai" value="{{ $sampai }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                    </div>
                    <label class="text-body-xs text-on-surface-variant">Kategori
                        <select name="kategori" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                            <option value="">Semua</option>
                            @foreach ($kategoriOptions as $key => $label)
                                <option value="{{ $key }}" @selected($kategori === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
            </form>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant">
                    <h2 class="font-title-md text-title-md text-on-surface leading-tight">Transaksi</h2>
                    <p class="text-body-xs text-on-surface-variant">{{ $expenses->count() }} pengeluaran</p>
                </div>
                {{-- Mobile: cards --}}
                <div class="flex flex-col gap-2 p-3 sm:hidden">
                    @forelse ($expenses as $expense)
                        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest/60 px-3 py-2.5">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-bold text-on-surface truncate">{{ $expense->expense_nama }}</span>
                                <span class="text-sm font-bold text-on-surface shrink-0">Rp&nbsp;{{ formatQty($expense->expense_nominal) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="badge">{{ $expense->expense_kategori }}</span>
                                <span class="text-[11px] text-on-surface-variant shrink-0">{{ formatDate($expense->expense_tanggal) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Tidak ada data pada periode ini.</p>
                    @endforelse
                </div>
                {{-- Desktop: table --}}
                <div class="overflow-x-auto hidden sm:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container">
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Tanggal</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Nama</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Kategori</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expenses as $expense)
                                <tr class="border-b border-outline-variant last:border-0 hover:bg-surface-container-lowest transition-colors">
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ formatDate($expense->expense_tanggal) }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ $expense->expense_nama }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface-variant">{{ $expense->expense_kategori }}</td>
                                    <td class="px-4 py-3 text-right text-body-sm font-medium text-on-surface">Rp&nbsp;{{ formatQty($expense->expense_nominal) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Tidak ada data pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
