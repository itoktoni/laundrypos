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

    protected function getData()
    {
        return $this->model->with(['hasItems', 'hasStatus'])->orderByDesc('created_at');
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
