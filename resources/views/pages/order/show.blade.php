<x-layouts::app>
    @php
        $statuses = \App\Models\OrderStatus::get()->values();
        $currentStatus = $order->hasStatus;
        $isBatal = $currentStatus?->order_status_is_batal ?? false;
        $currentIndex = $statuses->search(fn ($s) => $s->getKey() === $currentStatus?->getKey());
        $nextStatus = (! $isBatal && $currentIndex !== false && isset($statuses[$currentIndex + 1])) ? $statuses[$currentIndex + 1] : null;
        $prevStatus = (! $isBatal && $currentIndex !== false && $currentIndex > 0 && ! $statuses[$currentIndex - 1]->order_status_is_batal) ? $statuses[$currentIndex - 1] : null;
        $cancelStatus = $statuses->firstWhere('order_status_is_batal', true);
        $warna = $currentStatus?->order_status_warna ?? '#2563eb';
        $customerNama = $order->hasCustomer?->customer_nama ?? ($order->order_walkin_nama.' · Walk-in');
        $customerTelepon = $order->hasCustomer?->customer_telepon ?? $order->order_walkin_telepon;
    @endphp

    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => route('order.getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => $order->order_code]]" />

    <div class="content mt-4 lg:mt-0 space-y-6">
        {{-- Header: kode + status lead the page --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight">{{ $order->order_code }}</h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                          style="background: {{ $warna }}1f; color: {{ $warna }};">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $warna }};"></span>
                        {{ $currentStatus?->order_status_nama ?? '-' }}
                    </span>
                </div>
                <p class="text-sm text-on-surface-variant mt-1">
                    Dibuat {{ formatDate($order->created_at, true) }} oleh {{ $order->hasUser?->name ?? '-' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2 print:hidden">
                <a href="{{ route('order.strukpdf', ['id' => $order->order_id]) }}" class="btn btn-sm btn-outline">Struk PDF</a>
                <a href="{{ route('order.print', ['id' => $order->order_id, 'mode' => 'browser']) }}" target="_blank" class="btn btn-sm btn-outline">Print A5</a>
                <a href="{{ route('order.print', ['id' => $order->order_id, 'mode' => 'thermal']) }}" target="_blank" class="btn btn-sm btn-outline">Thermal 80mm</a>
            </div>
        </div>

        {{-- Progress stepper along the laundry's own flow --}}
        @if (! $isBatal)
            {{-- Mobile: wrapped grid, no horizontal scroll --}}
            <ol class="lg:hidden grid grid-cols-2 gap-2">
                @foreach ($statuses->where('order_status_is_batal', false) as $step)
                    @php($stepIndex = $statuses->search($step))
                    @php($state = $stepIndex < $currentIndex ? 'done' : ($stepIndex === $currentIndex ? 'now' : 'todo'))
                    @php($isNext = $stepIndex === $currentIndex + 1)
                    @php($isPrev = $stepIndex === $currentIndex - 1 && $prevStatus)
                    @if ($isNext || $isPrev)
                        <form method="POST" action="{{ route('order.transit', ['id' => $order->order_id]) }}" class="contents">
                            @csrf
                            <input type="hidden" name="order_status_id" value="{{ $step->getKey() }}">
                            <li class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs cursor-pointer transition hover:ring-2
                                {{ $isPrev ? 'hover:ring-warning/40' : 'hover:ring-primary/40' }}
                                {{ $state === 'now' ? 'bg-primary text-on-primary font-semibold'
                                    : ($state === 'done' ? 'bg-primary/10 text-primary' : 'border border-outline-variant text-on-surface-variant hover:bg-primary/5') }}">
                                <span class="w-5 h-5 rounded-full grid place-items-center text-[10px] font-bold shrink-0
                                    {{ $state === 'now' ? 'bg-on-primary/20'
                                        : ($state === 'done' ? 'bg-primary/15' : 'border border-outline-variant') }}">
                                    @if ($state === 'done') ✓@else {{ $loop->index + 1 }} @endif
                                </span>
                                <span class="leading-tight">{{ $step->order_status_nama }}</span>
                            </li>
                        </form>
                    @else
                        <li class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs
                            {{ $state === 'now' ? 'bg-primary text-on-primary font-semibold'
                                : ($state === 'done' ? 'bg-primary/10 text-primary' : 'border border-outline-variant text-on-surface-variant') }}">
                            <span class="w-5 h-5 rounded-full grid place-items-center text-[10px] font-bold shrink-0
                                {{ $state === 'now' ? 'bg-on-primary/20'
                                    : ($state === 'done' ? 'bg-primary/15' : 'border border-outline-variant') }}">
                                @if ($state === 'done') ✓@else {{ $loop->index + 1 }} @endif
                            </span>
                            <span class="leading-tight">{{ $step->order_status_nama }}</span>
                        </li>
                    @endif
                @endforeach
            </ol>

            {{-- Desktop: connected horizontal flow --}}
            <ol class="hidden lg:flex items-start -mx-1 px-1">
                @foreach ($statuses->where('order_status_is_batal', false) as $step)
                    @php($stepIndex = $statuses->search($step))
                    @php($state = $stepIndex < $currentIndex ? 'done' : ($stepIndex === $currentIndex ? 'now' : 'todo'))
                    @php($isNext = $stepIndex === $currentIndex + 1)
                    @php($isPrev = $stepIndex === $currentIndex - 1 && $prevStatus)
                    <li class="flex items-center shrink-0 {{ ! $loop->last ? 'flex-1 min-w-[96px]' : '' }}">
                        @if ($isNext || $isPrev)
                            <form method="POST" action="{{ route('order.transit', ['id' => $order->order_id]) }}" class="contents">
                                @csrf
                                <input type="hidden" name="order_status_id" value="{{ $step->getKey() }}">
                                <button type="submit" class="flex flex-col items-center text-center gap-1.5 px-1 cursor-pointer group">
                                    <span class="w-7 h-7 rounded-full grid place-items-center text-xs font-bold transition group-hover:ring-2
                                        {{ $isPrev ? 'group-hover:ring-warning/40' : 'group-hover:ring-primary/40' }}
                                        {{ $state === 'now' ? 'bg-primary text-on-primary ring-4 ring-primary/20'
                                            : ($state === 'done' ? 'bg-primary/15 text-primary' : 'border border-outline-variant text-on-surface-variant group-hover:bg-primary/5 group-hover:border-primary') }}">
                                        @if ($state === 'done') ✓@else {{ $loop->index + 1 }} @endif
                                    </span>
                                    <span class="text-[11px] leading-tight max-w-[96px] {{ $state === 'now' ? 'font-semibold' : 'text-on-surface-variant group-hover:text-primary' }}">
                                        {{ $step->order_status_nama }}
                                    </span>
                                </button>
                            </form>
                        @else
                            <div class="flex flex-col items-center text-center gap-1.5 px-1">
                                <span class="w-7 h-7 rounded-full grid place-items-center text-xs font-bold
                                    {{ $state === 'now' ? 'bg-primary text-on-primary ring-4 ring-primary/20'
                                        : ($state === 'done' ? 'bg-primary/15 text-primary' : 'border border-outline-variant text-on-surface-variant') }}">
                                    @if ($state === 'done') ✓@else {{ $loop->index + 1 }} @endif
                                </span>
                                <span class="text-[11px] leading-tight max-w-[96px] {{ $state === 'now' ? 'font-semibold' : 'text-on-surface-variant' }}">
                                    {{ $step->order_status_nama }}
                                </span>
                            </div>
                        @endif
                        @if (! $loop->last)
                            <div class="h-px flex-1 mx-1 mb-5 {{ $stepIndex < $currentIndex ? 'bg-primary/40' : 'bg-outline-variant' }}"></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main: items + grouped details --}}
            <div class="lg:col-span-2 space-y-6">
                <section aria-label="Rincian item">
                    {{-- Mobile: stacked list --}}
                    <ul class="sm:hidden divide-y divide-outline-variant/40">
                        @foreach ($order->hasItems as $item)
                            <li class="py-3">
                                <div class="flex items-baseline justify-between gap-2">
                                    <p class="text-sm font-medium min-w-0">{{ $item->order_item_nama_product }}</p>
                                    <p class="text-sm font-semibold tabular-nums whitespace-nowrap">{{ formatAngka($item->order_item_subtotal) }}</p>
                                </div>
                                <p class="text-xs text-on-surface-variant mt-0.5">
                                    {{ formatQty($item->order_item_qty) }} {{ $item->order_item_satuan }} × {{ formatAngka($item->order_item_harga) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Desktop: table --}}
                    <table class="hidden sm:table w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant text-left text-xs uppercase tracking-wide text-on-surface-variant">
                                <th class="py-2 pr-2 font-medium">Produk</th>
                                <th class="py-2 px-2 font-medium text-right">Harga</th>
                                <th class="py-2 px-2 font-medium text-right">Qty</th>
                                <th class="py-2 pl-2 font-medium text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->hasItems as $item)
                                <tr class="border-b border-outline-variant/40">
                                    <td class="py-2.5 pr-2">{{ $item->order_item_nama_product }}</td>
                                    <td class="py-2.5 px-2 text-right tabular-nums">{{ formatAngka($item->order_item_harga) }}</td>
                                    <td class="py-2.5 px-2 text-right tabular-nums whitespace-nowrap">{{ formatQty($item->order_item_qty) }} <span class="text-on-surface-variant">{{ $item->order_item_satuan }}</span></td>
                                    <td class="py-2.5 pl-2 text-right tabular-nums">{{ formatAngka($item->order_item_subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4 rounded-xl bg-surface-container px-4 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-on-surface-variant">Total{{ $order->order_catatan ? ' · catatan tersedia di bawah' : '' }}</p>
                            <p class="text-lg font-bold tabular-nums">{{ formatAngka($order->order_total) }}</p>
                        </div>
                        <span class="badge badge-outline text-xs">{{ $order->order_metode_pembayaran?->description }}</span>
                    </div>
                </section>

                @if ($order->order_catatan)
                    <section aria-label="Catatan" class="rounded-xl border border-outline-variant px-4 py-3">
                        <p class="text-xs text-on-surface-variant mb-1">Catatan</p>
                        <p class="text-sm">{{ $order->order_catatan }}</p>
                    </section>
                @endif

                <section aria-label="Detail order" class="grid sm:grid-cols-2 gap-x-8 gap-y-5 pt-1">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-on-surface-variant mb-2">Pelanggan</p>
                        <p class="text-sm font-medium">{{ $customerNama }}</p>
                        <p class="text-sm text-on-surface-variant">{{ $customerTelepon }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-on-surface-variant mb-2">Estimasi Selesai</p>
                        <p class="text-sm font-medium">{{ formatDate($order->order_estimasi_selesai, true) }}</p>
                        <p class="text-sm text-on-surface-variant">± {{ ceil(abs($order->created_at->diffInHours($order->order_estimasi_selesai)) / 24) }} hari kerja</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-on-surface-variant mb-2">Pengambilan</p>
                        <p class="text-sm font-medium">{{ $order->order_metode_pengambilan?->description }}</p>
                        @if ($order->order_metode_pengambilan?->value === 'jemput')
                            <p class="text-sm text-on-surface-variant">{{ $order->order_alamat_jemput }}</p>
                            <p class="text-sm text-on-surface-variant">Slot: {{ formatDate($order->order_slot_waktu, true) }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-on-surface-variant mb-2">Kasir</p>
                        <p class="text-sm font-medium">{{ $order->hasUser?->name ?? '-' }}</p>
                    </div>
                </section>
            </div>

            {{-- Side: status action + chronology --}}
            <div class="space-y-6 print:hidden">
                @if ($nextStatus || $prevStatus || $cancelStatus)
                    <section aria-label="Perbarui status" class="rounded-xl border border-outline-variant p-4">
                        <h2 class="font-semibold text-sm mb-3">Perbarui Status</h2>
                        <div class="space-y-2">
                            @if ($nextStatus)
                                <form method="POST" action="{{ route('order.transit', ['id' => $order->order_id]) }}">
                                    @csrf
                                    <input type="hidden" name="order_status_id" value="{{ $nextStatus->getKey() }}">
                                    <button type="submit" class="btn btn-primary w-full">
                                        Tandai "{{ $nextStatus->order_status_nama }}"
                                    </button>
                                </form>
                            @endif

                            @if ($prevStatus)
                                <form method="POST" action="{{ route('order.transit', ['id' => $order->order_id]) }}">
                                    @csrf
                                    <input type="hidden" name="order_status_id" value="{{ $prevStatus->getKey() }}">
                                    <button type="submit" class="btn btn-outline btn-warning w-full">
                                        Kembali ke "{{ $prevStatus->order_status_nama }}"
                                    </button>
                                </form>
                            @endif

                            @if ($cancelStatus && ! $nextStatus && ! $prevStatus)
                                <p class="text-xs text-on-surface-variant">Order sudah di tahap akhir alur.</p>
                            @endif
                        </div>

                        @if ($cancelStatus && ($nextStatus || $prevStatus))
                            <details class="mt-2">
                                <summary class="text-xs text-error cursor-pointer select-none">Batalkan order…</summary>
                                <form method="POST" action="{{ route('order.transit', ['id' => $order->order_id]) }}" class="mt-2 space-y-2">
                                    @csrf
                                    <input type="hidden" name="order_status_id" value="{{ $cancelStatus->getKey() }}">
                                    <textarea name="keterangan" required maxlength="255" rows="2" placeholder="Alasan pembatalan (wajib)" class="textarea w-full text-sm"></textarea>
                                    <button type="submit" class="btn btn-outline btn-error btn-sm w-full">Batalkan Order</button>
                                </form>
                            </details>
                        @endif
                    </section>
                @endif

                <section aria-label="Kronologi status">
                    <h2 class="font-semibold text-sm mb-3">Kronologi</h2>
                    <ol class="relative space-y-4 before:absolute before:left-[5px] before:top-2 before:bottom-2 before:w-px before:bg-outline-variant">
                        @forelse ($order->hasStatusLogs->sortBy('created_at')->reverse() as $log)
                            <li class="relative pl-6">
                                <span class="absolute left-0 top-1 w-[11px] h-[11px] rounded-full border-2 border-primary bg-surface"></span>
                                <p class="text-sm font-medium leading-snug">
                                    {{ $log->hasToStatus?->order_status_nama ?? '-' }}
                                    @if ($log->hasFromStatus)
                                        <span class="text-on-surface-variant font-normal">· dari {{ $log->hasFromStatus->order_status_nama }}</span>
                                    @endif
                                </p>
                                <p class="text-xs text-on-surface-variant">{{ formatDate($log->created_at, true) }} · {{ $log->hasUser?->name ?? '-' }}</p>
                                @if ($log->order_status_log_keterangan)
                                    <p class="text-xs italic mt-0.5">"{{ $log->order_status_log_keterangan }}"</p>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-on-surface-variant pl-6">Belum ada perubahan status.</li>
                        @endforelse
                    </ol>
                </section>
            </div>
        </div>
    </div>
</x-layouts::app>
