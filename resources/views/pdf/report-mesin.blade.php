<h2 style="text-align:center; margin:0;">LAPORAN MESIN</h2>
<p style="text-align:center; margin:4px 0;">Service {{ formatDate($dari) }} &ndash; {{ formatDate($sampai) }}</p>
<hr>

<table width="100%" cellspacing="0" cellpadding="4" style="font-size:12px;" border="1">
    <thead>
        <tr>
            <th align="left">Tanggal</th>
            <th align="left">Mesin</th>
            <th align="left">Jenis</th>
            <th align="left">Status</th>
            <th align="right">Biaya</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($services as $service)
            <tr>
                <td>{{ formatDate($service->service_tanggal) }}</td>
                <td>{{ $service->hasMesin?->mesin_nama ?? '-' }}</td>
                <td>{{ $service->service_jenis?->description ?? $service->service_jenis }}</td>
                <td>{{ $service->service_is_selesai ? 'Selesai' : 'Terbuka' }}</td>
                <td align="right">{{ number_format((float) $service->service_biaya) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><strong>TOTAL BIAYA ({{ $services->count() }} service)</strong></td>
            <td align="right"><strong>{{ number_format((float) $services->sum('service_biaya')) }}</strong></td>
        </tr>
    </tfoot>
</table>
