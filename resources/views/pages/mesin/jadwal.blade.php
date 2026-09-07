<?php /** @var App\Models\Mesin $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Jadwal Service']]" />
    <div class="content mt-4 lg:mt-0">
        <x-table>
            <x-slot:head>
                <th>Mesin</th>
                <th>Service Terakhir</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
                <th>Aksi</th>
            </x-slot:head>

            <x-slot:body>
                @foreach ($rows as $row)
                    <tr class="{{ $row['status'] === 'terlambat' ? 'bg-red-50' : ($row['status'] === 'jatuh_tempo' ? 'bg-yellow-50' : '') }}">
                        <td>
                            <span class="font-medium">{{ $row['mesin']->mesin_nama }}</span>
                            <span class="font-mono text-on-surface-variant text-xs ml-1">{{ $row['mesin']->mesin_kode }}</span>
                        </td>
                        <td>{{ $row['terakhir'] ? formatDate($row['terakhir']) : '-' }}</td>
                        <td class="font-medium">{{ $row['tempo'] ? formatDate($row['tempo']) : '-' }}</td>
                        <td>
                            @if ($row['status'] === 'terlambat')
                                <span class="badge badge-error">Terlambat {{ abs($row['sisa']) }} hari</span>
                            @elseif ($row['status'] === 'jatuh_tempo')
                                <span class="badge badge-warning">H-{{ $row['sisa'] }}</span>
                            @elseif ($row['status'] === 'tepat_waktu')
                                <span class="badge badge-success">Tepat Waktu</span>
                            @else
                                <span class="badge">Tanpa Jadwal</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('mesin-service.getCreate') }}?service_id_mesin={{ $row['mesin']->field_primary }}" class="btn btn-sm btn-primary">Servis Sekarang</a>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <div class="p-3 space-y-3">
                    @foreach($rows as $row)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $row['mesin']->mesin_nama }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="text-left">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Jatuh Tempo</p>
                                <p class="text-xs font-medium text-on-surface">{{ $row['tempo'] ? formatDate($row['tempo']) : '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Status</p>
                                <p class="text-xs font-medium">
                                    @if ($row['status'] === 'terlambat')
                                        <span class="badge badge-error">Terlambat</span>
                                    @elseif ($row['status'] === 'jatuh_tempo')
                                        <span class="badge badge-warning">H-{{ $row['sisa'] }}</span>
                                    @elseif ($row['status'] === 'tepat_waktu')
                                        <span class="badge badge-success">OK</span>
                                    @else
                                        <span class="badge">-</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('mesin-service.getCreate') }}?service_id_mesin={{ $row['mesin']->field_primary }}" class="btn btn-sm btn-primary btn-block">Servis Sekarang</a>
                    </div>
                    @endforeach
                </div>
            </x-slot:mobile>

        </x-table>
    </div>
</x-layouts::app>
