<x-layouts::app title="Absen Check-in/out">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-xl font-bold text-on-surface mb-1">Absen Hari Ini</h2>
        <p class="text-sm text-on-surface-variant mb-4">Toko: <b>{{ $laundry?->laundry_nama ?? config('website.name') }}</b> · Radius {{ $radius }} m · Valid jika ≤ {{ $radius }} m dari toko.</p>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-error-container text-on-error-container text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-success/10 border border-success/20 text-success text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 mb-4">
            <div id="map" class="w-full h-64 rounded-xl border border-outline-variant mb-3 relative z-0"></div>
            <div class="flex items-center gap-2 text-sm">
                <span class="material-symbols-outlined text-primary">my_location</span>
                <span>Lokasi kamu: <span id="my-coords" class="font-mono">—</span></span>
                <span class="ml-auto">Jarak ke toko: <b id="my-jarak">—</b></span>
                <span id="my-valid" class="px-2 py-0.5 rounded-full text-xs border">—</span>
            </div>
            <p class="text-xs text-on-surface-variant mt-2">Toko: <span class="font-mono">{{ $storeLat }}, {{ $storeLng }}</span> · Izinkan lokasi browser, tekan Check-in/out jika valid.</p>
        </div>

        @php
            $today = now()->toDateString();
            $hasIn = $existing?->attendance_checkin_at;
            $hasOut = $existing?->attendance_checkout_at;
        @endphp

        @if(!$hasIn)
            <form method="POST" action="{{ route('staff-attendance.postCheckin') }}" id="form-checkin" class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4">
                @csrf
                <input type="hidden" name="lat" id="checkin-lat">
                <input type="hidden" name="lng" id="checkin-lng">
                <h3 class="font-semibold mb-3">Check-in Masuk</h3>
                <button type="submit" id="btn-checkin" disabled class="w-full py-3 rounded-xl font-semibold bg-primary text-on-primary disabled:opacity-40 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">login</span> Check-in Masuk
                </button>
                <p class="text-xs text-on-surface-variant mt-2 text-center" id="checkin-hint">Menunggu lokasi valid…</p>
            </form>
        @elseif(!$hasOut)
            <div class="bg-success/10 border border-success/20 rounded-2xl p-4 mb-4 text-sm">
                Sudah check-in <b>{{ $existing->attendance_checkin_at->format('H:i') }}</b> ({{ $existing->attendance_checkin_jarak }} m) — silakan checkout saat pulang.
            </div>
            <form method="POST" action="{{ route('staff-attendance.postCheckout') }}" id="form-checkout" class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-4">
                @csrf
                <input type="hidden" name="lat" id="checkout-lat">
                <input type="hidden" name="lng" id="checkout-lng">
                <h3 class="font-semibold mb-3">Check-out Pulang</h3>
                <button type="submit" id="btn-checkout" disabled class="w-full py-3 rounded-xl font-semibold bg-error text-on-error disabled:opacity-40 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">logout</span> Check-out Pulang
                </button>
                <p class="text-xs text-on-surface-variant mt-2 text-center" id="checkout-hint">Menunggu lokasi valid…</p>
            </form>
        @else
            <div class="bg-success/10 border border-success/20 rounded-2xl p-6 text-center">
                <span class="material-symbols-outlined text-5xl text-success">check_circle</span>
                <p class="font-semibold mt-2">Absen hari ini selesai</p>
                <p class="text-sm text-on-surface-variant">In {{ $existing->attendance_checkin_at->format('H:i') }} → Out {{ $existing->attendance_checkout_at->format('H:i') }}</p>
            </div>
        @endif

        <div class="mt-4 text-center">
            <a href="{{ route('staff-attendance.getTable') }}" wire:navigate class="text-sm text-primary hover:underline">Lihat riwayat →</a>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush
    @push('scripts')
    <script data-leaflet src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {
        const storeLat = parseFloat(@json($storeLat));
        const storeLng = parseFloat(@json($storeLng));
        const radius = parseInt(@json($radius), 10);

        function haversine(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const toRad = x => x * Math.PI / 180;
            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLng/2)**2;
            return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
        }

        let map, storeMarker, myMarker, circle;
        let curLat = null, curLng = null, curJarak = null;

        function initMap() {
            if (typeof L === 'undefined' || !document.getElementById('map')) {
                return false;
            }
            // ponytail: navigate bolak-balik re-eksekusi script; buang map lama.
            if (map) {
                try { map.remove(); } catch (e) {}
                map = null;
            }
            map = L.map('map').setView([storeLat, storeLng], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
            storeMarker = L.marker([storeLat, storeLng]).addTo(map).bindPopup('Toko').openPopup();
            circle = L.circle([storeLat, storeLng], { radius, color: '#16a34a', fillOpacity: 0.15 }).addTo(map);

            return true;
        }

        // ponytail: saat Livewire navigate, script inline dieksekusi langsung
        // sementara leaflet.js eksternal masih diunduh → tunggu L tersedia.
        function ensureLeaflet(cb) {
            if (typeof L !== 'undefined') {
                cb();

                return;
            }
            let s = document.querySelector('script[data-leaflet]');
            if (!s) {
                s = document.createElement('script');
                s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                s.setAttribute('data-leaflet', '1');
                s.onload = cb;
                s.onerror = function () { alert('Gagal memuat peta. Periksa koneksi.'); };
                document.head.appendChild(s);
            } else {
                s.addEventListener('load', cb, { once: true });
            }
        }

        function updateUI(lat, lng) {
            curLat = lat; curLng = lng;
            curJarak = haversine(storeLat, storeLng, lat, lng);
            const valid = curJarak <= radius;
            document.getElementById('my-coords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
            document.getElementById('my-jarak').textContent = curJarak + ' m';
            const badge = document.getElementById('my-valid');
            badge.textContent = valid ? 'Valid' : 'Di luar radius';
            badge.className = 'px-2 py-0.5 rounded-full text-xs border ' + (valid ? 'bg-success/10 border-success/20 text-success' : 'bg-error/10 border-error/20 text-error');

            // Update form hiddens + buttons
            ['checkin-lat','checkin-lng','checkout-lat','checkout-lng'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { if (id.includes('lat')) el.value = lat; else el.value = lng; }
            });
            const btnIn = document.getElementById('btn-checkin');
            const btnOut = document.getElementById('btn-checkout');
            const hintIn = document.getElementById('checkin-hint');
            const hintOut = document.getElementById('checkout-hint');
            if (btnIn) { btnIn.disabled = !valid; if (hintIn) hintIn.textContent = valid ? 'Lokasi valid — bisa check-in' : 'Mendekat ke toko (< ' + radius + ' m)'; }
            if (btnOut) { btnOut.disabled = !valid; if (hintOut) hintOut.textContent = valid ? 'Lokasi valid — bisa check-out' : 'Mendekat ke toko'; }

            if (myMarker) map.removeLayer(myMarker);
            myMarker = L.marker([lat, lng]).addTo(map).bindPopup('Kamu').openPopup();
            map.setView([lat, lng], 17);
        }

        function start() {
            if (!document.getElementById('map')) {
                return;
            }
            // ponytail: hentikan watcher kunjungan sebelumnya agar tidak menumpuk.
            if (window.__absenWatch != null && navigator.geolocation) {
                try { navigator.geolocation.clearWatch(window.__absenWatch); } catch (e) {}
                window.__absenWatch = null;
            }
            ensureLeaflet(function () {
                if (!initMap()) {
                    return;
                }
                if (!navigator.geolocation) {
                    alert('Browser tidak mendukung geolocation');
                    return;
                }
                navigator.geolocation.getCurrentPosition(pos => {
                    updateUI(pos.coords.latitude, pos.coords.longitude);
                }, err => {
                    alert('Gagal ambil lokasi: ' + err.message + ' — izinkan lokasi browser.');
                }, { enableHighAccuracy: true, timeout: 10000 });

                // Watch for checkout/in movement
                window.__absenWatch = navigator.geolocation.watchPosition(pos => updateUI(pos.coords.latitude, pos.coords.longitude), null, { enableHighAccuracy: true });
            });
        }

        // ponytail: DOMContentLoaded sudah lewat saat Livewire navigate,
        // jadi jalankan langsung bila dokumen sudah siap.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', start);
        } else {
            start();
        }
    })();
    </script>
    @endpush
</x-layouts::app>
