<x-layouts::app title="Absensi Staff">
    <div class="mb-4 flex items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-on-surface">Absensi Staff</h2>
            <p class="text-sm text-on-surface-variant">Riwayat check-in/out — valid jika ≤ {{ $model->getTable() ? '' : '' }} radius toko.</p>
        </div>
        <a href="{{ route('staff-attendance.getCheckin') }}" wire:navigate class="btn btn-primary gap-1.5"><span class="material-symbols-outlined text-[18px]">how_to_reg</span> Check-in/out</a>
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-on-surface-variant uppercase border-b border-outline-variant bg-surface-container">
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Staff</th>
                        <th class="px-4 py-3">Check-in</th>
                        <th class="px-4 py-3">Jarak</th>
                        <th class="px-4 py-3">Check-out</th>
                        <th class="px-4 py-3">Jarak</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr class="border-b border-outline-variant/40 hover:bg-surface-container-low">
                            <td class="px-4 py-3 font-mono text-xs">{{ $row->attendance_tanggal->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $row->hasUser?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $row->attendance_checkin_at ? $row->attendance_checkin_at->format('H:i') : '—' }}</td>
                            <td class="px-4 py-3">
                                @if($row->attendance_checkin_jarak !== null)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs border {{ $row->attendance_checkin_valid ? 'bg-success/10 border-success/20 text-success' : 'bg-error/10 border-error/20 text-error' }}">{{ $row->attendance_checkin_jarak }} m</span>
                                @else
                                    <span class="text-on-surface-variant">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $row->attendance_checkout_at ? $row->attendance_checkout_at->format('H:i') : '—' }}</td>
                            <td class="px-4 py-3">
                                @if($row->attendance_checkout_jarak !== null)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs border {{ $row->attendance_checkout_valid ? 'bg-success/10 border-success/20 text-success' : 'bg-error/10 border-error/20 text-error' }}">{{ $row->attendance_checkout_jarak }} m</span>
                                @else
                                    <span class="text-on-surface-variant">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3"><span class="badge badge-sm {{ $row->attendance_status === 'hadir' ? 'badge-success' : 'badge-warning' }}">{{ $row->attendance_status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-10 text-on-surface-variant">Belum ada absensi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-outline-variant">{{ $data->links() }}</div>
    </div>
</x-layouts::app>
