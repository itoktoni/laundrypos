<x-layouts::app title="Dashboard">
    <div>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-on-surface">Dashboard</h2>
        </div>

        {{-- Filter tanggal --}}
        <form method="GET" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 form-card mb-5">
            <div class="flex items-end gap-3 flex-wrap">
                <div>
                    <label class="text-xs font-semibold text-on-surface-variant uppercase block mb-1">Dari</label>
                    <input type="date" name="start_date" value="{{ $start }}" class="input input-bordered input-sm" />
                </div>
                <div>
                    <label class="text-xs font-semibold text-on-surface-variant uppercase block mb-1">Sampai</label>
                    <input type="date" name="end_date" value="{{ $end }}" class="input input-bordered input-sm" />
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <span class="material-symbols-outlined text-sm">filter_alt</span> Filter
                </button>
            </div>
        </form>

        @if($isStaff)
            {{-- Staff personal header --}}
            <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 mb-5 flex items-center gap-3">
                <span class="w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
                <div>
                    <p class="text-sm font-semibold text-on-surface">Halo, {{ auth()->user()->name }} — performa kamu periode ini</p>
                    <p class="text-xs text-on-surface-variant">Filter {{ $start }} s/d {{ $end }} — data hanya order yang kamu buat</p>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
                <div class="bg-surface-container-lowest border border-primary/30 rounded-xl p-5 form-card">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase">Order Hari Ini</p>
                    <p class="text-2xl font-bold text-primary mt-2">{{ $staffStats['hariIni'] }}</p>
                    <p class="text-xs text-on-surface-variant mt-1">dibuat oleh kamu</p>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 form-card">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase">Order Bulan Ini</p>
                    <p class="text-2xl font-bold text-on-surface mt-2">{{ $staffStats['bulanIni'] }}</p>
                    <p class="text-xs text-on-surface-variant mt-1">target: {{ $staffStats['target'] }} · total: {{ $staffStats['total'] }}</p>
                </div>
                <div class="bg-surface-container-lowest border border-success/30 rounded-xl p-5 form-card">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase">Selesai Dikerjakan</p>
                    <p class="text-2xl font-bold text-success mt-2">{{ $staffStats['selesai'] }}</p>
                    <p class="text-xs text-on-surface-variant mt-1">{{ $staffStats['pending'] }} pending</p>
                </div>
                <div class="bg-surface-container-lowest border border-warning/30 rounded-xl p-5 form-card">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">trophy</span> Bonus Fee</p>
                    <p class="text-xl font-bold text-warning mt-2">{{ formatAngka($staffStats['bonus'], 'Rp ') }}</p>
                    <p class="text-xs text-on-surface-variant mt-1">{{ $staffStats['lebih'] }} lebih × {{ formatAngka($staffStats['fee'], 'Rp ') }}</p>
                </div>
            </div>
            @if($staffStats['lebih'] > 0)
                <div class="bg-warning/10 border border-warning/20 rounded-xl px-4 py-3 mb-5 text-sm text-warning flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">celebration</span>
                    Selamat! Kamu melebihi target {{ $staffStats['target'] }} order — bonus <b>{{ formatAngka($staffStats['bonus'], 'Rp ') }}</b> ({{ $staffStats['lebih'] }} × {{ formatAngka($staffStats['fee'], 'Rp ') }})
                </div>
            @endif
            {{-- Recent my orders --}}
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card mb-5">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">receipt_long</span> Order Terbaru Saya
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-xs text-on-surface-variant uppercase border-b border-outline-variant"><th class="pb-3 pr-4">Kode</th><th class="pb-3 pr-4">Pelanggan</th><th class="pb-3 pr-4">Status</th><th class="pb-3 text-right">Total</th></tr></thead>
                        <tbody>
                        @forelse($staffRecentOrders as $o)
                            <tr class="border-b border-outline-variant/50">
                                <td class="py-3 pr-4 font-mono text-xs">{{ $o->order_code }}</td>
                                <td class="py-3 pr-4">{{ $o->hasCustomer?->customer_nama ?? $o->order_walkin_nama ?? '-' }}</td>
                                <td class="py-3 pr-4"><span class="badge badge-sm" style="background: {{ $o->hasStatus?->order_status_warna ?? '#999' }}; color:#fff">{{ $o->hasStatus?->order_status_nama ?? '-' }}</span></td>
                                <td class="py-3 text-right font-mono">{{ formatAngka($o->order_total, 'Rp ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-6 text-on-surface-variant text-sm">Belum ada order</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Ringkasan Cards (global, hidden for staff unless owner wants) --}}
        @if(!$isStaff)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div class="bg-surface-container-lowest border border-success/30 rounded-xl p-5 form-card">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-success/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-success text-2xl">trending_up</span>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase">Pemasukan</span>
                </div>
                <span class="text-2xl font-bold text-success">{{ formatAngka($pemasukan, 'Rp ') }}</span>
            </div>

            <div class="bg-surface-container-lowest border border-error/30 rounded-xl p-5 form-card">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-error/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-error text-2xl">trending_down</span>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase">Pengeluaran</span>
                </div>
                <span class="text-2xl font-bold text-error">{{ formatAngka($pengeluaran, 'Rp ') }}</span>
            </div>

            <div class="bg-surface-container-lowest border {{ $labaRugi >= 0 ? 'border-primary/30' : 'border-warning/30' }} rounded-xl p-5 form-card">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl {{ $labaRugi >= 0 ? 'bg-primary/10' : 'bg-warning/10' }} flex items-center justify-center">
                        <span class="material-symbols-outlined {{ $labaRugi >= 0 ? 'text-primary' : 'text-warning' }} text-2xl">account_balance</span>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase">{{ $labaRugi >= 0 ? 'Laba' : 'Rugi' }}</span>
                </div>
                <span class="text-2xl font-bold {{ $labaRugi >= 0 ? 'text-primary' : 'text-warning' }}">{{ formatAngka(abs($labaRugi), 'Rp ') }}</span>
            </div>
        </div>
        @endif

        @if($isStaff)
            {{-- Staff charts (tanpa Arus Kas/Pengeluaran) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                    <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">trending_up</span> Produktivitas Harian — Order Saya (7 hari)
                    </h3>
                    <div class="w-full h-64 relative"><canvas id="staffDailyChart"></canvas></div>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                    <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">donut_small</span> Status Order Saya (periode ini)
                    </h3>
                    <div class="w-full h-64 relative"><canvas id="staffStatusChart"></canvas></div>
                </div>
            </div>
        @else
            {{-- Charts Row (admin/owner) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                    <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">account_balance_wallet</span> Arus Kas Harian
                    </h3>
                    <div class="w-full h-64 relative"><canvas id="cashFlowChart"></canvas></div>
                </div>

                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                    <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">pie_chart</span> Pengeluaran per Kategori
                    </h3>
                    <div class="w-full h-64 relative"><canvas id="expenseCategoryChart"></canvas></div>
                </div>
            </div>

            {{-- Pemasukan by Metode + Recent Expenses --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                    <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">payment</span> Pemasukan per Metode Bayar
                    </h3>
                    <div class="w-full h-64 relative"><canvas id="incomeMethodChart"></canvas></div>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card">
                    <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                        Pengeluaran Terakhir
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs text-on-surface-variant uppercase border-b border-outline-variant">
                                    <th class="pb-3 pr-4">Tanggal</th>
                                    <th class="pb-3 pr-4">Nama</th>
                                    <th class="pb-3 pr-4">Kategori</th>
                                    <th class="pb-3 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentExpenses as $exp)
                                    <tr class="border-b border-outline-variant/50">
                                        <td class="py-3 pr-4 text-on-surface-variant">{{ formatDate($exp->expense_tanggal) }}</td>
                                        <td class="py-3 pr-4 font-medium">{{ $exp->expense_nama }}</td>
                                        <td class="py-3 pr-4"><span class="badge badge-soft badge-info badge-sm">{{ $exp->expense_kategori }}</span></td>
                                        <td class="py-3 text-right font-mono text-sm">{{ formatAngka($exp->expense_nominal, 'Rp ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8 text-on-surface-variant">
                                            <span class="material-symbols-outlined text-4xl mb-2 block">receipt_long</span>
                                            <p class="text-sm">Tidak ada pengeluaran</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(!$isStaff && $topStaff->isNotEmpty())
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card mt-5">
            <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">leaderboard</span> Top Staff Periode Ini
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-on-surface-variant uppercase border-b border-outline-variant"><th class="pb-3 pr-4">Staff</th><th class="pb-3 pr-4 text-center">Order</th><th class="pb-3 pr-4 text-center">Selesai</th><th class="pb-3 text-right">Omzet</th><th class="pb-3 text-right">Bonus</th></tr></thead>
                    <tbody>
                    @foreach($topStaff as $row)
                        <tr class="border-b border-outline-variant/50">
                            <td class="py-3 pr-4 font-medium">{{ $row->user?->name ?? 'ID '.$row->order_id_user }}</td>
                            <td class="py-3 pr-4 text-center">{{ $row->total }}</td>
                            <td class="py-3 pr-4 text-center text-success font-semibold">{{ $row->selesai }}</td>
                            <td class="py-3 text-right font-mono">{{ formatAngka($row->omzet, 'Rp ') }}</td>
                            <td class="py-3 text-right font-mono text-warning font-semibold">{{ $row->bonus > 0 ? formatAngka($row->bonus, 'Rp ') : '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <p class="text-xs text-on-surface-variant mt-3">Bonus = max(0, order − {{ config('website.staff_target', 100) }}) × {{ formatAngka(config('website.staff_fee', 1000), 'Rp ') }} — hanya order milik staff ybs.</p>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
    function renderDashboardCharts() {
        if (typeof window.Chart === 'undefined') {
            console.warn('Chart.js not loaded yet, retry in 200ms');
            setTimeout(renderDashboardCharts, 200);
            return;
        }
        const isStaff = @json($isStaff);

        // Destroy previous
        ['_cfChart','_ecChart','_imChart','_staffDaily','_staffStatus'].forEach(k => { if (window[k]) { try { window[k].destroy(); } catch(e){} } });

        if (isStaff) {
            const dailyData = @json($staffDailyOrders ?? []);
            const labels = Object.keys(dailyData).map(d => d.substring(8));
            const vals = Object.values(dailyData);
            const el1 = document.getElementById('staffDailyChart');
            if (el1) {
                window._staffDaily = new window.Chart(el1, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Order', data: vals, backgroundColor: '#00288e', borderRadius: 4 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { ticks: { font: { size: 10 } } } } }
                });
            }
            const statusData = @json($staffStatusDist ?? []);
            const el2 = document.getElementById('staffStatusChart');
            if (el2) {
                const has = Object.keys(statusData).length > 0;
                window._staffStatus = new window.Chart(el2, {
                    type: 'doughnut',
                    data: { labels: has ? Object.keys(statusData) : ['Belum ada data'], datasets: [{ data: has ? Object.values(statusData) : [1], backgroundColor: has ? ['#2563eb','#f59e0b','#22c55e','#ef4444','#8b5cf6','#06b6d4','#eab308'] : ['#e5e7eb'] }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } } }
                });
            }
            return;
        }

        const cashFlowData = @json($cashFlow);
        const cashFlowDates = Object.keys(cashFlowData);
        const cashFlowLabels = cashFlowDates.map(d => d.substring(8));
        const cashFlowIncome = cashFlowDates.map(d => cashFlowData[d].income);
        const cashFlowExpense = cashFlowDates.map(d => cashFlowData[d].expense);
        const cashFlowNet = cashFlowDates.map(d => cashFlowData[d].net);

        const cfEl = document.getElementById('cashFlowChart');
        if (cfEl) {
            window._cfChart = new window.Chart(cfEl, {
                type: 'bar',
                data: {
                    labels: cashFlowLabels,
                    datasets: [
                        { label: 'Pemasukan', data: cashFlowIncome, backgroundColor: '#22c55e', borderRadius: 4 },
                        { label: 'Pengeluaran', data: cashFlowExpense, backgroundColor: '#ef4444', borderRadius: 4 },
                        { label: 'Bersih', data: cashFlowNet, type: 'line', borderColor: '#3b82f6', backgroundColor: '#3b82f6', tension: 0.3, fill: false, pointRadius: 3 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID') } } },
                    scales: { y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } }, x: { ticks: { font: { size: 10 } } } }
                }
            });
        }

        const expenseCatData = @json($pengeluaranByKategori);
        const ecEl = document.getElementById('expenseCategoryChart');
        if (ecEl) {
            const hasEc = Object.keys(expenseCatData).length > 0;
            window._ecChart = new window.Chart(ecEl, {
                type: 'doughnut',
                data: {
                    labels: hasEc ? Object.keys(expenseCatData) : ['Belum ada data'],
                    datasets: [{ data: hasEc ? Object.values(expenseCatData) : [1], backgroundColor: hasEc ? ['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#8b5cf6','#ec4899','#6b7280','#14b8a6'] : ['#e5e7eb'] }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } }, tooltip: { callbacks: { label: ctx => ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID') } } }
                }
            });
        }

        const incomeMethodData = @json($pemasukanByMetode);
        const methodLabels = { tunai: 'Tunai', transfer: 'Transfer', dompet_digital: 'Dompet Digital', qris: 'QRIS', cash: 'Tunai' };
        const imEl = document.getElementById('incomeMethodChart');
        if (imEl) {
            const hasIm = Object.keys(incomeMethodData).length > 0;
            window._imChart = new window.Chart(imEl, {
                type: 'doughnut',
                data: {
                    labels: hasIm ? Object.keys(incomeMethodData).map(k => methodLabels[k] || k) : ['Belum ada data'],
                    datasets: [{ data: hasIm ? Object.values(incomeMethodData) : [1], backgroundColor: hasIm ? ['#22c55e','#3b82f6','#8b5cf6','#f59e0b','#06b6d4'] : ['#e5e7eb'] }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } }, tooltip: { callbacks: { label: ctx => ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID') } } }
                }
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderDashboardCharts);
    } else {
        renderDashboardCharts();
    }
    document.addEventListener('livewire:navigated', renderDashboardCharts);
    </script>
    @endpush
</x-layouts::app>
