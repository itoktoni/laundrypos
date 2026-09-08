<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $laundryId = session('laundry_id');
        $user = $request->user();
        $role = $user->role ?? '';
        $isStaff = $role === 'editor';
        $isUser = $role === 'user';
        $isAdmin = $role === 'admin';
        $isDeveloper = $role === 'developer';
        $isPrivileged = $isDeveloper; // hanya developer lihat keuangan & penggajian
        $isCashier = in_array($role, ['admin','editor','user'], true); // kasir/operasional
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

        // Jika staff, chart hanya order miliknya (bisa lihat & edit)
        $staffIdForChart = $isStaff ? $user->id : null;

        $pemasukanByMetode = Order::where('order_id_laundry', $laundryId)
            ->when($staffIdForChart, fn($q) => $q->where('order_id_user', $staffIdForChart))
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
            ->when($staffIdForChart, fn($q) => $q->where('order_id_user', $staffIdForChart))
            ->where('order_status_id', $selesaiId)
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, sum(order_total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyExpense = $isStaff
            ? collect() // staff: arus kas hanya pemasukan miliknya, expense = 0
            : Expense::where('expense_id_laundry', $laundryId)
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

        // ponytail: absensi — untuk staff/user biar tau sudah absen atau belum
        $todayAttendance = null;
        $recentAttendances = collect();
        $attendanceSummary = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
        if (in_array($role, ['editor', 'user'], true)) {
            $todayAttendance = StaffAttendance::withoutGlobalScopes()
                ->where('attendance_id_laundry', $laundryId)
                ->where('attendance_id_user', $user->id)
                ->where('attendance_tanggal', Carbon::today()->toDateString())
                ->first();
            $recentAttendances = StaffAttendance::withoutGlobalScopes()
                ->where('attendance_id_laundry', $laundryId)
                ->where('attendance_id_user', $user->id)
                ->whereBetween('attendance_tanggal', [$start, $end])
                ->orderByDesc('attendance_tanggal')
                ->limit(7)
                ->get();
            $counts = StaffAttendance::withoutGlobalScopes()
                ->where('attendance_id_laundry', $laundryId)
                ->where('attendance_id_user', $user->id)
                ->whereBetween('attendance_tanggal', [$start, $end])
                ->selectRaw('attendance_status, count(*) as c')
                ->groupBy('attendance_status')
                ->pluck('c', 'attendance_status');
            $attendanceSummary = [
                'hadir' => (int) ($counts['hadir'] ?? 0),
                'izin' => (int) ($counts['izin'] ?? 0),
                'sakit' => (int) ($counts['sakit'] ?? 0),
                'alpha' => 0,
            ];
        }

        // Staff-specific stats & charts (editor) — khusus order milik staff
        $staffStats = null;
        $staffRecentOrders = collect();
        $staffDailyOrders = collect();
        $staffStatusDist = collect();
        $topStaff = collect();
        // ponytail: dashboard untuk user (role=user) — hanya order + pendapatan, tanpa pengeluaran
        $userStats = null;
        $userRecentOrders = collect();
        $userDailyOrders = collect();
        $userStatusDist = collect();
        $staffTarget = (int) config('website.staff_target', 100);
        $staffFee = (int) config('website.staff_fee', 1000);
        if ($isStaff) {
            $uid = $user->id;
            $bulanIni = Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])->count();
            $lebih = max(0, $bulanIni - $staffTarget);
            $staffStats = [
                'hariIni' => Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)->whereDate('created_at', Carbon::today())->count(),
                'bulanIni' => $bulanIni,
                'total' => Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)->count(),
                'selesai' => Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)->where('order_status_id', $selesaiId)->count(),
                'pendapatan' => Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)->where('order_status_id', $selesaiId)->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])->sum('order_total'),
                'pending' => Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)->whereNotIn('order_status_id', [$selesaiId])->count(),
                'target' => $staffTarget,
                'fee' => $staffFee,
                'lebih' => $lebih,
                'bonus' => $lebih * $staffFee,
            ];
            $staffRecentOrders = Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)
                ->with(['hasCustomer', 'hasStatus'])->latest()->limit(8)->get();

            // Chart: order harian saya 7 hari terakhir
            $dailyMyOrders = Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)
                ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')->pluck('total', 'date');
            $staffDailyOrders = $dates->mapWithKeys(fn($d) => [$d => (int) $dailyMyOrders->get($d, 0)]);

            // Chart: distribusi status order saya periode ini
            $staffStatusDist = Order::where('order_id_laundry', $laundryId)->where('order_id_user', $uid)
                ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                ->with('hasStatus')
                ->get()->groupBy(fn($o) => $o->hasStatus?->order_status_nama ?? 'Tanpa Status')
                ->map->count();
        } elseif ($isUser) {
            // User: ringkas — order + pendapatan saja, tanpa pengeluaran/laba
            $userStats = [
                'hariIni' => Order::where('order_id_laundry', $laundryId)->whereDate('created_at', Carbon::today())->count(),
                'bulanIni' => Order::where('order_id_laundry', $laundryId)->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])->count(),
                'total' => Order::where('order_id_laundry', $laundryId)->count(),
                'selesai' => Order::where('order_id_laundry', $laundryId)->where('order_status_id', $selesaiId)->count(),
                'pendapatan' => (int) $pemasukan,
                'pending' => Order::where('order_id_laundry', $laundryId)->whereNotIn('order_status_id', [$selesaiId])->count(),
            ];
            $userRecentOrders = Order::where('order_id_laundry', $laundryId)->with(['hasCustomer', 'hasStatus'])->latest()->limit(8)->get();
            $dailyAll = Order::where('order_id_laundry', $laundryId)
                ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')->pluck('total', 'date');
            $userDailyOrders = $dates->mapWithKeys(fn($d) => [$d => (int) $dailyAll->get($d, 0)]);
            $userStatusDist = Order::where('order_id_laundry', $laundryId)
                ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                ->with('hasStatus')
                ->get()->groupBy(fn($o) => $o->hasStatus?->order_status_nama ?? 'Tanpa Status')
                ->map->count();
        } else {
            // Top staff leaderboard for admin/owner — hitung bonus per staff juga
            $topStaff = Order::where('order_id_laundry', $laundryId)
                ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                ->selectRaw('order_id_user, count(*) as total, sum(case when order_status_id = ? then 1 else 0 end) as selesai, sum(order_total) as omzet', [$selesaiId])
                ->groupBy('order_id_user')->orderByDesc('total')->limit(5)->get()
                ->map(function ($row) use ($staffTarget, $staffFee) {
                    $row->user = \App\Models\User::find($row->order_id_user);
                    $lebih = max(0, (int) $row->total - $staffTarget);
                    $row->lebih = $lebih;
                    $row->bonus = $lebih * $staffFee;
                    return $row;
                });
        }

        return view('dashboard', compact(
            'start', 'end', 'pemasukan', 'pengeluaran', 'labaRugi',
            'pemasukanByMetode', 'pengeluaranByKategori', 'cashFlow', 'recentExpenses',
            'isStaff', 'isUser', 'isAdmin', 'isDeveloper', 'isPrivileged', 'isCashier',
            'staffStats', 'staffRecentOrders', 'staffDailyOrders', 'staffStatusDist', 'topStaff',
            'userStats', 'userRecentOrders', 'userDailyOrders', 'userStatusDist',
            'todayAttendance', 'recentAttendances', 'attendanceSummary'
        ));
    }
}
