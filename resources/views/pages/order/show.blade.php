<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => route('order.getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => $order->order_code]]" />
    <div class="content mt-4 lg:mt-0">
        @php($statusLogs = $order->hasStatusLogs)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <x-card label="Order {{ $order->order_code }}">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-on-surface-variant">Pelanggan</p>
                            <p class="font-medium">{{ $order->hasCustomer?->customer_nama ?? ($order->order_walkin_nama.' (Walk-in)') }}</p>
                        </div>
                        <div>
                            <p class="text-on-surface-variant">Telepon</p>
                            <p>{{ $order->hasCustomer?->customer_telepon ?? $order->order_walkin_telepon }}</p>
                        </div>
                        <div>
                            <p class="text-on-surface-variant">Metode Pengambilan</p>
                            <p>{{ $order->order_metode_pengambilan->getDescription() }}</p>
                        </div>
                        <div>
                            <p class="text-on-surface-variant">Pembayaran</p>
                            <p>{{ $order->order_metode_pembayaran->getDescription() }}</p>
                        </div>
                        <div>
                            <p class="text-on-surface-variant">Estimasi Selesai</p>
                            <p>{{ formatDate($order->order_estimasi_selesai, true) }} ({{ ceil(abs($order->created_at->diffInHours($order->order_estimasi_selesai)) / 24) }} hari)</p>
                        </div>
                        @if ($order->order_catatan)
                            <div class="col-span-2">
                                <p class="text-on-surface-variant">Catatan</p>
                                <p>{{ $order->order_catatan }}</p>
                            </div>
                        @endif
                    </div>
                </x-card>

                <x-card label="Item">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-outline-variant text-left">
                                <th class="py-2">Produk</th>
                                <th class="py-2">Harga</th>
                                <th class="py-2">Qty</th>
                                <th class="py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->hasItems as $item)
                                <tr class="border-b border-outline-variant/50">
                                    <td class="py-2">{{ $item->order_item_nama_product }}</td>
                                    <td class="py-2">{{ formatAngka($item->order_item_harga) }}</td>
                                    <td class="py-2">{{ formatQty($item->order_item_qty) }} {{ $item->order_item_satuan }}</td>
                                    <td class="py-2 text-right">{{ formatAngka($item->order_item_subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="pt-2 font-bold">Total</td>
                                <td class="pt-2 text-right font-bold">{{ formatAngka($order->order_total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </x-card>
            </div>

            <div class="space-y-4">
                <x-card label="Status: {{ $order->hasStatus?->order_status_nama }}">
                    <ol class="space-y-3">
                        @forelse ($statusLogs->reverse() as $log)
                            <li class="border-l-2 border-primary pl-3">
                                <p class="text-sm font-medium">
                                    {{ $log->hasFromStatus?->order_status_nama ? $log->hasFromStatus->order_status_nama.' → ' : '' }}{{ $log->hasToStatus?->order_status_nama }}
                                </p>
                                <p class="text-xs text-on-surface-variant">{{ formatDate($log->created_at, true) }} oleh {{ $log->hasUser?->name ?? '-' }}</p>
                                @if ($log->order_status_log_keterangan)
                                    <p class="text-xs italic mt-0.5">"{{ $log->order_status_log_keterangan }}"</p>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-on-surface-variant">Belum ada perubahan status.</li>
                        @endforelse
                    </ol>

                    <div class="mt-4 flex flex-wrap gap-2 print:hidden">
                        <a href="{{ route('order.strukpdf', ['id' => $order->order_id]) }}" class="btn btn-sm btn-soft">Struk PDF</a>
                        <a href="{{ route('order.print', ['id' => $order->order_id, 'mode' => 'browser']) }}" target="_blank" class="btn btn-sm btn-soft">Print Browser</a>
                        <a href="{{ route('order.print', ['id' => $order->order_id, 'mode' => 'thermal']) }}" target="_blank" class="btn btn-sm btn-soft">Thermal 80mm</a>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts::app>
