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
            <p class="text-xs font-semibold text-on-surface mb-2 flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">map</span> Pilih di peta (opsional)</p>
            <div id="laundry-map" class="w-full h-64 rounded-xl border border-outline-variant"></div>
            <p class="text-xs text-on-surface-variant mt-2">Klik peta untuk isi latitude/longitude. Radius lingkaran sesuai input.</p>
        </div>

        <x-action :model="$model" :action="['save']" />
    </x-form>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const latInput = document.querySelector('input[name="laundry_latitude"]');
            const lngInput = document.querySelector('input[name="laundry_longitude"]');
            const radiusInput = document.querySelector('input[name="laundry_radius_m"]');
            const defaultLat = parseFloat(latInput?.value) || -6.2000000;
            const defaultLng = parseFloat(lngInput?.value) || 106.8166660;
            const defaultRadius = parseInt(radiusInput?.value || 50, 10);

            const map = L.map('laundry-map').setView([defaultLat, defaultLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
            let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
            let circle = L.circle([defaultLat, defaultLng], { radius: defaultRadius, color: '#00288e', fillOpacity: 0.15 }).addTo(map);

            function updateInputs(latlng) {
                if (latInput) latInput.value = latlng.lat.toFixed(7);
                if (lngInput) lngInput.value = latlng.lng.toFixed(7);
                marker.setLatLng(latlng);
                circle.setLatLng(latlng);
            }

            map.on('click', e => updateInputs(e.latlng));
            marker.on('dragend', e => updateInputs(e.target.getLatLng()));
            if (radiusInput) {
                radiusInput.addEventListener('input', () => {
                    const r = parseInt(radiusInput.value || 50, 10);
                    circle.setRadius(r);
                });
            }
            // Sync initial if empty
            if (!latInput.value) updateInputs({ lat: defaultLat, lng: defaultLng });
            else { marker.setLatLng([parseFloat(latInput.value), parseFloat(lngInput.value)]); circle.setLatLng([parseFloat(latInput.value), parseFloat(lngInput.value)]); }
        });
    </script>
    @endpush
</x-layouts::app>
