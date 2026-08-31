<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $laundryId = session('laundry_id');
        $start = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $selesaiId = OrderStatus::where('order_status_id_laundry', $laundryId)
            ->where('order_status_is_selesai', true)
            ->value('order_status_id');

        $pemasukan = Order::where('order_id_laundry', $laundryId)
            ->where('order_status_id', $selesaiId)
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->sum('order_total');

        $pengeluaran = Expense::where('expense_id_laundry', $laundryId)
            ->whereBetween('expense_tanggal', [$start, $end])
            ->sum('expense_nominal');

        $labaRugi = $pemasukan - $pengeluaran;

        $pemasukanByMetode = Order::where('order_id_laundry', $laundryId)
            ->where('order_status_id', $selesaiId)
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw('order_metode_pembayaran, sum(order_total) as total')
            ->groupBy('order_metode_pembayaran')
            ->pluck('total', 'order_metode_pembayaran');

        $pengeluaranByKategori = Expense::where('expense_id_laundry', $laundryId)
            ->whereBetween('expense_tanggal', [$start, $end])
            ->selectRaw('expense_kategori, sum(expense_nominal) as total')
            ->groupBy('expense_kategori')
            ->pluck('total', 'expense_kategori');

        $dailyIncome = Order::where('order_id_laundry', $laundryId)
            ->where('order_status_id', $selesaiId)
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, sum(order_total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyExpense = Expense::where('expense_id_laundry', $laundryId)
            ->where('expense_tanggal', '>=', Carbon::now()->subDays(6)->toDateString())
            ->selectRaw('expense_tanggal as date, sum(expense_nominal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dates = collect(range(0, 6))->map(fn ($i) => Carbon::now()->subDays(6 - $i)->toDateString());

        $cashFlow = $dates->mapWithKeys(fn ($date) => [$date => [
            'income' => $dailyIncome->get($date, 0),
            'expense' => $dailyExpense->get($date, 0),
            'net' => ($dailyIncome->get($date, 0)) - ($dailyExpense->get($date, 0)),
        ]]);

        $recentExpenses = Expense::where('expense_id_laundry', $laundryId)
            ->whereBetween('expense_tanggal', [$start, $end])
            ->latest('expense_tanggal')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'start', 'end', 'pemasukan', 'pengeluaran', 'labaRugi',
            'pemasukanByMetode', 'pengeluaranByKategori', 'cashFlow', 'recentExpenses'
        ));
    }
}
