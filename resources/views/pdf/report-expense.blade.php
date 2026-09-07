<h2 style="text-align:center; margin:0;">LAPORAN PENGELUARAN</h2>
<p style="text-align:center; margin:4px 0;">Periode {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
<hr>

<table width="100%" cellspacing="0" cellpadding="4" style="font-size:12px;" border="1">
    <thead>
        <tr>
            <th align="left">Tanggal</th>
            <th align="left">Nama</th>
            <th align="left">Kategori</th>
            <th align="right">Nominal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($expenses as $expense)
            <tr>
                <td>{{ formatDate($expense->expense_tanggal) }}</td>
                <td>{{ $expense->expense_nama }}</td>
                <td>{{ $expense->expense_kategori }}</td>
                <td align="right">{{ number_format((float) $expense->expense_nominal) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTAL</strong></td>
            <td align="right"><strong>{{ number_format((float) $expenses->sum('expense_nominal')) }}</strong></td>
        </tr>
    </tfoot>
</table>
