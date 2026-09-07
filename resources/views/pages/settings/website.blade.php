@php
    $title = 'Website Settings';
@endphp

<x-layouts::app :title="$title">
    <div>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-on-surface">Website Settings</h2>
        </div>

        <form method="POST" action="{{ route('settings.website.save') }}" enctype="multipart/form-data" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Website Name</label>
                    <input type="text" name="name" value="{{ old('name', $settings['name'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('description', $settings['description'] ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Address</label>
                    <textarea name="alamat" rows="2"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('alamat', $settings['alamat'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Phone</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $settings['telepon'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $settings['email'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>

                {{-- Logo Upload --}}
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Logo</label>
                    @php
                        $logoUrl = \App\Models\WebsiteSetting::fileUrl($settings['logo'] ?? null);
                    @endphp
                    <div class="flex items-center gap-4 mb-2">
                        <div id="logo-preview" class="shrink-0 w-16 h-16 rounded-lg border border-outline-variant bg-surface flex items-center justify-center overflow-hidden">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo" class="max-w-full max-h-full object-contain">
                            @else
                                <span class="material-symbols-outlined text-on-surface-variant/40">image</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="logo" id="logo-input" accept="image/*"
                                class="w-full text-sm text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer"
                                onchange="previewImage(this, 'logo-preview')">
                            @if($logoUrl)
                                <label class="inline-flex items-center gap-1.5 mt-2 text-xs text-on-surface-variant cursor-pointer">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-outline-variant text-error focus:ring-error">
                                    Remove current logo
                                </label>
                            @endif
                        </div>
                    </div>
                    @error('logo')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Favicon Upload --}}
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Favicon</label>
                    @php
                        $faviconUrl = \App\Models\WebsiteSetting::fileUrl($settings['favicon'] ?? null);
                    @endphp
                    <div class="flex items-center gap-4 mb-2">
                        <div id="favicon-preview" class="shrink-0 w-10 h-10 rounded-lg border border-outline-variant bg-surface flex items-center justify-center overflow-hidden">
                            @if($faviconUrl)
                                <img src="{{ $faviconUrl }}" alt="Favicon" class="max-w-full max-h-full object-contain">
                            @else
                                <span class="material-symbols-outlined text-on-surface-variant/40 text-base">image</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="favicon" id="favicon-input" accept="image/*"
                                class="w-full text-sm text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer"
                                onchange="previewImage(this, 'favicon-preview')">
                            @if($faviconUrl)
                                <label class="inline-flex items-center gap-1.5 mt-2 text-xs text-on-surface-variant cursor-pointer">
                                    <input type="checkbox" name="remove_favicon" value="1" class="rounded border-outline-variant text-error focus:ring-error">
                                    Remove current favicon
                                </label>
                            @endif
                        </div>
                    </div>
                    @error('favicon')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Primary Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ old('primary_color', $settings['colors']['primary'] ?? '#00288e') }}"
                            class="h-10 w-16 border border-outline-variant rounded-lg cursor-pointer">
                        <input type="text" id="primary_color_hex"
                            value="{{ old('primary_color', $settings['colors']['primary'] ?? '#00288e') }}"
                            class="w-28 border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono"
                            pattern="#[0-9a-fA-F]{6}" placeholder="#00288e"
                            oninput="document.querySelector('input[name=primary_color]').value = this.value">
                    </div>
                    <p class="text-xs text-on-surface-variant mt-1">Updates all buttons, links, and accent colors.</p>
                </div>
                <div class="flex items-end">
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-outline-variant bg-surface">
                        <span class="text-sm text-on-surface-variant">Preview:</span>
                        <span class="inline-block w-8 h-8 rounded-lg" style="background-color: {{ $settings['colors']['primary'] ?? '#00288e' }}"></span>
                        <span class="px-3 py-1 rounded-lg text-xs font-semibold text-on-primary" style="background-color: {{ $settings['colors']['primary'] ?? '#00288e' }}">Primary</span>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Footer Text</label>
                    <textarea name="footer_text" rows="2"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('footer_text', $settings['footer_text'] ?? '') }}</textarea>
                </div>

                {{-- Offline / PWA --}}
                <div class="md:col-span-2">
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface flex items-start gap-4">
                        <div class="shrink-0 w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">wifi_off</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">Mode Offline (PWA)</p>
                                    <p class="text-xs text-on-surface-variant mt-0.5">Aktifkan agar POS & Dashboard tetap bisa dipakai tanpa internet. Data disimpan di browser (IndexedDB) dan sinkron otomatis saat online.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="offline_enabled" value="1" class="sr-only peer" {{ old('offline_enabled', $settings['offline_enabled'] ?? config('website.offline_enabled', true)) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-outline-variant rounded-full peer peer-checked:bg-primary transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                                </label>
                            </div>
                            <div class="mt-3 flex items-center gap-2 text-xs">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ ($settings['offline_enabled'] ?? config('website.offline_enabled', true)) ? 'bg-success/10 text-success border border-success/20' : 'bg-outline-variant/50 text-on-surface-variant border border-outline-variant' }}">
                                    <span class="w-2 h-2 rounded-full {{ ($settings['offline_enabled'] ?? config('website.offline_enabled', true)) ? 'bg-success' : 'bg-on-surface-variant/50' }}"></span>
                                    {{ ($settings['offline_enabled'] ?? config('website.offline_enabled', true)) ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <span class="text-on-surface-variant">Status saat ini: <span id="offline-setting-status" class="font-medium text-on-surface">mengecek…</span></span>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-2">Nonaktifkan jika tidak butuh offline — service worker & sync akan dimatikan.</p>
                </div>

                {{-- Staff Target & Fee (khusus order yang bisa dilihat/diedit) --}}
                <div class="md:col-span-2">
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center"><span class="material-symbols-outlined text-warning">trophy</span></div>
                            <div>
                                <p class="text-sm font-semibold text-on-surface">Target & Insentif Staff (Order)</p>
                                <p class="text-xs text-on-surface-variant">Khusus order yang staff bisa lihat & edit (order miliknya). Jika melebihi target, fee per order akan dihitung.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1">Target per Bulan (order)</label>
                                <input type="number" name="staff_target" min="1" max="10000" value="{{ old('staff_target', $settings['staff_target'] ?? config('website.staff_target', 100)) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                                <p class="text-xs text-on-surface-variant mt-1">Contoh: 100 → staff harus kerjakan 100 order/bulan.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1">Fee per Order Lebih (Rp)</label>
                                <input type="number" name="staff_fee" min="0" max="1000000" step="100" value="{{ old('staff_fee', $settings['staff_fee'] ?? config('website.staff_fee', 1000)) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                                <p class="text-xs text-on-surface-variant mt-1">Contoh: 1000 → 120 order = 20×1000 = Rp 20.000 fee.</p>
                            </div>
                        </div>
                        <div class="mt-3 p-2.5 rounded-lg bg-primary/5 border border-primary/10 text-xs text-on-surface-variant">
                            Rumus: <span class="font-mono font-semibold">fee = max(0, order_bulan_ini − target) × fee_per_order</span> — hanya hitung order milik staff tersebut.
                        </div>
                    </div>
                </div>

                {{-- Store Location (untuk absensi 50m) --}}
                <div class="md:col-span-2">
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center"><span class="material-symbols-outlined text-primary">location_on</span></div>
                            <div>
                                <p class="text-sm font-semibold text-on-surface">Lokasi Toko (Absensi)</p>
                                <p class="text-xs text-on-surface-variant">Jika laundry belum set koordinat, pakai config ini. Radius default 50 m — check-in/out valid jika ≤ radius.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1">Latitude</label>
                                <input type="text" name="store_latitude" value="{{ old('store_latitude', $settings['store_latitude'] ?? config('website.store_latitude', '-6.2000000')) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono" placeholder="-6.2000000">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1">Longitude</label>
                                <input type="text" name="store_longitude" value="{{ old('store_longitude', $settings['store_longitude'] ?? config('website.store_longitude', '106.8166660')) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono" placeholder="106.8166660">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-on-surface mb-1">Radius (meter)</label>
                                <input type="number" name="store_radius" min="10" max="1000" value="{{ old('store_radius', $settings['store_radius'] ?? config('website.store_radius', 50)) }}" class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                            </div>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-2">Lihat <a href="https://maps.google.com" target="_blank" class="text-primary hover:underline">Google Maps</a> untuk koordinat — klik kanan → copy coordinates. Per laundry juga bisa di-set via DB `laundry_latitude/longitude`.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-outline-variant">
                <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 transition-opacity">
                    Save Settings
                </button>
                <a href="{{ route('dashboard') }}" wire:navigate class="text-sm text-on-surface-variant hover:text-on-surface">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="max-w-full max-h-full object-contain">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        (function() {
            const el = document.getElementById('offline-setting-status');
            if (!el) return;
            function upd() { el.textContent = navigator.onLine ? 'Online' : 'Offline'; el.className = navigator.onLine ? 'font-medium text-success' : 'font-medium text-warning'; }
            upd();
            window.addEventListener('online', upd);
            window.addEventListener('offline', upd);
        })();
    </script>
    @endpush
</x-layouts::app>