<?php

namespace App\Http\Controllers;

use App\Actions\CreateAction;
use App\Concerns\ControllerTrait;
use App\Enums\InventoryTipeEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMovementController extends Controller
{
    use ControllerTrait;

    public function __construct(InventoryMovement $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $selectedId = request()->input('movement_id_inventory', request()->route('id'));

        return array_merge([
            'model' => $this->model,
            'inventoryOptions' => Inventory::orderBy('inventory_nama')->pluck('inventory_nama', 'inventory_id')->all(),
            'tipeOptions' => InventoryTipeEnum::getOptions(),
            'satuanOptions' => Inventory::satuanOptions(),
            'selectedInventory' => $selectedId ? Inventory::find($selectedId) : null,
            'hargaAcuan' => Inventory::pluck('inventory_harga', 'inventory_id')->all(),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->leftJoinRelationship('hasInventory')->filter()->sort()->latest('inventory_movement.movement_tanggal');
    }

    public function postCreate(GeneralRequest $request)
    {
        // Default tanggal hari ini bila kosong & default uom dari master.
        if (! $request->input('movement_tanggal')) {
            $request->merge(['movement_tanggal' => now()->toDateString()]);
        }
        if (! $request->input('movement_uom') && $request->input('movement_id_inventory')) {
            $inv = Inventory::find($request->input('movement_id_inventory'));
            if ($inv) {
                $request->merge(['movement_uom' => $inv->inventory_satuan]);
            }
        }

        // Guard stok-minus untuk tipe keluar di dalam transaction + lock.
        if ($request->input('movement_tipe') === InventoryTipeEnum::KELUAR) {
            $inventoryId = (int) $request->input('movement_id_inventory');
            $qty = (int) $request->input('movement_qty', 0);

            try {
                $response = DB::transaction(function () use ($request, $inventoryId, $qty) {
                    $stok = (int) InventoryMovement::where('movement_id_inventory', $inventoryId)
                        ->lockForUpdate()
                        ->selectRaw("COALESCE(SUM(CASE WHEN movement_tipe = 'masuk' THEN movement_qty ELSE -movement_qty END), 0) as stok")
                        ->value('stok');

                    if ($qty > $stok) {
                        throw ValidationException::withMessages([
                            'movement_qty' => "Stok tidak cukup (tersedia {$stok}, diminta {$qty}).",
                        ]);
                    }

                    return CreateAction::run($request, $this->model);
                });

                return $this->response($response);
            } catch (ValidationException $e) {
                throw $e;
            }
        }

        // Masuk: simpan lalu segarkan harga acuan master dari harga satuan baris.
        $response = DB::transaction(function () use ($request) {
            $created = CreateAction::run($request, $this->model);
            if ($created['status']) {
                /** @var InventoryMovement $move */
                $move = $created['data'];
                $qty = (int) ($move->movement_qty ?? 0);
                $nominal = (float) ($move->movement_nominal ?? 0);
                if ($qty > 0 && $nominal > 0) {
                    Inventory::where('inventory_id', $move->movement_id_inventory)
                        ->update(['inventory_harga' => round($nominal / $qty, 2)]);
                }
            }

            return $created;
        });

        return $this->response($response);
    }
}
