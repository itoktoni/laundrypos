<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Roster Jadwal']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex-1 min-w-0">
                    <h1 class="font-headline-lg-mobile sm:font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight">Roster Jadwal</h1>
                    <p class="text-body-sm text-on-surface-variant">{{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
                </div>
                @if ($user)
                    <div class="shrink-0 w-full sm:w-auto sm:ml-auto">
                        <a href="{{ route('report.jadwal.getPdf', ['dari' => $dari, 'sampai' => $sampai, 'user' => $userId]) }}" class="btn btn-sm btn-primary w-full sm:w-auto text-center">PDF</a>
                    </div>
                @endif
            </div>

            <form method="GET" action="{{ route('report.jadwal.getIndex') }}" class="bg-surface-container-low rounded-xl border border-outline-variant p-3 flex flex-col gap-2">
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
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
            </form>

            @if ($user)
                <div class="rounded-2xl border border-outline-variant overflow-hidden bg-gradient-to-br from-primary/15 via-surface-container-low to-surface-container-low">
                    <div class="p-4 flex items-center gap-2 min-w-0">
                        <span class="w-10 h-10 rounded-full bg-primary/15 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined">person</span>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-body-sm font-bold text-on-surface truncate">{{ $user->name }}</p>
                            <p class="text-body-xs text-on-surface-variant">{{ $schedules->count() }} hari terjadwal</p>
                        </div>
                    </div>
                </div>

                @php
                    $bulanId = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    $hariId = ['S', 'S', 'R', 'K', 'J', 'S', 'M'];
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
                    @endphp
                    <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                        <div class="px-4 py-3 border-b border-outline-variant">
                            <h2 class="font-title-md text-title-md text-on-surface leading-tight">{{ $bulanId[$cursor->month - 1] }} {{ $cursor->year }}</h2>
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
                                        $sched = $schedules[$key] ?? null;
                                        $row = $rows[$key] ?? null;
                                        $st = $row?->attendance_status;
                                        $inRange = $key >= $rangeStart && $key <= $rangeEnd;
                                        $cell = 'bg-surface-container-lowest text-on-surface-variant/60';
                                        $jam = null;
                                        if ($sched) {
                                            $jam = substr((string) $sched->schedule_jam_masuk, 0, 5).'–'.substr((string) $sched->schedule_jam_pulang, 0, 5);
                                            $cell = 'bg-amber-50 text-amber-800 border border-amber-300 font-bold';
                                        }
                                        if ($st === 'hadir') {
                                            $cell = 'bg-green-600 text-white font-bold';
                                        } elseif ($st === 'izin') {
                                            $cell = 'bg-amber-200 text-amber-900 font-bold';
                                        } elseif ($st === 'sakit') {
                                            $cell = 'bg-red-200 text-red-800 font-bold';
                                        }
                                    @endphp
                                    <div class="w-[14.28%] p-0.5 {{ $inRange ? '' : 'opacity-40' }}">
                                        <div class="rounded-lg py-1 text-center {{ $cell }}">
                                            <p class="text-xs leading-tight">{{ $d }}</p>
                                            @if ($jam)
                                                <p class="text-[8px] leading-tight opacity-80">{{ $jam }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @php $cursor->addMonth(); @endphp
                @endwhile
            @else
                <p class="px-4 py-6 text-center text-body-sm text-on-surface-variant bg-surface-container-low rounded-xl border border-outline-variant">Pilih karyawan lalu tekan Tampilkan.</p>
            @endif
        </div>
    </div>
</x-layouts::app>
