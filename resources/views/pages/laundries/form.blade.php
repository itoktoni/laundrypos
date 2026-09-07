<?php /** @var App\Models\Laundry $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Cabang Laundry'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Cabang Laundry">
            @bind($model ?? null)

                <x-input col="6" name="laundry_nama" label="Nama" required />
                <x-input col="6" name="laundry_kode" label="Kode (unik)" required />
                <x-textarea col="12" name="laundry_alamat" label="Alamat" />
                <x-input col="6" name="laundry_telepon" label="Telepon" />
                <x-input col="3" name="laundry_latitude" label="Latitude" type="text" helper="Contoh -6.2000000" />
                <x-input col="3" name="laundry_longitude" label="Longitude" type="text" helper="106.8166660" />
                <x-input col="3" name="laundry_radius_m" label="Radius (m)" type="number" helper="Default 50m untuk absensi" />
                <x-select col="3" name="laundry_is_aktif" label="Aktif" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />

            @endbind
        </x-card>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 mt-4">
            <div class="flex items-center justify-between gap-2 mb-2">
                <p class="text-xs font-semibold text-on-surface flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">map</span> Pilih di peta (opsional)</p>
                <button type="button" id="btn-lokasi-saya" class="btn btn-sm btn-soft gap-1"><span class="material-symbols-outlined text-sm">my_location</span> Lokasi saya</button>
            </div>
            <div id="laundry-map" class="w-full h-64 rounded-xl border border-outline-variant relative z-0"></div>
            <p class="text-xs text-on-surface-variant mt-2">Klik peta / geser pin / pakai lokasi saat ini untuk isi latitude/longitude. Radius lingkaran sesuai input.</p>
        </div>

        <x-action :model="$model" :action="['save']" />
    </x-form>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush
    @push('scripts')
    <script data-leaflet src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {
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

        function start() {
            const container = document.getElementById('laundry-map');
            if (!container || container.hasAttribute('data-map-ready')) {
                return;
            }
            container.setAttribute('data-map-ready', '1');

            const latInput = document.querySelector('input[name="laundry_latitude"]');
            const lngInput = document.querySelector('input[name="laundry_longitude"]');
            const radiusInput = document.querySelector('input[name="laundry_radius_m"]');
            const btnLokasi = document.getElementById('btn-lokasi-saya');
            const defaultLat = parseFloat(latInput?.value) || -6.2000000;
            const defaultLng = parseFloat(lngInput?.value) || 106.8166660;
            const defaultRadius = parseInt(radiusInput?.value || 50, 10);

            ensureLeaflet(function () {
                const map = L.map('laundry-map').setView([defaultLat, defaultLng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
                let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
                let circle = L.circle([defaultLat, defaultLng], { radius: defaultRadius, color: '#00288e', fillOpacity: 0.15 }).addTo(map);

                function updateInputs(latlng, zoom) {
                    if (latInput) latInput.value = latlng.lat.toFixed(7);
                    if (lngInput) lngInput.value = latlng.lng.toFixed(7);
                    marker.setLatLng(latlng);
                    circle.setLatLng(latlng);
                    map.setView(latlng, zoom ?? map.getZoom());
                }

                map.on('click', e => updateInputs(e.latlng));
                marker.on('dragend', e => updateInputs(e.target.getLatLng()));
                if (radiusInput) {
                    radiusInput.addEventListener('input', () => {
                        circle.setRadius(parseInt(radiusInput.value || 50, 10));
                    });
                }
                if (btnLokasi) {
                    btnLokasi.addEventListener('click', () => {
                        if (!navigator.geolocation) {
                            alert('Browser tidak mendukung geolocation');

                            return;
                        }
                        btnLokasi.disabled = true;
                        navigator.geolocation.getCurrentPosition(pos => {
                            updateInputs({ lat: pos.coords.latitude, lng: pos.coords.longitude }, 17);
                            btnLokasi.disabled = false;
                        }, err => {
                            alert('Gagal ambil lokasi: ' + err.message + ' — izinkan lokasi browser.');
                            btnLokasi.disabled = false;
                        }, { enableHighAccuracy: true, timeout: 10000 });
                    });
                }
                // Sync initial if empty
                if (!latInput?.value) {
                    updateInputs({ lat: defaultLat, lng: defaultLng });
                }
            });
        }

        // ponytail: DOMContentLoaded sudah lewat saat Livewire navigate.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', start);
        } else {
            start();
        }
        document.addEventListener('livewire:navigated', start);
    })();
    </script>
    @endpush
</x-layouts::app>
