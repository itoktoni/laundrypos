<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Laporan Absensi']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex-1 min-w-0">
                    <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight">Laporan Absensi</h1>
                    <p class="text-body-sm text-on-surface-variant">{{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
                </div>
                <div class="shrink-0 w-full sm:w-auto sm:ml-auto">
                    <a href="{{ route('report.absensi.getPdf', ['dari' => $dari, 'sampai' => $sampai, 'user' => $userId, 'upah' => $upah]) }}" class="btn btn-sm btn-primary w-full sm:w-auto text-center">PDF</a>
                </div>
            </div>

            <div class="rounded-2xl border border-outline-variant overflow-hidden bg-gradient-to-br from-primary/15 via-surface-container-low to-surface-container-low">
                <div class="p-4 flex flex-col gap-3">
                    <div class="flex items-end justify-between gap-2 flex-wrap">
                        <div class="min-w-0">
                            <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Kehadiran</p>
                            <p class="text-3xl font-bold text-on-surface">{{ formatAngka($hadir) }} <span class="text-base font-medium text-on-surface-variant">hadir</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Est. gaji</p>
                            <p class="text-xl font-bold text-primary">Rp&nbsp;{{ formatQty(collect($payrolls)->sum('total')) }}</p>
                        </div>
                    </div>
                    <a href="{{ route('report.penggajian.getIndex') }}" class="flex items-center justify-between gap-2 rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 px-3 py-2">
                        <span class="text-body-xs text-on-surface-variant">Rincian gaji per karyawan</span>
                        <span class="text-xs font-bold text-primary">Penggajian &rarr;</span>
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('report.absensi.getIndex') }}" class="bg-surface-container-low rounded-xl border border-outline-variant p-3 flex flex-col gap-2">
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
                    <div class="flex flex-col sm:flex-row gap-2">
                        <label class="sm:flex-1 text-body-xs text-on-surface-variant">Karyawan
                            <select name="user" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                                <option value="">Semua</option>
                                @foreach ($userOptions as $id => $nama)
                                    <option value="{{ $id }}" @selected((string) $userId === (string) $id)>{{ $nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="sm:flex-1 text-body-xs text-on-surface-variant">Upah harian (Rp)
                            <input type="number" min="0" name="upah" value="{{ $upah }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
            </form>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant">
                    <h2 class="font-title-md text-title-md text-on-surface leading-tight">Penggajian</h2>
                    <p class="text-body-xs text-on-surface-variant">Hadir &times; Rp&nbsp;{{ formatQty($upah) }} / hari</p>
                </div>
                <div class="flex flex-col divide-y divide-outline-variant">
                    @forelse ($payrolls as $pay)
                        <div class="flex items-center justify-between gap-2 px-4 py-3">
                            <span class="text-body-sm text-on-surface min-w-0">{{ $pay['nama'] }}
                                <span class="text-body-xs text-on-surface-variant block">{{ $pay['hadir'] }} hadir &bull; {{ $pay['izin'] }} izin &bull; {{ $pay['sakit'] }} sakit</span>
                            </span>
                            <span class="text-body-sm font-bold text-primary text-right shrink-0">Rp&nbsp;{{ formatQty($pay['total']) }}</span>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Tidak ada data.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                <div class="px-4 py-3 border-b border-outline-variant">
                    <h2 class="font-title-md text-title-md text-on-surface leading-tight">Detail Harian</h2>
                    <p class="text-body-xs text-on-surface-variant">{{ $rows->count() }} record</p>
                </div>
                {{-- Mobile: cards --}}
                <div class="flex flex-col gap-2 p-3 sm:hidden">
                    @forelse ($rows as $row)
                        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest/60 px-3 py-2.5">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-bold text-on-surface truncate">{{ $row->hasUser?->name ?? '-' }}</span>
                                @if ($row->attendance_status === 'hadir')
                                    <span class="badge badge-success shrink-0">hadir</span>
                                @elseif ($row->attendance_status === 'izin')
                                    <span class="badge badge-warning shrink-0">izin</span>
                                @else
                                    <span class="badge badge-error shrink-0">{{ $row->attendance_status }}</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] text-on-surface-variant">{{ formatDate($row->attendance_tanggal) }}</span>
                                <span class="text-xs font-medium text-on-surface shrink-0">{{ $row->attendance_checkin_at ? $row->attendance_checkin_at->format('H:i') : '-' }} &ndash; {{ $row->attendance_checkout_at ? $row->attendance_checkout_at->format('H:i') : '-' }}</span>
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
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Karyawan</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Check-in</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Check-out</th>
                                <th class="px-4 py-3 text-left text-label-sm text-on-surface-variant font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-outline-variant last:border-0 hover:bg-surface-container-lowest transition-colors">
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ formatDate($row->attendance_tanggal) }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ $row->hasUser?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ $row->attendance_checkin_at ? $row->attendance_checkin_at->format('H:i') : '-' }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface">{{ $row->attendance_checkout_at ? $row->attendance_checkout_at->format('H:i') : '-' }}</td>
                                    <td class="px-4 py-3 text-body-sm text-on-surface-variant">{{ ucfirst($row->attendance_status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-body-sm text-on-surface-variant">Tidak ada data pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
