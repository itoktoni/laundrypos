@props(['empty' => 'No data found.'])
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden form-card">
    <div class="p-0">
        {{-- Desktop: visible lg and up --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b-2 border-outline-variant bg-surface-container">
                        {{ $head }}
                    </tr>
                </thead>
                <tbody class="[&>tr]:border-b [&>tr]:border-outline-variant/50 [&>tr:last-child]:border-b-0 [&>tr]:hover:bg-surface-container-low/50">
                    {{ $body }}
                </tbody>
            </table>
        </div>

        {{-- Mobile: visible below lg — pure CSS breakpoint, no server UA sniff --}}
        @isset($mobile)
            <div class="lg:hidden">{{ $mobile }}</div>
        @else
            {{-- Fallback: responsive card-like view via same data (avoid blank). Will be overridden once page defines <x-slot:mobile> --}}
            <div class="lg:hidden overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b-2 border-outline-variant bg-surface-container">
                            {{ $head }}
                        </tr>
                    </thead>
                    <tbody class="[&>tr]:border-b [&>tr]:border-outline-variant/50 [&>tr:last-child]:border-b-0 [&>tr]:hover:bg-surface-container-low/50">
                        {{ $body }}
                    </tbody>
                </table>
            </div>
            <p class="lg:hidden text-[10px] text-center text-on-surface-variant/60 py-2">* Belum ada tampilan card mobile — tambahkan &lt;x-slot:mobile&gt; untuk UX optimal</p>
        @endisset
    </div>
</div>
<style>
    thead tr th {
        padding: 0.875rem 1.25rem;
        text-align: left;
        vertical-align: middle;
        font-size: 0.8125rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #191c1e;
        background: #f2f4f6;
        border-bottom: 2px solid #c4c5d5;
    }
    tbody tr td {
        padding: 0.75rem 1.25rem;
        text-align: left;
        vertical-align: middle;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #191c1e;
        border-bottom: 1px solid #eceef0;
    }
    tbody tr:last-child td {
        border-bottom: none;
    }
</style>
