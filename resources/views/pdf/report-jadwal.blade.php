<h2 style="text-align:center; margin:0;">ROSTER JADWAL</h2>
<p style="text-align:center; margin:4px 0;">{{ $user?->name ?? '-' }} &bull; {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
<hr>

<table width="100%" cellspacing="0" cellpadding="4" style="font-size:12px;" border="1">
    <thead>
        <tr>
            <th align="left">Tanggal</th>
            <th align="left">Jadwal</th>
            <th align="left">Check-in</th>
            <th align="left">Check-out</th>
            <th align="left">Status</th>
            <th align="left">Ket.</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lines as $line)
            <tr>
                <td>{{ formatDate($line['tanggal']) }}</td>
                <td>{{ $line['jadwal'] }}</td>
                <td>{{ $line['checkin'] }}</td>
                <td>{{ $line['checkout'] }}</td>
                <td>{{ $line['status'] }}</td>
                <td>{{ $line['ket'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
