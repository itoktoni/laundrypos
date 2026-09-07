<?php

namespace App\Http\Controllers;

use App\Actions\CreateAction;
use App\Concerns\ControllerTrait;
use App\Enums\InventoryTipeEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    use ControllerTrait;

    public function __construct(Inventory $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'satuanOptions' => Inventory::satuanOptions(),
        ], $data);
    }

    protected function getData()
    {
        // ponytail: agregat ledger via subquery agar list tidak N+1;
        // filter()->sort() tetap jalan di atas query dasar.
        $query = $this->model->query()
            ->select('inventory.*')
            ->selectSub(
                InventoryMovement::selectRaw("COALESCE(SUM(CASE WHEN movement_tipe = 'masuk' THEN movement_qty ELSE -movement_qty END), 0)")
                    ->whereColumn('movement_id_inventory', 'inventory.inventory_id'),
                'ledger_stok'
            )
            ->selectSub(
                InventoryMovement::selectRaw('COALESCE(SUM(movement_nominal), 0)')
                    ->where('movement_tipe', 'masuk')
                    ->whereColumn('movement_id_inventory', 'inventory.inventory_id'),
                'ledger_nominal_masuk'
            )
            ->selectSub(
                InventoryMovement::selectRaw('COALESCE(SUM(movement_qty), 0)')
                    ->where('movement_tipe', 'masuk')
                    ->whereColumn('movement_id_inventory', 'inventory.inventory_id'),
                'ledger_qty_masuk'
            );

        return $query->filter()->sort();
    }

    // ponytail: stok awal di form create langsung menjadi movement Masuk
    // pertama (qty x harga) agar kartu stok & avg terisi sejak awal.
    public function postCreate(GeneralRequest $request)
    {
        $stokAwal = (int) $request->input('stok_awal', 0);

        $response = DB::transaction(function () use ($request, $stokAwal) {
            $created = CreateAction::run($request, $this->model);
            if (! $created['status']) {
                return $created;
            }

            /** @var Inventory $inventory */
            $inventory = $created['data'];
            $harga = (float) ($inventory->inventory_harga ?? 0);

            if ($stokAwal > 0) {
                $inventory->hasMovements()->create([
                    'movement_tanggal' => now()->toDateString(),
                    'movement_tipe' => InventoryTipeEnum::MASUK,
                    'movement_uom' => $inventory->inventory_satuan,
                    'movement_qty' => $stokAwal,
                    'movement_nominal' => round($stokAwal * $harga, 2),
                    'movement_keterangan' => 'Stok awal',
                ]);
            }

            return $created;
        });

        return $this->response($response);
    }

    // Kartu stok: contoh HTML user — 3 kartu ringkasan + tabel riwayat
    // dengan running saldo & moving average per baris.
    public function getKartuStok(GeneralRequest $request, $id)
    {
        $inventory = $this->model->findOrFail($id);

        $movements = InventoryMovement::where('movement_id_inventory', $inventory->inventory_id)
            ->orderBy('movement_tanggal')
            ->orderBy('movement_id')
            ->get();

        $rows = [];
        $saldo = 0;
        $nilai = 0; // total nilai berjalan (moving average method)
        $avg = 0;

        foreach ($movements as $index => $move) {
            $qty = (int) $move->movement_qty;
            $nominal = (float) $move->movement_nominal;

            if ($move->movement_tipe === 'masuk') {
                $saldo += $qty;
                $nilai += $nominal;
            } else {
                // Keluar dinilai dengan avg berjalan (tidak mengubah avg)
                $saldo -= $qty;
                $nilai -= $qty * $avg;
            }

            $avg = $saldo > 0 ? round($nilai / $saldo, 2) : 0;
            // Amankan floating drift saat stok nol
            if ($saldo <= 0) {
                $nilai = 0;
                $avg = $saldo < 0 ? 0 : $avg;
            }

            $rows[] = [
                'no' => $index + 1,
                'tanggal' => $move->movement_tanggal,
                'uom' => $move->movement_uom,
                'tipe' => $move->movement_tipe,
                'qty' => $move->movement_tipe === 'masuk' ? $qty : -$qty,
                'nominal' => $nominal,
                'rata_rata' => $avg,
                'saldo' => $saldo,
            ];
        }

        // Tampilkan terbaru dulu seperti contoh (no 4 di atas)
        $rows = array_reverse($rows);

        $summary = [
            'stok' => $saldo,
            'satuan' => $inventory->inventory_satuan,
            'rata_rata' => $avg,
            'total' => round($saldo * $avg, 2),
        ];

        return $this->views($this->template('kartu'), [
            'model' => $inventory,
            'rows' => $rows,
            'summary' => $summary,
        ]);
    }

    // getShow dibajak ke kartu stok agar tombol Show lama tetap mendarat benar.
    public function getShow(GeneralRequest $request, $id)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->payload(TOAST_SUCCESS, $this->model->findOrFail($id));
        }

        return $this->getKartuStok($request, $id);
    }
}
