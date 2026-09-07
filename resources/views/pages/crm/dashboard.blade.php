<?php
/** @var int $totalCustomers */
/** @var int $vipCount */
/** @var float $vipPercentage */
/** @var int $newCustomers */
/** @var int $churnedCount */
/** @var int $unpickedOrders */
/** @var float $unpickedRevenue */
/** @var int $promoReturnCount */
/** @var int $promoUsageCount */
/** @var float $promoReturnRate */
/** @var float $avgOrdersPerCustomer */
/** @var float $growthRate */
/** @var \Illuminate\Support\Collection $topCustomers */
/** @var \Carbon\Carbon $startDate */
/** @var \Carbon\Carbon $endDate */
?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'CRM']]" />

    <div class="content mt-4 lg:mt-0 space-y-6">
        {{-- Header + Filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-on-surface">Customer Relationship Management</h1>
                <p class="text-sm text-on-surface-variant">Analitik pelanggan dan performa bisnis</p>
            </div>
            <form method="GET" class="flex items-end gap-2 flex-wrap">
                <div>
                    <label class="text-xs text-on-surface-variant mb-1 block">Dari</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="input input-sm" />
                </div>
                <div>
                    <label class="text-xs text-on-surface-variant mb-1 block">Sampai</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="input input-sm" />
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-soft">Reset</a>
            </form>
        </div>

        {{-- Stats Row 1 --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">people</span>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wide">Total Pelanggan</span>
                </div>
                <p class="text-2xl font-bold text-on-surface">{{ $totalCustomers }}</p>
            </div>

            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#f59e0b] text-[20px]">workspace_premium</span>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wide">VIP</span>
                </div>
                <p class="text-2xl font-bold text-on-surface">{{ $vipCount }}</p>
                <p class="text-xs text-on-surface-variant mt-1">{{ $vipPercentage }}% dari total</p>
            </div>

            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#10b981] text-[20px]">person_add</span>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wide">Pelanggan Baru</span>
                </div>
                <p class="text-2xl font-bold text-on-surface">{{ $newCustomers }}</p>
                <p class="text-xs text-on-surface-variant mt-1">30 hari terakhir</p>
            </div>

            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#ef4444] text-[20px]">person_off</span>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wide">Churned</span>
                </div>
                <p class="text-2xl font-bold text-on-surface">{{ $churnedCount }}</p>
                <p class="text-xs text-on-surface-variant mt-1">Tidak balik {{ $churnDays }} hari</p>
            </div>
        </div>

        {{-- Stats Row 2 --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#8b5cf6] text-[20px]">local_laundry_service</span>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wide">Belum Diambil</span>
                </div>
                <p class="text-2xl font-bold text-on-surface">{{ $unpickedOrders }}</p>
                <p class="text-xs text-on-surface-variant mt-1">Rp {{ formatAngka($unpickedRevenue) }}</p>
            </div>

            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#06b6d4] text-[20px]">sell</span>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wide">Promo Return Rate</span>
                </div>
                <p class="text-2xl font-bold text-on-surface">{{ $promoReturnRate }}%</p>
                <p class="text-xs text-on-surface-variant mt-1">{{ $promoReturnCount }}/{{ $promoUsageCount }} pakai promo</p>
            </div>

            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#3b82f6] text-[20px]">avg_pace</span>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wide">Rata-rata Order</span>
                </div>
                <p class="text-2xl font-bold text-on-surface">{{ $avgOrdersPerCustomer }}</p>
                <p class="text-xs text-on-surface-variant mt-1">per pelanggan</p>
            </div>

            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[#10b981] text-[20px]">trending_up</span>
                    <span class="text-xs text-on-surface-variant uppercase tracking-wide">Pertumbuhan</span>
                </div>
                <p class="text-2xl font-bold text-on-surface {{ $growthRate >= 0 ? 'text-[#10b981]' : 'text-[#ef4444]' }}">
                    {{ $growthRate >= 0 ? '+' : '' }}{{ $growthRate }}%
                </p>
                <p class="text-xs text-on-surface-variant mt-1">vs bulan lalu</p>
            </div>
        </div>

        {{-- Info Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- VIP Info --}}
            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <h3 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">workspace_premium</span>
                    Threshold VIP
                </h3>
                <p class="text-sm text-on-surface-variant">
                    Pelanggan menjadi VIP jika memiliki minimal <strong class="text-on-surface">{{ $vipThreshold }} order</strong>.
                    Saat ini <strong class="text-on-surface">{{ $vipCount }}</strong> pelanggan ({{ $vipPercentage }}%) adalah VIP.
                </p>
            </div>

            {{-- Churn Info --}}
            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                <h3 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">person_off</span>
                    Churn Detection
                </h3>
                <p class="text-sm text-on-surface-variant">
                    Pelanggan dianggap churned jika tidak melakukan order selama <strong class="text-on-surface">{{ $churnDays }} hari</strong>.
                    Saat ini <strong class="text-[#ef4444]">{{ $churnedCount }}</strong> pelanggan churned.
                </p>
            </div>
        </div>

        {{-- Top Customers --}}
        <div class="border border-outline-variant rounded-xl bg-surface-container-lowest shadow-sm">
            <div class="p-4 border-b border-outline-variant">
                <h3 class="font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">leaderboard</span>
                    Top Pelanggan
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-outline-variant bg-surface-container">
                            <th class="text-left px-4 py-3 text-xs font-bold uppercase text-on-surface-variant">#</th>
                            <th class="text-left px-4 py-3 text-xs font-bold uppercase text-on-surface-variant">Nama</th>
                            <th class="text-left px-4 py-3 text-xs font-bold uppercase text-on-surface-variant">Telepon</th>
                            <th class="text-left px-4 py-3 text-xs font-bold uppercase text-on-surface-variant">Total Order</th>
                            <th class="text-left px-4 py-3 text-xs font-bold uppercase text-on-surface-variant">Status</th>
                        </tr>
                    </thead>
                    <tbody class="[&>tr]:border-b [&>tr]:border-outline-variant/50 [&>tr:last-child]:border-b-0">
                        @forelse ($topCustomers as $i => $customer)
                            <tr>
                                <td class="px-4 py-3 text-sm text-on-surface-variant">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-on-surface">{{ $customer->customer_nama }}</td>
                                <td class="px-4 py-3 text-sm text-on-surface-variant">{{ $customer->customer_telepon ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-on-surface">{{ $customer->order_count }}</td>
                                <td class="px-4 py-3">
                                    @if ($customer->order_count >= $vipThreshold)
                                        <span class="badge badge-warning">VIP</span>
                                    @else
                                        <span class="badge badge-ghost">Regular</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-on-surface-variant">Belum ada data order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
