<h2 style="text-align:center; margin:0;">LAPORAN ORDER</h2>
<p style="text-align:center; margin:4px 0;">Periode {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
<hr>

<table width="100%" cellspacing="0" cellpadding="4" style="font-size:12px;" border="1">
    <thead>
        <tr>
            <th align="left">Kode</th>
            <th align="left">Tanggal</th>
            <th align="left">Pelanggan</th>
            <th align="left">Status</th>
            <th align="right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orders as $order)
            <tr>
                <td>{{ $order->order_code }}</td>
                <td>{{ formatDate($order->created_at, true) }}</td>
                <td>{{ $order->hasCustomer?->customer_nama ?? $order->order_walkin_nama ?? '-' }}</td>
                <td>{{ $order->hasStatus?->order_status_nama ?? '-' }}</td>
                <td align="right">{{ number_format((float) $order->order_total) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><strong>TOTAL ({{ $orders->count() }} order)</strong></td>
            <td align="right"><strong>{{ number_format((float) $orders->sum('order_total')) }}</strong></td>
        </tr>
    </tfoot>
</table>
