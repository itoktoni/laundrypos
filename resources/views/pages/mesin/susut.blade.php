<?php /** @var App\Models\Mesin $item */ ?>
@php
    $harga = (float) ($item->mesin_harga ?? 0);
    $residu = min((float) ($item->mesin_nilai_residu ?? 0), $harga);
    $disusutkan = max($harga - $residu, 0);
    $akum = (float) $item->akumulasi_susut;
    $persen = $disusutkan > 0 ? min(round($akum / $disusutkan * 100, 1), 100) : 0;
    $umur = max((int) ($item->mesin_umur_tahun ?? 0), 0);
    $bulanJalan = (int) $item->bulan_berjalan;
    $totalBulan = $umur * 12;
    $tahunBerjalan = $totalBulan > 0 ? min((int) ceil(max($bulanJalan, 1) / 12), max($umur, 1)) : 1;
    $sisaBulan = max($totalBulan - $bulanJalan, 0);
    $isAktif = $item->mesin_status?->value === 'aktif';
@endphp

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Penyusutan']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ moduleRoute('getTable') }}" class="w-9 h-9 bg-surface-container-low rounded-full flex items-center justify-center hover:bg-surface-container transition-colors shrink-0">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div class="flex-1 min-w-0">
                    <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight break-words line-clamp-2">{{ $item->mesin_nama }}</h1>
                    <p class="text-body-sm text-on-surface-variant flex items-center gap-2 flex-wrap">
                        <span class="font-mono">{{ $item->mesin_kode }}</span>
                        <span class="badge {{ $isAktif ? 'badge-success' : 'badge-error' }}">{{ $item->mesin_status?->description ?? $item->mesin_status }}</span>
                    </p>
                </div>
                <div class="flex gap-1 whitespace-nowrap shrink-0 w-full sm:w-auto sm:ml-auto">
                    <a href="{{ route('mesin-service.getCreate') }}?service_id_mesin={{ $item->field_primary }}" class="btn btn-sm btn-primary flex-1 sm:flex-none text-center">Service</a>
                </div>
            </div>

            <div class="rounded-2xl border border-outline-variant overflow-hidden bg-gradient-to-br from-primary/15 via-surface-container-low to-surface-container-low">
                <div class="p-4 flex flex-col gap-4">
                    <div class="flex flex-col gap-3 min-w-0">
                        <div class="min-w-0">
                            <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Nilai buku saat ini</p>
                            <p class="text-2xl font-bold text-on-surface break-words">Rp&nbsp;{{ formatQty($item->nilai_buku) }}</p>
                            <p class="text-body-xs text-on-surface-variant mt-1">dari Rp&nbsp;{{ formatQty($harga) }} &bull; residu Rp&nbsp;{{ formatQty($residu) }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-2 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2">
                            <span class="text-body-xs text-on-surface-variant">Tersusutkan {{ $persen }}%</span>
                            <span class="text-sm font-bold text-primary">Rp&nbsp;{{ formatQty($akum) }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="h-2.5 rounded-full bg-surface-container-highest overflow-hidden">
                            <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $persen }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-body-xs text-on-surface-variant">{{ $bulanJalan }} dari {{ $totalBulan }} bulan</span>
                            <span class="text-body-xs text-on-surface-variant">{{ $sisaBulan }} bln tersisa</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1 flex items-center justify-between gap-2 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Susut / bulan</p>
                            <p class="text-sm font-bold text-on-surface text-right">Rp&nbsp;{{ formatQty($item->susut_per_bulan) }}</p>
                        </div>
                        <div class="flex-1 flex items-center justify-between gap-2 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Susut / tahun</p>
                            <p class="text-sm font-bold text-on-surface text-right">Rp&nbsp;{{ formatQty($item->susut_per_tahun) }}</p>
                        </div>
                        <div class="flex-1 flex items-center justify-between gap-2 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2.5">
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Umur manfaat</p>
                            <p class="text-sm font-bold text-on-surface text-right">{{ $umur }} thn</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">receipt_long</span>
                    <div>
                        <h2 class="font-title-md text-title-md text-on-surface leading-tight">Ringkasan Garis Lurus</h2>
                        <p class="text-body-xs text-on-surface-variant">Dihitung per bulan sejak tanggal beli</p>
                    </div>
                </div>
                <div class="flex flex-col divide-y divide-outline-variant">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-body-sm text-on-surface-variant">Harga perolehan</span>
                        <span class="text-body-sm font-medium text-on-surface">Rp&nbsp;{{ formatQty($item->mesin_harga) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-body-sm text-on-surface-variant">Nilai residu</span>
                        <span class="text-body-sm font-medium text-on-surface">Rp&nbsp;{{ formatQty($item->mesin_nilai_residu) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-body-sm text-on-surface-variant">Tanggal beli</span>
                        <span class="text-body-sm font-medium text-on-surface">{{ $item->mesin_tanggal_beli ? formatDate($item->mesin_tanggal_beli) : '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-body-sm text-on-surface-variant">Akumulasi susut</span>
                        <span class="text-body-sm font-medium text-on-surface">Rp&nbsp;{{ formatQty($item->akumulasi_susut) }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 bg-primary/5">
                        <span class="text-body-sm font-medium text-on-surface">Nilai buku saat ini</span>
                        <span class="text-body-sm font-bold text-primary">Rp&nbsp;{{ formatQty($item->nilai_buku) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">calendar_month</span>
                    <div>
                        <h2 class="font-title-md text-title-md text-on-surface leading-tight">Jadwal per Tahun</h2>
                        <p class="text-body-xs text-on-surface-variant">Tahun {{ $tahunBerjalan }} sedang berjalan</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 p-3">
                    @php
                        $perTahun = (float) $item->susut_per_tahun;
                        $akumRow = 0;
                    @endphp
                    @forelse (range(1, max($umur, 1)) as $th)
                        @php
                            $akumRow = round(min($akumRow + $perTahun, $disusutkan), 2);
                            $buku = round(max($harga - $akumRow, $residu), 2);
                            $isCurrent = $th === $tahunBerjalan;
                            $isPast = $th < $tahunBerjalan;
                        @endphp
                        <div class="rounded-xl border {{ $isCurrent ? 'border-primary/40 bg-primary/5' : 'border-outline-variant/60 bg-surface-container-lowest/60' }} px-3 py-2.5">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-sm font-bold text-on-surface">Tahun {{ $th }}</span>
                                @if ($isCurrent)
                                    <span class="badge badge-success">berjalan</span>
                                @elseif ($isPast)
                                    <span class="badge">selesai</span>
                                @else
                                    <span class="text-body-xs text-on-surface-variant">mendatang</span>
                                @endif
                            </div>
                            <div class="flex flex-row gap-2">
                                <div class="flex-1">
                                    <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Susut</p>
                                    <p class="text-xs font-medium text-on-surface">Rp&nbsp;{{ formatQty($perTahun) }}</p>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Akumulasi</p>
                                    <p class="text-xs font-medium text-on-surface-variant">Rp&nbsp;{{ formatQty($akumRow) }}</p>
                                </div>
                                <div class="flex-1 text-right">
                                    <p class="text-[10px] uppercase tracking-wide text-on-surface-variant mb-0.5">Nilai buku</p>
                                    <p class="text-xs font-bold text-on-surface">Rp&nbsp;{{ formatQty($buku) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Umur manfaat belum diisi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
