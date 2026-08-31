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

        {{-- Ringkasan Cards --}}
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

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">account_balance_wallet</span>
                    Arus Kas Harian
                </h3>
                <div id="cashFlowChart" class="w-full h-64"></div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">pie_chart</span>
                    Pengeluaran per Kategori
                </h3>
                <div id="expenseCategoryChart" class="w-full h-64"></div>
            </div>
        </div>

        {{-- Pemasukan by Metode + Recent Expenses --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">payment</span>
                    Pemasukan per Metode Bayar
                </h3>
                <div id="incomeMethodChart" class="w-full h-64"></div>
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
    </div>

    @push('scripts')
    <script>
        const cashFlowData = @json($cashFlow);
        const cashFlowDates = Object.keys(cashFlowData);
        const cashFlowIncome = cashFlowDates.map(d => cashFlowData[d].income);
        const cashFlowExpense = cashFlowDates.map(d => cashFlowData[d].expense);
        const cashFlowNet = cashFlowDates.map(d => cashFlowData[d].net);

        new ApexCharts(document.querySelector('#cashFlowChart'), {
            chart: { type: 'bar', height: 256, toolbar: { show: false } },
            series: [
                { name: 'Pemasukan', data: cashFlowIncome },
                { name: 'Pengeluaran', data: cashFlowExpense },
                { name: 'Bersih', data: cashFlowNet, type: 'line' }
            ],
            xaxis: { categories: cashFlowDates.map(d => d.substring(8)), labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { formatter: v => 'Rp ' + v.toLocaleString('id-ID') } },
            colors: ['#22c55e', '#ef4444', '#3b82f6'],
            plotOptions: { bar: { columnWidth: '60%' } },
            stroke: { width: [0, 0, 2] },
            tooltip: { y: { formatter: v => 'Rp ' + v.toLocaleString('id-ID') } }
        }).render();

        const expenseCatData = @json($pengeluaranByKategori);
        new ApexCharts(document.querySelector('#expenseCategoryChart'), {
            chart: { type: 'donut', height: 256 },
            series: Object.values(expenseCatData).length ? Object.values(expenseCatData) : [1],
            labels: Object.keys(expenseCatData).length ? Object.keys(expenseCatData) : ['Belum ada data'],
            colors: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#8b5cf6', '#ec4899', '#6b7280', '#14b8a6'],
            plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', formatter: () => 'Rp ' + Object.values(expenseCatData).reduce((a,b) => a+b, 0).toLocaleString('id-ID') } } } } }
        }).render();

        const incomeMethodData = @json($pemasukanByMetode);
        const methodLabels = { tunai: 'Tunai', transfer: 'Transfer', dompet_digital: 'Dompet Digital' };
        new ApexCharts(document.querySelector('#incomeMethodChart'), {
            chart: { type: 'donut', height: 256 },
            series: Object.values(incomeMethodData).length ? Object.values(incomeMethodData) : [1],
            labels: Object.keys(incomeMethodData).length ? Object.keys(incomeMethodData).map(k => methodLabels[k] || k) : ['Belum ada data'],
            colors: ['#22c55e', '#3b82f6', '#8b5cf6'],
            plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', formatter: () => 'Rp ' + Object.values(incomeMethodData).reduce((a,b) => a+b, 0).toLocaleString('id-ID') } } } } }
        }).render();
    </script>
    @endpush
</x-layouts::app>
