<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Order;
use App\Models\OrderStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    use ControllerTrait;

    public function __construct(Order $model)
    {
        $this->model = $model::getModel();
    }

    protected function getFields()
    {
        // ponytail: override — sediakan "Pelanggan" virtual yang cari di customer + walk-in
        return [
            'order_code' => 'Kode',
            'pelanggan' => 'Pelanggan',
            'hasCustomer.customer_nama' => 'Pelanggan (Member)',
            'order_walkin_nama' => 'Walk-in',
        ];
    }

    protected function getData()
    {
        $query = $this->model->with(['hasItems', 'hasStatus', 'hasCustomer'])->orderByDesc('order.created_at');
        // Staff/user hanya bisa lihat order miliknya — admin/developer lihat semua cabang
        if (in_array(auth()->user()->role ?? '', ['editor', 'user'], true)) {
            $query->where('order.order_id_user', auth()->id());
        }

        $request = request();
        $needsCustomerJoin = false;

        // Deteksi butuh join customer: filter pelanggan atau hasCustomer atau sort
        $filters = $request->input('filters', []);
        $q = $request->input('_q');
        $field = $request->input('_field');
        if (($q && in_array($field, ['pelanggan', 'hasCustomer.customer_nama'], true)) || isset($filters['pelanggan']) || isset($filters['hasCustomer.customer_nama'])) {
            $needsCustomerJoin = true;
        }
        if ($needsCustomerJoin) {
            $query->leftJoin('customer', 'customer.customer_id', '=', 'order.order_id_customer')
                ->select('order.*');
        }

        // Legacy _q + _field search — handle pelanggan virtual (OR customer + walk-in)
        if ($q && $field) {
            if ($field === 'pelanggan') {
                $like = '%'.strtolower($q).'%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(customer.customer_nama) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(order.order_walkin_nama) LIKE ?', [$like]);
                });
            } elseif ($field === 'hasCustomer.customer_nama') {
                // Biarkan ControllerTrait style — sudah di-join di atas, tinggal filter manual agar tidak double
                // Skip di sini, biar loop filters di bawah yang handle, tapi untuk _q kita handle langsung
                $query->whereRaw('LOWER(customer.customer_nama) LIKE ?', ['%'.strtolower($q).'%']);
            } elseif (in_array($field, ['order_code', 'order_walkin_nama'], true)) {
                $query->whereRaw('LOWER(order.'.$field.') LIKE ?', ['%'.strtolower($q).'%']);
            }
            // kembalikan tanpa jalankan generic trait filter lagi untuk _q
            return $query;
        }

        // Advanced filters: filters[field][operator] — handle pelanggan virtual + delegasi lain ke logic trait
        if (! empty($filters)) {
            $allowed = array_keys($this->getFields());
            foreach ($filters as $fField => $conditions) {
                if (! in_array($fField, $allowed, true)) {
                    continue;
                }
                if ($fField === 'pelanggan') {
                    if (is_array($conditions)) {
                        foreach ($conditions as $op => $val) {
                            if ($val === '' || $val === null) continue;
                            $like = '%'.strtolower($val).'%';
                            if ($op === '$contains' || $op === '$eq' && false) {
                                $query->where(function ($w) use ($like) {
                                    $w->whereRaw('LOWER(customer.customer_nama) LIKE ?', [$like])
                                        ->orWhereRaw('LOWER(order.order_walkin_nama) LIKE ?', [$like]);
                                });
                            } elseif ($op === '$eq') {
                                $query->where(function ($w) use ($val) {
                                    $w->whereRaw('LOWER(customer.customer_nama) = ?', [strtolower($val)])
                                        ->orWhereRaw('LOWER(order.order_walkin_nama) = ?', [strtolower($val)]);
                                });
                            } else {
                                $query->where(function ($w) use ($like) {
                                    $w->whereRaw('LOWER(customer.customer_nama) LIKE ?', [$like])
                                        ->orWhereRaw('LOWER(order.order_walkin_nama) LIKE ?', [$like]);
                                });
                            }
                        }
                    } elseif (is_string($conditions) && $conditions !== '') {
                        $like = '%'.strtolower($conditions).'%';
                        $query->where(function ($w) use ($like) {
                            $w->whereRaw('LOWER(customer.customer_nama) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(order.order_walkin_nama) LIKE ?', [$like]);
                        });
                    }
                    continue;
                }
                // Untuk hasCustomer.customer_nama — sudah join
                if ($fField === 'hasCustomer.customer_nama') {
                    $col = 'customer.customer_nama';
                    if (is_array($conditions)) {
                        foreach ($conditions as $op => $val) {
                            if ($val === '' || $val === null) continue;
                            match ($op) {
                                '$contains' => $query->whereRaw('LOWER('.$col.') LIKE ?', ['%'.strtolower($val).'%']),
                                '$eq' => $query->whereRaw('LOWER('.$col.') = ?', [strtolower($val)]),
                                default => $query->whereRaw('LOWER('.$col.') LIKE ?', ['%'.strtolower($val).'%']),
                            };
                        }
                    } elseif (is_string($conditions) && $conditions !== '') {
                        $query->whereRaw('LOWER('.$col.') LIKE ?', ['%'.strtolower($conditions).'%']);
                    }
                    continue;
                }
                // order_code / order_walkin_nama — delegasi normal
                $col = 'order.'.$fField;
                if (is_array($conditions)) {
                    foreach ($conditions as $op => $val) {
                        if ($val === '' || $val === null) continue;
                        match ($op) {
                            '$contains' => $query->whereRaw('LOWER('.$col.') LIKE ?', ['%'.strtolower($val).'%']),
                            '$eq' => $query->whereRaw('LOWER('.$col.') = ?', [strtolower($val)]),
                            default => $query->whereRaw('LOWER('.$col.') LIKE ?', ['%'.strtolower($val).'%']),
                        };
                    }
                } elseif (is_string($conditions) && $conditions !== '') {
                    $query->whereRaw('LOWER('.$col.') LIKE ?', ['%'.strtolower($conditions).'%']);
                }
            }

            // Sort handling tetap jalan
            $sort = $request->input('sort.0');
            if ($sort) {
                $parts = explode(':', $sort);
                $col = $parts[0] ?? null;
                $dir = ($parts[1] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                if ($col && in_array($col, ['order_code', 'created_at'], true)) {
                    $query->orderBy('order.'.$col, $dir);
                }
            }

            return $query;
        }

        // Tidak ada filter khusus — tetap support sort default
        $sort = $request->input('sort.0');
        if ($sort) {
            $parts = explode(':', $sort);
            $col = $parts[0] ?? null;
            $dir = ($parts[1] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            if ($col && in_array($col, ['order_code', 'created_at'], true)) {
                $query->reorder()->orderBy('order.'.$col, $dir);
            }
        }

        return $query;
    }

    public function getShow(Request $request, $id)
    {
        $order = $this->model->with(['hasItems', 'hasStatus', 'hasStatusLogs.hasToStatus', 'hasStatusLogs.hasFromStatus', 'hasCustomer'])->findOrFail($id);

        return $this->views('pages.order.show', [
            'order' => $order,
        ]);
    }

    public function getCreate(Request $request)
    {
        // Orders are created through the POS terminal.
        return redirect()->route('pos.index');
    }

    public function getUpdate(Request $request, $id)
    {
        // Orders are not edited via CRUD form; the detail page holds actions.
        return redirect()->route('order.getShow', ['id' => $id]);
    }

    public function postTransit(Request $request, $id)
    {
        $order = $this->model->findOrFail($id);

        $validated = $request->validate([
            'order_status_id' => ['required', 'integer'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $status = OrderStatus::findOrFail($validated['order_status_id']);
            $order->transitStatus($status, $validated['keterangan'] ?? null);
            flash()->success('Status order diperbarui menjadi "'.$status->order_status_nama.'".');
        } catch (ValidationException $e) {
            flash()->error($e->errors()[array_key_first($e->errors())][0] ?? 'Perpindahan status tidak valid.');
        }

        return redirect()->back();
    }

    public function getStrukPdf(Request $request, $id)
    {
        $order = $this->model->with(['hasItems', 'hasStatus', 'hasCustomer'])->findOrFail($id);

        return Pdf::loadView('pdf.struk', ['order' => $order])
            ->setPaper('a5')
            ->download($order->order_code.'.pdf');
    }

    public function getPrint(Request $request, $id)
    {
        $mode = $request->input('mode', 'browser');
        abort_unless(in_array($mode, ['browser', 'thermal']), 404);

        $order = $this->model->with(['hasItems', 'hasStatus', 'hasCustomer'])->findOrFail($id);

        return view('pages.order.print', [
            'order' => $order,
            'mode' => $mode,
        ]);
    }
}
