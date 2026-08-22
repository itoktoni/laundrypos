{{-- Struk partial — dipakai oleh PDF (dompdf) dan print view. --}}
@php($customerNama = $order->hasCustomer?->customer_nama ?? $order->order_walkin_nama)
@php($customerTelepon = $order->hasCustomer?->customer_telepon ?? $order->order_walkin_telepon)

<div class="struk">
    <h2 style="text-align:center; margin:0;">STRUK LAUNDRY</h2>
    <p style="text-align:center; margin:4px 0;">{{ $order->order_code }}</p>
    <hr>

    <p style="margin:2px 0;">Tanggal: {{ formatDate($order->created_at, true) }}</p>
    <p style="margin:2px 0;">Pelanggan: {{ $customerNama }} ({{ $customerTelepon }})</p>
    <p style="margin:2px 0;">Pengambilan: {{ $order->order_metode_pengambilan?->description }}</p>
    @if ($order->order_metode_pengambilan->value === 'jemput')
        <p style="margin:2px 0;">Alamat: {{ $order->order_alamat_jemput }}</p>
        <p style="margin:2px 0;">Slot: {{ formatDate($order->order_slot_waktu, true) }}</p>
    @endif
    <hr>

    <table width="100%" cellspacing="0" cellpadding="2" style="font-size:12px;">
        <thead>
            <tr>
                <th align="left">Produk</th>
                <th align="right">Harga</th>
                <th align="right">Qty</th>
                <th align="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->hasItems as $item)
                <tr>
                    <td>{{ $item->order_item_nama_product }}</td>
                    <td align="right">{{ number_format((float) $item->order_item_harga) }}</td>
                    <td align="right">{{ formatQty($item->order_item_qty) }} {{ $item->order_item_satuan }}</td>
                    <td align="right">{{ number_format((float) $item->order_item_subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>TOTAL</strong></td>
                <td align="right"><strong>{{ formatAngka($order->order_total) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    <hr>

    <p style="margin:2px 0;">Bayar: {{ $order->order_metode_pembayaran?->description }}</p>
    <p style="margin:2px 0;">Estimasi selesai: {{ formatDate($order->order_estimasi_selesai, true) }}
        ({{ ceil(abs($order->created_at->diffInHours($order->order_estimasi_selesai)) / 24) }} hari)</p>
    @if ($order->order_catatan)
        <p style="margin:2px 0;">Catatan: {{ $order->order_catatan }}</p>
    @endif
    <p style="text-align:center; margin-top:8px;">Terima kasih telah menggunakan layanan kami!</p>
</div>
