<x-layouts::app title="Pilih Cabang">
    @php
        $user = auth()->user();
        $roleLabel = match($user->role ?? '') {
            'developer' => 'Owner',
            'admin' => 'Admin',
            'editor' => 'Staff',
            'user' => 'Customer',
            default => ucfirst($user->role ?? 'Staff'),
        };
    @endphp

    <div class="max-w-4xl mx-auto">
        {{-- Greeting header --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 md:p-6 mb-6 flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-lg shrink-0">
                {{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name ?? 'Staff')[1] ?? '', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-on-surface leading-tight">Halo, {{ $user->name ?? 'Staff' }} 👋</h1>
                <p class="text-sm text-on-surface-variant mt-1">Pilih cabang untuk memulai shift hari ini. Cabang menentukan stok, harga, dan transaksi POS.</p>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border bg-primary/10 border-primary/20 text-primary">
                        <span class="material-symbols-outlined text-[14px]">badge</span> {{ $roleLabel }}
                    </span>
                    <span class="text-xs text-on-surface-variant">{{ $user->email }}</span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-success/10 text-success border border-success/20">
                        <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span> Online
                    </span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                @csrf
                <button type="submit" class="text-xs text-on-surface-variant hover:text-error flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">logout</span> Keluar
                </button>
            </form>
        </div>

        <form method="POST" action="{{ route('laundry.select') }}" id="picker-form">
            @csrf

            {{-- Search + count --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                    <input type="search" id="laundry-search" placeholder="Cari cabang atau kode..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-outline-variant bg-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <span class="text-xs text-on-surface-variant whitespace-nowrap"><span id="laundry-count">{{ $laundries->count() }}</span> cabang</span>
            </div>

            @if ($laundries->isEmpty())
                <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-10 text-center">
                    <h3 class="font-semibold text-on-surface mt-3">Belum ada cabang untuk akun ini</h3>
                    <p class="text-sm text-on-surface-variant mt-1 max-w-md mx-auto">Hubungi admin/owner untuk ditambahkan ke cabang. Kamu akan otomatis masuk setelah di-assign.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="laundry-grid">
                    @foreach ($laundries as $laundry)
                        <label class="picker-card group relative flex flex-col p-4 md:p-5 rounded-2xl border-2 border-outline-variant bg-surface-container-lowest cursor-pointer transition-all hover:shadow-md hover:border-primary/30" data-name="{{ strtolower($laundry->laundry_nama . ' ' . $laundry->laundry_kode) }}">
                            <input type="radio" name="laundry_id" value="{{ $laundry->laundry_id }}" required class="sr-only">
                            {{-- Selected check --}}
                            <span class="picker-check absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-outline-variant bg-surface flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-white text-[14px] opacity-0 transition-opacity">check</span>
                            </span>

                            <div class="flex items-start gap-3 pr-6">
                                <div class="picker-icon w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 transition-colors">
                                    <span class="material-symbols-outlined">local_laundry_service</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-semibold text-on-surface leading-tight truncate">{{ $laundry->laundry_nama }}</h3>
                                    <p class="text-xs font-mono text-on-surface-variant mt-0.5">{{ $laundry->laundry_kode }}</p>
                                </div>
                            </div>

                            <div class="mt-3 space-y-1.5 text-xs text-on-surface-variant">
                                @if($laundry->laundry_alamat)
                                    <p class="flex items-start gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5 shrink-0">location_on</span><span class="line-clamp-2">{{ $laundry->laundry_alamat }}</span></p>
                                @endif
                                @if($laundry->laundry_telepon)
                                    <p class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[14px] shrink-0">call</span>{{ $laundry->laundry_telepon }}</p>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                @if($laundry->laundry_is_aktif)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-success/10 text-success border border-success/20">● Aktif</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-error/10 text-error border border-error/20">● Nonaktif</span>
                                @endif
                                <span class="picker-cta text-[11px] text-on-surface-variant ml-auto font-medium">Pilih cabang →</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                {{-- Sticky action --}}
                <div class="sticky bottom-20 md:bottom-6 mt-6 bg-surface-container-lowest border border-outline-variant rounded-2xl p-3 md:p-4 flex items-center gap-3 shadow-lg">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-on-surface-variant">Cabang terpilih</p>
                        <p id="selected-name" class="text-sm font-semibold text-on-surface truncate">Belum memilih</p>
                    </div>
                    <button type="submit" id="btn-masuk" disabled class="shrink-0 inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-xl font-semibold text-sm disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90 transition-opacity">
                        Masuk <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </div>
            @endif
        </form>

        <p class="text-center text-xs text-on-surface-variant mt-4">Tip: Staff bisa di-assign ke beberapa cabang. Pilih salah satu untuk sesi ini — bisa ganti kapan saja via menu cabang.</p>
    </div>

    @push('scripts')
    <script>
        (function() {
            const search = document.getElementById('laundry-search');
            const grid = document.getElementById('laundry-grid');
            const count = document.getElementById('laundry-count');
            const selectedName = document.getElementById('selected-name');
            const btn = document.getElementById('btn-masuk');
            if (!grid) return;
            const cards = [...grid.querySelectorAll('label[data-name]')];
            const radios = [...grid.querySelectorAll('input[type=radio]')];

            function updateSelected() {
                const checked = grid.querySelector('input:checked');
                // Visual selected state via JS (Tailwind has- tidak reliable di build)
                cards.forEach(c => {
                    const isChecked = c.querySelector('input:checked');
                    const check = c.querySelector('.picker-check');
                    const checkIcon = check?.querySelector('.material-symbols-outlined');
                    const icon = c.querySelector('.picker-icon');
                    const cta = c.querySelector('.picker-cta');
                    if (isChecked) {
                        c.classList.add('!border-primary', '!bg-primary/5', 'shadow-md');
                        c.classList.remove('border-outline-variant');
                        if (check) { check.classList.add('!border-primary', '!bg-primary'); check.classList.remove('border-outline-variant', 'bg-surface'); }
                        if (checkIcon) checkIcon.classList.remove('opacity-0');
                        if (checkIcon) checkIcon.classList.add('opacity-100');
                        if (icon) { icon.classList.add('!bg-primary', '!text-on-primary'); icon.classList.remove('bg-primary/10', 'text-primary'); }
                        if (cta) { cta.classList.add('!text-primary'); cta.classList.remove('text-on-surface-variant'); }
                    } else {
                        c.classList.remove('!border-primary', '!bg-primary/5', 'shadow-md');
                        c.classList.add('border-outline-variant');
                        if (check) { check.classList.remove('!border-primary', '!bg-primary'); check.classList.add('border-outline-variant', 'bg-surface'); }
                        if (checkIcon) { checkIcon.classList.add('opacity-0'); checkIcon.classList.remove('opacity-100'); }
                        if (icon) { icon.classList.remove('!bg-primary', '!text-on-primary'); icon.classList.add('bg-primary/10', 'text-primary'); }
                        if (cta) { cta.classList.remove('!text-primary'); cta.classList.add('text-on-surface-variant'); }
                    }
                });

                if (checked) {
                    const label = checked.closest('label');
                    const name = label.querySelector('h3')?.textContent?.trim() || 'Cabang terpilih';
                    if (selectedName) selectedName.textContent = name;
                    if (btn) btn.disabled = false;
                } else {
                    if (selectedName) selectedName.textContent = 'Belum memilih';
                    if (btn) btn.disabled = true;
                }
            }

            radios.forEach(r => r.addEventListener('change', updateSelected));
            // Click anywhere on card = check radio
            cards.forEach(c => c.addEventListener('click', (e) => {
                const input = c.querySelector('input[type=radio]');
                if (input && !input.checked) { input.checked = true; input.dispatchEvent(new Event('change', {bubbles: true})); }
            }));
            // Auto select first for convenience
            if (radios.length === 1) { radios[0].checked = true; updateSelected(); }

            if (search) {
                search.addEventListener('input', () => {
                    const q = search.value.toLowerCase().trim();
                    let visible = 0;
                    cards.forEach(c => {
                        const match = !q || (c.dataset.name || '').includes(q);
                        c.style.display = match ? '' : 'none';
                        if (match) visible++;
                    });
                    if (count) count.textContent = visible;
                });
            }
        })();
    </script>
    @endpush
</x-layouts::app>
