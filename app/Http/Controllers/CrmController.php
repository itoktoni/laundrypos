<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmController extends Controller
{
    public function __invoke(Request $request)
    {
        $vipThreshold = config('crm.vip_threshold');
        $churnDays = config('crm.churn_days');
        $newCustomerDays = config('crm.new_customer_days');

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();

        $totalCustomers = Customer::count();

        // VIP: customer dengan order >= threshold (all time)
        $vipCustomerIds = Order::select('order_id_customer')
            ->whereNotNull('order_id_customer')
            ->groupBy('order_id_customer')
            ->havingRaw('COUNT(*) >= ?', [$vipThreshold])
            ->pluck('order_id_customer');

        $vipCount = $vipCustomerIds->count();
        $vipPercentage = $totalCustomers > 0 ? round($vipCount / $totalCustomers * 100, 1) : 0;

        // Customer baru dalam periode
        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endDate])->count();

        // Pertumbuhan customer baru vs periode sebelumnya
        $periodLength = $startDate->diffInDays($endDate);
        $prevStartDate = $startDate->copy()->subDays($periodLength);
        $prevEndDate = $startDate->copy()->subDay()->endOfDay();
        $prevNewCustomers = Customer::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        $growthRate = $prevNewCustomers > 0 ? round(($newCustomers - $prevNewCustomers) / $prevNewCustomers * 100, 1) : ($newCustomers > 0 ? 100 : 0);

        // Churned: customer yang punya order terakhir > churn_days hari lalu
        $churnedThreshold = now()->subDays($churnDays);
        $allCustomerWithOrders = Order::whereNotNull('order_id_customer')
            ->select('order_id_customer', DB::raw('MAX(created_at) as last_order'))
            ->groupBy('order_id_customer')
            ->get();

        $churnedCount = $allCustomerWithOrders
            ->filter(fn ($row) => Carbon::parse($row->last_order)->lt($churnedThreshold))
            ->count();

        // Selesai tapi belum diambil
        $selesaiStatus = OrderStatus::where('order_status_nama', 'Selesai Dicuci')->first();
        $unpickedOrders = $selesaiStatus
            ? Order::where('order_status_id', $selesaiStatus->getKey())->count()
            : 0;

        $unpickedRevenue = $selesaiStatus
            ? (float) Order::where('order_status_id', $selesaiStatus->getKey())->sum('order_total')
            : 0;

        // Promo effectiveness dalam periode
        $ordersWithDiscount = Order::whereNotNull('order_id_discount')
            ->whereNotNull('order_id_customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('order_id_customer')
            ->distinct()
            ->pluck('order_id_customer');

        $promoReturnCount = 0;
        if ($ordersWithDiscount->isNotEmpty()) {
            $promoReturnCount = Order::whereIn('order_id_customer', $ordersWithDiscount)
                ->groupBy('order_id_customer')
                ->havingRaw('COUNT(*) >= 2')
                ->count('order_id_customer');
        }

        $promoUsageCount = $ordersWithDiscount->count();
        $promoReturnRate = $promoUsageCount > 0 ? round($promoReturnCount / $promoUsageCount * 100, 1) : 0;

        // Rata-rata order per customer dalam periode
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $activeCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('order_id_customer')
            ->distinct('order_id_customer')
            ->count('order_id_customer');
        $avgOrdersPerCustomer = $activeCustomers > 0 ? round($totalOrders / $activeCustomers, 1) : 0;

        // Top customers dalam periode
        $topCustomers = Customer::query()
            ->select('customer_id', 'customer_nama', 'customer_telepon')
            ->selectRaw('COUNT(order.order_id) as order_count')
            ->leftJoin('order', 'customer.customer_id', '=', 'order.order_id_customer')
            ->whereBetween('order.created_at', [$startDate, $endDate])
            ->groupBy('customer_id', 'customer_nama', 'customer_telepon')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();

        return view('pages.crm.dashboard', compact(
            'totalCustomers',
            'vipCount',
            'vipPercentage',
            'newCustomers',
            'churnedCount',
            'unpickedOrders',
            'unpickedRevenue',
            'promoReturnCount',
            'promoUsageCount',
            'promoReturnRate',
            'avgOrdersPerCustomer',
            'growthRate',
            'topCustomers',
            'vipThreshold',
            'churnDays',
            'startDate',
            'endDate',
        ));
    }
}
