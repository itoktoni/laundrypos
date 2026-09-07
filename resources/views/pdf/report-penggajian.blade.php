<h2 style="text-align:center; margin:0;">LAPORAN PENGGAJIAN</h2>
<p style="text-align:center; margin:4px 0;">{{ $user?->name ?? '-' }} &bull; {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
<hr>

<p>Hadir: <strong>{{ $hadir }}</strong> &bull; Izin: {{ $izin }} &bull; Sakit: {{ $sakit }} &bull; Terlambat: {{ $terlambat }} &bull; Tanpa checkout: {{ $noCheckout }}</p>
<p>Gaji pokok: Rp {{ number_format((float) $pokok) }}</p>
<p>Bonus: Rp {{ number_format((float) $bonus) }}</p>
<p>Kehadiran: {{ $hadir }} hari &times; Rp {{ number_format((float) $potongan) }} = Rp {{ number_format((float) $insentif) }}</p>
<p>Denda terlambat: {{ $terlambat }} &times; Rp {{ number_format((float) $dendaTerlambat) }} = Rp {{ number_format((float) $potTerlambat) }}</p>
<p>Denda tanpa checkout: {{ $noCheckout }} &times; Rp {{ number_format((float) $dendaCheckout) }} = Rp {{ number_format((float) $potCheckout) }}</p>
<p>Total gaji: <strong>Rp {{ number_format((float) $total) }}</strong></p>

<table width="100%" cellspacing="0" cellpadding="4" style="font-size:12px;" border="1">
    <thead>
        <tr>
            <th align="left">Tanggal</th>
            <th align="left">Check-in</th>
            <th align="left">Check-out</th>
            <th align="left">Status</th>
            <th align="left">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            @php $eval = \App\Models\StaffSchedule::evaluate($row); @endphp
            <tr>
                <td>{{ formatDate($row->attendance_tanggal) }}</td>
                <td>{{ $row->attendance_checkin_at ? $row->attendance_checkin_at->format('H:i') : '-' }}</td>
                <td>{{ $row->attendance_checkout_at ? $row->attendance_checkout_at->format('H:i') : '-' }}</td>
                <td>{{ ucfirst($row->attendance_status) }}</td>
                <td>{{ $eval['terlambat'] ? 'Terlambat' : '' }}{{ $eval['terlambat'] && $eval['noCheckout'] ? ' + ' : '' }}{{ $eval['noCheckout'] ? 'Tanpa checkout' : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
