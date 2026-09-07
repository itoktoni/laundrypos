<h2 style="text-align:center; margin:0;">LAPORAN ABSENSI</h2>
<p style="text-align:center; margin:4px 0;">Periode {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
<hr>

<table width="100%" cellspacing="0" cellpadding="4" style="font-size:12px;" border="1">
    <thead>
        <tr>
            <th align="left">Tanggal</th>
            <th align="left">Karyawan</th>
            <th align="left">Check-in</th>
            <th align="left">Check-out</th>
            <th align="left">Status</th>
            <th align="left">Ket.</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ formatDate($row->attendance_tanggal) }}</td>
                <td>{{ $row->hasUser?->name ?? '-' }}</td>
                <td>{{ $row->attendance_checkin_at ? $row->attendance_checkin_at->format('H:i') : '-' }}</td>
                <td>{{ $row->attendance_checkout_at ? $row->attendance_checkout_at->format('H:i') : '-' }}</td>
                <td>{{ ucfirst($row->attendance_status) }}</td>
                <td>{{ ($row->evaluasi['terlambat'] ?? false) ? 'Telat '.$row->evaluasi['menitTerlambat'].' mnt' : '' }}{{ (($row->evaluasi['terlambat'] ?? false) && ($row->evaluasi['noCheckout'] ?? false)) ? ' + ' : '' }}{{ ($row->evaluasi['noCheckout'] ?? false) ? 'Tanpa checkout' : '' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6"><strong>Hadir: {{ $rows->where('attendance_status', 'hadir')->count() }} &bull; Izin: {{ $rows->where('attendance_status', 'izin')->count() }} &bull; Sakit: {{ $rows->where('attendance_status', 'sakit')->count() }}</strong></td>
        </tr>
    </tfoot>
</table>

<br>
<h3 style="margin:0 0 4px;">PENGGAJIAN (Rp {{ number_format((float) $upah) }} / hari)</h3>

<table width="100%" cellspacing="0" cellpadding="4" style="font-size:12px;" border="1">
    <thead>
        <tr>
            <th align="left">Karyawan</th>
            <th align="right">Hadir</th>
            <th align="right">Izin</th>
            <th align="right">Sakit</th>
            <th align="right">Total Gaji</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($payrolls as $pay)
            <tr>
                <td>{{ $pay['nama'] }}</td>
                <td align="right">{{ $pay['hadir'] }}</td>
                <td align="right">{{ $pay['izin'] }}</td>
                <td align="right">{{ $pay['sakit'] }}</td>
                <td align="right">{{ number_format((float) $pay['total']) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
