<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Penggajian']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex-1 min-w-0">
                    <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight">Penggajian</h1>
                    <p class="text-body-sm text-on-surface-variant">{{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
                </div>
                @if ($user && $rows->isNotEmpty())
                    <div class="shrink-0 w-full sm:w-auto sm:ml-auto">
                        <a href="{{ route('report.penggajian.getPdf', ['dari' => $dari, 'sampai' => $sampai, 'user' => $userId, 'pokok' => $pokok, 'bonus' => $bonus, 'potongan' => $potongan, 'denda_terlambat' => $dendaTerlambat, 'denda_checkout' => $dendaCheckout]) }}" class="btn btn-sm btn-primary w-full sm:w-auto text-center">PDF</a>
                    </div>
                @endif
            </div>

            @if ($user && $rows->isEmpty())
                <div class="px-4 py-4 text-center bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-body-sm font-bold text-amber-800">Tidak ada data {{ $user->name }} di cabang {{ $cabangNama }}.</p>
                    <p class="text-body-xs text-amber-700 mt-1">Karyawan ini terdaftar di cabang lain — ganti cabang aktif lewat pemilih cabang, atau pilih periode lain.</p>
                </div>
            @endif

            <form method="GET" action="{{ route('report.penggajian.getIndex') }}" class="bg-surface-container-low rounded-xl border border-outline-variant p-3 flex flex-col gap-2">
                <p class="text-body-sm font-bold text-on-surface">Pilih Karyawan</p>
                <div class="flex flex-col gap-2">
                    <label class="text-body-xs text-on-surface-variant">Karyawan
                        <select name="user" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                            <option value="">-- Pilih --</option>
                            @foreach ($userOptions as $id => $nama)
                                <option value="{{ $id }}" @selected((string) $userId === (string) $id)>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex flex-row gap-2">
                        <label class="flex-1 min-w-0 text-body-xs text-on-surface-variant">Dari
                            <input type="date" name="dari" value="{{ $dari }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                        <label class="flex-1 min-w-0 text-body-xs text-on-surface-variant">Sampai
                            <input type="date" name="sampai" value="{{ $sampai }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <label class="sm:flex-1 text-body-xs text-on-surface-variant">Gaji pokok (Rp)
                            <input type="number" min="0" name="pokok" value="{{ $rawPokok }}" placeholder="{{ formatQty($configPokok) }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                        <label class="sm:flex-1 text-body-xs text-on-surface-variant">Bonus (Rp)
                            <input type="number" min="0" name="bonus" value="{{ $rawBonus }}" placeholder="0" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                        <label class="sm:flex-1 text-body-xs text-on-surface-variant">Insentif / hari hadir (Rp)
                            <input type="number" min="0" name="potongan" value="{{ $rawPotongan }}" placeholder="{{ formatQty($configPotongan) }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <label class="sm:flex-1 text-body-xs text-on-surface-variant">Denda terlambat (Rp)
                            <input type="number" min="0" name="denda_terlambat" value="{{ $rawDendaTerlambat }}" placeholder="{{ formatQty($configDendaTerlambat) }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                        <label class="sm:flex-1 text-body-xs text-on-surface-variant">Denda tanpa checkout (Rp)
                            <input type="number" min="0" name="denda_checkout" value="{{ $rawDendaCheckout }}" placeholder="{{ formatQty($configDendaCheckout) }}" class="mt-1 w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface">
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
            </form>

            @if ($user && $rows->isNotEmpty())
                <div class="rounded-2xl border border-outline-variant overflow-hidden bg-gradient-to-br from-primary/15 via-surface-container-low to-surface-container-low">
                    <div class="p-4 flex flex-col gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-10 h-10 rounded-full bg-primary/15 text-primary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined">person</span>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-body-sm font-bold text-on-surface truncate">{{ $user->name }}</p>
                                <p class="text-body-xs text-on-surface-variant">{{ $hadir }} hadir dari {{ $hariKerja }} hari kerja</p>
                            </div>
                            <span class="badge badge-success shrink-0">{{ $izin + $sakit > 0 ? $izin + $sakit . ' absen' : 'penuh' }}</span>
                        </div>
                        <div>
                            <p class="text-label-sm text-on-surface-variant uppercase tracking-wide mb-1">Total gaji</p>
                            <p class="text-3xl font-bold text-on-surface break-words">Rp&nbsp;{{ formatQty($total) }}</p>
                        </div>
                        <div class="flex flex-col rounded-xl bg-surface-container-lowest/70 border border-outline-variant/60 overflow-hidden divide-y divide-outline-variant/60">
                            <div class="flex items-center justify-between gap-2 px-3 py-2">
                                <span class="text-body-xs text-on-surface-variant">Gaji pokok</span>
                                <span class="text-xs font-medium text-on-surface text-right shrink-0">Rp&nbsp;{{ formatQty($pokok) }}</span>
                            </div>
                            @if ($bonus > 0)
                                <div class="flex items-center justify-between gap-2 px-3 py-2">
                                    <span class="text-body-xs text-on-surface-variant">Bonus</span>
                                    <span class="text-xs font-medium text-on-surface text-right shrink-0">+ Rp&nbsp;{{ formatQty($bonus) }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-2 px-3 py-2">
                                <span class="text-body-xs text-on-surface-variant">Hadir {{ $hadir }} &times; Rp&nbsp;{{ formatQty($potongan) }}</span>
                                <span class="text-xs font-medium text-green-600 text-right shrink-0">+Rp&nbsp;{{ formatQty($insentif) }}</span>
                            </div>
                            @if ($terlambat > 0)
                                <div class="flex items-center justify-between gap-2 px-3 py-2">
                                    <span class="text-body-xs text-on-surface-variant">Terlambat {{ $terlambat }} &times; Rp&nbsp;{{ formatQty($dendaTerlambat) }}</span>
                                    <span class="text-xs font-medium text-red-600 text-right shrink-0">&minus;Rp&nbsp;{{ formatQty($potTerlambat) }}</span>
                                </div>
                            @endif
                            @if ($noCheckout > 0)
                                <div class="flex items-center justify-between gap-2 px-3 py-2">
                                    <span class="text-body-xs text-on-surface-variant">Tanpa checkout {{ $noCheckout }} &times; Rp&nbsp;{{ formatQty($dendaCheckout) }}</span>
                                    <span class="text-xs font-medium text-red-600 text-right shrink-0">&minus;Rp&nbsp;{{ formatQty($potCheckout) }}</span>
                                </div>
                            @endif
                            <div class="flex flex-col gap-0.5 px-3 py-2 bg-primary/5">
                                <span class="text-[11px] text-on-surface-variant break-words">Rp&nbsp;{{ formatQty($pokok) }} + Rp&nbsp;{{ formatQty($bonus) }} + Rp&nbsp;{{ formatQty($insentif) }} &minus; Rp&nbsp;{{ formatQty($potTerlambat + $potCheckout) }}</span>
                                <span class="text-base font-bold text-primary text-right">= Rp&nbsp;{{ formatQty($total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $bulanId = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    $hariId = ['S', 'S', 'R', 'K', 'J', 'S', 'M'];
                    $byDate = $rows->keyBy(fn ($r) => \Carbon\Carbon::parse($r->attendance_tanggal)->toDateString());
                    $cursor = \Carbon\Carbon::parse($dari)->startOfMonth();
                    $lastMonth = \Carbon\Carbon::parse($sampai)->startOfMonth();
                    $rangeStart = \Carbon\Carbon::parse($dari)->toDateString();
                    $rangeEnd = \Carbon\Carbon::parse($sampai)->toDateString();
                    $guard = 0;
                @endphp
                @while ($cursor <= $lastMonth && $guard < 12)
                    @php
                        $guard++;
                        $daysInMonth = $cursor->daysInMonth;
                        $offset = ($cursor->dayOfWeek + 6) % 7;
                        $prefix = $cursor->format('Y-m');
                        $monthKey = $cursor->format('Y-m');
                        $monthRows = $rows->filter(fn ($r) => str_starts_with(\Carbon\Carbon::parse($r->attendance_tanggal)->toDateString(), $monthKey));
                        $hadirBulan = $monthRows->where('attendance_status', 'hadir')->count();
                        $insentifBulan = round($hadirBulan * $potongan, 2);
                        $terlambatBulan = $monthRows->filter(fn ($r) => in_array(\Carbon\Carbon::parse($r->attendance_tanggal)->toDateString(), $lateDates))->count();
                    @endphp
                    <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                        <div class="px-4 py-3 border-b border-outline-variant">
                            <h2 class="font-title-md text-title-md text-on-surface leading-tight">{{ $bulanId[$cursor->month - 1] }} {{ $cursor->year }}</h2>
                            <p class="text-body-xs text-on-surface-variant mt-0.5">{{ $hadirBulan }} hadir &times; Rp&nbsp;{{ formatQty($potongan) }} = <span class="font-bold text-green-600">+Rp&nbsp;{{ formatQty($insentifBulan) }}</span>@if($terlambatBulan > 0) &bull; <span class="font-bold text-red-600">{{ $terlambatBulan }} terlambat</span>@endif</p>
                        </div>
                        <div class="p-3">
                            <div class="flex flex-row">
                                @foreach ($hariId as $h)
                                    <span class="w-[14.28%] text-center text-[10px] uppercase tracking-wide text-on-surface-variant pb-1">{{ $h }}</span>
                                @endforeach
                            </div>
                            <div class="flex flex-row flex-wrap">
                                @for ($i = 0; $i < $offset; $i++)
                                    <div class="w-[14.28%] p-0.5"><div class="rounded-lg py-1.5 text-center text-xs">&nbsp;</div></div>
                                @endfor
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $key = sprintf('%s-%02d', $prefix, $d);
                                        $st = $byDate[$key]?->attendance_status ?? null;
                                        $isLate = in_array($key, $lateDates);
                                        $inRange = $key >= $rangeStart && $key <= $rangeEnd;
                                        $isToday = $key === \Carbon\Carbon::today()->toDateString();
                                        $cell = $st === 'hadir'
                                            ? 'bg-green-600 text-white font-bold'
                                            : ($st === 'izin'
                                                ? 'bg-amber-200 text-amber-900 font-bold'
                                                : ($st === 'sakit'
                                                    ? 'bg-red-200 text-red-800 font-bold'
                                                    : 'bg-surface-container-lowest text-on-surface-variant/60'));
                                    @endphp
                                    <div class="w-[14.28%] p-0.5 {{ $inRange ? '' : 'opacity-40' }}">
                                        <div class="rounded-lg py-1.5 text-center text-xs {{ $cell }} {{ $isToday ? 'ring-2 ring-primary' : '' }} {{ $isLate ? 'underline decoration-2 underline-offset-2' : '' }}" @if($isLate) title="Terlambat" @endif>{{ $d }}</div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @php $cursor->addMonth(); @endphp
                @endwhile
            @elseif (! $user)
                <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant bg-surface-container-low rounded-xl border border-outline-variant">Pilih karyawan lalu tekan Tampilkan.</p>
            @endif
        </div>
    </div>
</x-layouts::app>
