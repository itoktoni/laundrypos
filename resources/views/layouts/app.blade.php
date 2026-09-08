<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

 @include('layouts.head')

<body class="text-on-surface bg-surface antialiased font-body-sm overflow-x-hidden" x-data="warehouseApp()">

    <x-offline-banner />

    {{-- Overlay for mobile drawer (class statis = state awal drawerOpen=false, mencegah flash sebelum Alpine boot) --}}
    <div class="fixed inset-0 bg-black/40 z-40 md:hidden transition-opacity duration-200 opacity-0 pointer-events-none" :class="drawerOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" @click="drawerOpen = false"></div>

    @php $isPos = request()->routeIs('pos.index'); @endphp

    @if(!$isPos)
        @include('layouts.header')
        @include('layouts.mobile')
        @include('layouts.sidebar')
    @else
        {{-- POS: header minimal — fokus kasir, tanpa sidebar penuh --}}
        <header class="fixed top-0 w-full z-50 bg-surface-container-lowest shadow-sm border-b-2 flex items-center justify-between px-3 md:px-6 h-16" style="border-bottom-color: #16a34a;">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" wire:navigate class="w-9 h-9 rounded-full hover:bg-surface-container flex items-center justify-center"><span class="material-symbols-outlined text-xl">arrow_back</span></a>
                <span class="font-bold text-primary truncate">{{ config('website.name') }} — POS Kasir</span>
                @php $curLaundry = \App\Models\Laundry::find(session('laundry_id')); @endphp
                @if($curLaundry)<span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium"><span class="material-symbols-outlined text-sm">storefront</span> {{ $curLaundry->laundry_nama }}</span>@endif
            </div>
            <a href="{{ route('order.getTable') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-outline-variant text-xs font-medium hover:bg-surface-container"><span class="material-symbols-outlined text-sm">receipt_long</span> Orders</a>
        </header>
    @endif

    {{-- Main Content --}}
    @if($isPos)
        <main class="px-3 md:px-4" style="padding-top: 4rem; padding-bottom: 5rem;">
            <div class="max-w-full mx-auto pt-4">
                {{ $slot }}
            </div>
        </main>
    @else
        <main class="px-4 md:px-6 md:ml-72" style="padding-top: 4rem; padding-bottom: calc(8rem + env(safe-area-inset-bottom));" :class="sidebarOpen ? 'md:ml-72' : 'md:ml-0'">
            <div class="max-w-full md:max-w-[calc(100vw-18rem)] mx-auto pt-4">
                {{ $slot }}
            </div>
        </main>
    @endif
    {{-- Bottom Nav (Mobile) — tetap tampil di POS --}}
    <x-bottom-nav />

    {{-- Toast notifications --}}
    <x-toast />

    @stack('scripts')

    @include('layouts.script')
    @livewireScripts

    <x-printer-modal />
</body>
</html>
