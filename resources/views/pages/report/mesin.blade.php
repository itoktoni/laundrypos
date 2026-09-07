<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Laporan Mesin']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex-1 min-w-0">
                    <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight">Laporan Mesin</h1>
                    <p class="text-body-sm text-on-surface-variant">Service {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
                </div>
                <div class="shrink-0 w-full sm:w-auto sm:ml-auto">
                    <a href="{{ route('report.mesin.getPdf', ['dari' => $dari, 'sampai' => $sampai]) }}" class="btn btn-sm btn-primary w-full sm:w-auto text-center">PDF</a>
                </div>
            </div>

            <div class="rounded-2xl border border-outline-variant overflow-hidden bg-gradient-to-br from-primary/15 via-surface-container-low to-surface-container-low">
                <div class="p-4 flex flex-col gap-3">
                    <div class="min-w-0">
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Total nilai buku</p>
                        <p class="text-3xl font-bold text-on-surface break-words">Rp&nbsp;{{ formatQty($totalNilaiBuku) }}</p>
                    </div>
                    <div class="flex flex-row gap-2">
                        <div class="flex-1 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Biaya service</p>
                            <p class="text-sm font-bold text-on-surface">Rp&nbsp;{{ formatQty($totalBiaya) }}</p>
                        </div>
                        <div class="flex-1 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Service</p>
                            <p class="text-sm font-bold text-on-surface">{{ $services->count() }}x</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('report.mesin.getIndex') }}" class="bg-surface-container-low rounded-xl border border-outline-variant p-3 flex flex-col gap-2">
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
                    <h2 class="font-title-md text-title-md text-on-surface leading-tight">Posisi Mesin</h2>
                    <p class="text-body-xs text-on-surface-variant">{{ $mesins->count() }} mesin</p>
                </div>
                <div class="flex flex-col divide-y divide-outline-variant">
                    @forelse ($mesins as $mesin)
                        <a href="{{ route('mesin.getSusut', $mesin->field_primary) }}" class="flex items-center justify-between gap-2 px-4 py-3 hover:bg-surface-container-lowest transition-colors">
                            <span class="text-body-sm text-on-surface min-w-0">{{ $mesin->mesin_nama }} <span class="font-mono text-on-surface-variant">{{ $mesin->mesin_kode }}</span></span>
                            <span class="text-body-sm font-bold text-on-surface text-right shrink-0">Rp&nbsp;{{ formatQty($mesin->nilai_buku) }}</span>
                        </a>
                    @empty
                        <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Belum ada mesin.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant">
                    <h2 class="font-title-md text-title-md text-on-surface leading-tight">Riwayat Service</h2>
                    <p class="text-body-xs text-on-surface-variant">{{ $services->count() }} service</p>
                </div>
                {{-- Mobile: cards --}}
                <div class="flex flex-col gap-2 p-3 sm:hidden">
                    @forelse ($services as $service)
                        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest/60 px-3 py-2.5">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-bold text-on-surface truncate">{{ $service->hasMesin?->mesin_nama ?? '-' }}</span>
                                @if ($service->service_is_selesai)
                                    <span class="badge badge-success shrink-0">selesai</span>
                                @else
                                    <span class="badge badge-warning shrink-0">terbuka</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] text-on-surface-variant truncate">{{ $service->service_jenis?->description ?? $service->service_jenis }} &bull; {{ formatDate($service->service_tanggal) }}</span>
                                <span class="text-sm font-bold text-on-surface shrink-0">Rp&nbsp;{{ formatQty($service->service_biaya) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Tidak ada service pada periode ini.</p>
                    @endforelse
                </div>
                {{-- Desktop: table --}}
                <div class="overflow-x-auto hidden sm:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container">
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Tanggal</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Mesin</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Jenis</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Status</th>
                                <th class="px-4 py-3 text-right text-label-sm text-on-surface-variant font-medium">Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($services as $service)
                                <tr class="border-b border-outline-variant last:border-0 hover:bg-surface-container-lowest transition-colors">
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ formatDate($service->service_tanggal) }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ $service->hasMesin?->mesin_nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface-variant">{{ $service->service_jenis?->description ?? $service->service_jenis }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface-variant">{{ $service->service_is_selesai ? 'Selesai' : 'Terbuka' }}</td>
                                    <td class="px-4 py-3 text-right text-body-sm font-medium text-on-surface">Rp&nbsp;{{ formatQty($service->service_biaya) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Tidak ada service pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
