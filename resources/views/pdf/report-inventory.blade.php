<h2 style="text-align:center; margin:0;">LAPORAN INVENTORY</h2>
<p style="text-align:center; margin:4px 0;">Posisi stok per {{ formatDate($sampai) }} (mutasi {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }})</p>
<hr>

<table width="100%" cellspacing="0" cellpadding="4" style="font-size:12px;" border="1">
    <thead>
        <tr>
            <th align="left">Barang</th>
            <th align="right">Stok</th>
            <th align="right">Rata-rata</th>
            <th align="right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['nama'] }}</td>
                <td align="right">{{ $row['stok'] }} {{ $row['satuan'] }}</td>
                <td align="right">{{ number_format((float) $row['rata_rata']) }}</td>
                <td align="right">{{ number_format((float) $row['total']) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTAL NILAI STOK</strong></td>
            <td align="right"><strong>{{ number_format((float) $rows->sum('total')) }}</strong></td>
        </tr>
    </tfoot>
</table>
