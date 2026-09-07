<?php

namespace App\Http\Controllers;

use App\Concerns\ReportTrait;
use App\Models\Order;
use App\Models\OrderStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportOrderController extends Controller
{
    use ReportTrait;

    protected function query(Request $request, string $dari, string $sampai)
    {
        $query = Order::with(['hasCustomer', 'hasStatus'])
            ->whereDate('created_at', '>=', $dari)
            ->whereDate('created_at', '<=', $sampai);

        // Staff (editor) hanya lihat order miliknya, konsisten dgn OrderController.
        if ((auth()->user()->role ?? '') === 'editor') {
            $query->where('order_id_user', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('order_status_id', $request->input('status'));
        }

        return $query->orderBy('created_at');
    }

    public function getIndex(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $orders = $this->query($request, $dari, $sampai)->get();

        return view('pages.report.order', [
            'orders' => $orders,
            'summary' => [
                'jumlah' => $orders->count(),
                'omzet' => (float) $orders->sum('order_total'),
                'rata_rata' => $orders->count() > 0 ? round((float) $orders->sum('order_total') / $orders->count(), 2) : 0,
            ],
            'dari' => $dari,
            'sampai' => $sampai,
            'statusOptions' => OrderStatus::pluck('order_status_nama', 'order_status_id'),
            'statusId' => $request->input('status', ''),
        ]);
    }

    public function getPdf(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        return Pdf::loadView('pdf.report-order', [
            'orders' => $this->query($request, $dari, $sampai)->get(),
            'dari' => $dari,
            'sampai' => $sampai,
        ])->setPaper('a4', 'landscape')->download("laporan-order-{$dari}-{$sampai}.pdf");
    }
}
