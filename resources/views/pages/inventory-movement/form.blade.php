<?php /** @var App\Models\InventoryMovement $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-select col="6" name="movement_id_inventory" :options="$inventoryOptions" />
                <x-input col="6" type="date" name="movement_tanggal" />
                <x-select col="6" name="movement_tipe" :options="$tipeOptions" />
                <x-select col="6" name="movement_uom" :options="$satuanOptions" />
                <x-input col="6" type="number" min="1" name="movement_qty" helper="Qty masuk/keluar (Keluar ditolak bila melebihi stok)" />
                <x-input col="6" type="number" step="0.01" min="0" name="movement_nominal" helper="Total nominal baris. Untuk Keluar boleh 0 (dinilai avg berjalan di kartu)" />
                <div class="col-span-12 md:col-span-6 -mt-2">
                    <span id="hargaAcuan" class="font-label-caps text-label-caps text-on-surface-variant"></span>
                </div>
                <x-textarea col="12" name="movement_keterangan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>

    <script>
        var HARGA_ACUAN = @json($hargaAcuan ?? []);
        function refreshHargaAcuan(autoFill) {
            var sel = document.querySelector('[name="movement_id_inventory"]');
            var qtyEl = document.querySelector('[name="movement_qty"]');
            var nomEl = document.querySelector('[name="movement_nominal"]');
            var tipeEl = document.querySelector('[name="movement_tipe"]');
            var hint = document.getElementById('hargaAcuan');
            if (!sel || !hint) return;
            var harga = parseFloat(HARGA_ACUAN[sel.value] ?? 0) || 0;
            hint.textContent = harga > 0 ? 'Harga acuan: Rp ' + harga.toLocaleString('id-ID') + ' / satuan' : '';
            if (autoFill && tipeEl && tipeEl.value === 'masuk' && harga > 0 && qtyEl && nomEl && !parseFloat(nomEl.value)) {
                var qty = parseInt(qtyEl.value, 10) || 0;
                if (qty > 0) nomEl.value = (qty * harga).toFixed(2);
            }
        }
        document.addEventListener('DOMContentLoaded', function () {
            ['movement_id_inventory', 'movement_tipe', 'movement_qty'].forEach(function (n) {
                var el = document.querySelector('[name="' + n + '"]');
                if (el) el.addEventListener('change', function () { refreshHargaAcuan(true); });
            });
            var qtyEl = document.querySelector('[name="movement_qty"]');
            if (qtyEl) qtyEl.addEventListener('input', function () { refreshHargaAcuan(true); });
            refreshHargaAcuan(false);
        });
    </script>

    @if (!empty($selectedInventory))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var sel = document.querySelector('[name="movement_id_inventory"]');
                if (sel) { sel.value = "{{ $selectedInventory->field_primary }}"; sel.dispatchEvent(new Event('change')); }
                var uom = document.querySelector('[name="movement_uom"]');
                if (uom && !uom.value) { uom.value = "{{ $selectedInventory->inventory_satuan }}"; }
            });
        </script>
    @endif
</x-layouts::app>
