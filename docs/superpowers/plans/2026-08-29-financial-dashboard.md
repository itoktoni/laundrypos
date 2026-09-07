# Financial Dashboard (Laba Rugi, Arus Kas, Pemasukan & Pengeluaran)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the existing generic `/dashboard` with a financial dashboard showing profit/loss, cash flow, income, and expenses.

**Architecture:** New `expense` table tracks all pengeluaran. Income is derived from `order.order_total` where status = Selesai. Profit = Income - Expenses. Cash flow tracks money in vs money out by period. All scoped to the active laundry via `BelongsToLaundry` trait. The existing `DashboardController` is modified to serve financial data instead of user counts.

**Tech Stack:** Laravel 13, Livewire, ApexCharts (already installed), Tailwind CSS, Flux UI

---

## File Structure

| Action | File |
|--------|------|
| Create | `database/migrations/2026_08_29_130000_create_expense_table.php` |
| Create | `app/Models/Expense.php` |
| Create | `app/Policies/ExpensePolicy.php` |
| Create | `app/Http/Controllers/ExpenseController.php` |
| Modify | `app/Http/Controllers/DashboardController.php` — replace user counts with financial data |
| Modify | `resources/views/dashboard.blade.php` — replace with financial dashboard view |
| Modify | `config/menu.php` — add Pengeluaran menu item |
| Modify | `routes/web.php` — add expense route |

---

## Task 1: Expense Migration + Model

**Files:**
- Create: `database/migrations/2026_08_29_130000_create_expense_table.php`
- Create: `app/Models/Expense.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense', function (Blueprint $table) {
            $table->id('expense_id');
            $table->foreignId('expense_id_laundry')->constrained('laundry', 'laundry_id')->cascadeOnDelete();
            $table->string('expense_nama', 255);
            $table->string('expense_kategori', 100)->comment('Listrik, Air, Gaji, Sewa, Perlengkapan, Operasional, Lainnya');
            $table->decimal('expense_nominal', 12, 2);
            $table->date('expense_tanggal');
            $table->string('expense_catatan', 500)->nullable();
            $table->string('expense_metode_pembayaran', 20)->default('tunai')->comment('tunai, transfer, dompet_digital');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense');
    }
};
```

- [ ] **Step 2: Run migration**

Run: `php artisan migrate`
Expected: Table `expense` created

- [ ] **Step 3: Create Expense model**

```php
<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use App\Concerns\BelongsToLaundry;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['expense_nama', 'expense_kategori', 'expense_nominal', 'expense_tanggal', 'expense_catatan', 'expense_metode_pembayaran'])]
class Expense extends BaseModel
{
    use BelongsToLaundry, Filterable, Sortable;

    protected $table = 'expense';
    protected $primaryKey = 'expense_id';

    public static $filterColumns = ['expense_nama', 'expense_kategori'];
    public static $sortColumns = ['expense_nama', 'expense_tanggal', 'expense_nominal'];

    public static function field_name(): string
    {
        return 'expense_nama';
    }

    public function rules(): array
    {
        return [
            'expense_nama' => 'required|string|max:255',
            'expense_kategori' => 'required|string|max:100',
            'expense_nominal' => 'required|numeric|min:0',
            'expense_tanggal' => 'required|date',
            'expense_catatan' => 'nullable|string|max:500',
            'expense_metode_pembayaran' => 'required|in:tunai,transfer,dompet_digital',
        ];
    }

    public static function kategoriOptions(): array
    {
        return [
            'Listrik' => 'Listrik',
            'Air' => 'Air',
            'Gaji' => 'Gaji',
            'Sewa' => 'Sewa',
            'Perlengkapan' => 'Perlengkapan',
            'Operasional' => 'Operasional',
            'Marketing' => 'Marketing',
            'Maintenance' => 'Maintenance',
            'Lainnya' => 'Lainnya',
        ];
    }
}
```

- [ ] **Step 4: Verify model works**

Run: `php artisan tinker --execute="echo App\Models\Expense::count();"`
Expected: 0 (no error)

---

## Task 2: Expense Policy + Controller

**Files:**
- Create: `app/Policies/ExpensePolicy.php`
- Create: `app/Http/Controllers/ExpenseController.php`

- [ ] **Step 1: Create ExpensePolicy**

```php
<?php

namespace App\Policies;

class ExpensePolicy extends BasePolicy {}
```

- [ ] **Step 2: Create ExpenseController**

```php
<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Expense;

class ExpenseController extends Controller
{
    use ControllerTrait;

    public function __construct(Expense $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'kategoriOptions' => Expense::kategoriOptions(),
            'metodeOptions' => [
                'tunai' => 'Tunai',
                'transfer' => 'Transfer',
                'dompet_digital' => 'Dompet Digital',
            ],
        ], $data);
    }
}
```

- [ ] **Step 3: Add route in routes/web.php**

Add inside the auth middleware group:

```php
Route::auto('/expense', 'ExpenseController', ['name' => 'expense']);
```

- [ ] **Step 4: Add menu entry in config/menu.php**

Add "Pengeluaran" item under the existing "Laundry" section:

```php
// Inside 'Laundry' items array, add:
['label' => 'Pengeluaran', 'route' => 'expense.table', 'icon' => 'receipt_long'],
```

---

## Task 3: Expense Views (Table + Form)

**Files:**
- Create: `resources/views/pages/expense/table.blade.php`
- Create: `resources/views/pages/expense/form.blade.php`

- [ ] **Step 1: Create table view**

```blade
<x-layouts::app>
    <x-breadcrumb :items="[['url' => '', 'label' => moduleLabel()]]" />

    <x-card :label="moduleLabel()">
        <x-slot:actions>
            <a href="{{ moduleRoute('getCreate') }}" class="btn btn-sm btn-primary">
                <span class="material-symbols-outlined text-sm">add</span> Tambah Pengeluaran
            </a>
        </x-slot:actions>

        <x-table>
            <x-table-header :columns="['Nama', 'Kategori', 'Nominal', 'Tanggal', 'Metode', 'Aksi']" />
            <tbody>
                @forelse($data as $item)
                    <tr>
                        <td>{{ $item->expense_nama }}</td>
                        <td>
                            <span class="badge badge-soft badge-info">{{ $item->expense_kategori }}</span>
                        </td>
                        <td class="text-right font-mono">{{ formatAngka($item->expense_nominal, 'Rp ') }}</td>
                        <td>{{ formatDate($item->expense_tanggal) }}</td>
                        <td>{{ ucfirst($item->expense_metode_pembayaran) }}</td>
                        <td>
                            <x-table-action :model="$item" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-400 py-8">Belum ada pengeluaran</td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        <x-pagination :data="$data" />
    </x-card>
</x-layouts::app>
```

- [ ] **Step 2: Create form view**

```blade
<x-layouts::app>
    <x-breadcrumb :items="[
        ['url' => moduleRoute('getTable'), 'label' => moduleLabel()],
        ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create'],
    ]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)
                <x-input col="6" name="expense_nama" label="Nama Pengeluaran" />
                <x-select col="6" name="expense_kategori" :options="$kategoriOptions" label="Kategori" />
                <x-input col="6" name="expense_nominal" type="number" label="Nominal (Rp)" />
                <x-input col="6" name="expense_tanggal" type="date" label="Tanggal" />
                <x-select col="6" name="expense_metode_pembayaran" :options="$metodeOptions" label="Metode Pembayaran" />
                <x-textarea col="12" name="expense_catatan" label="Catatan" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
```

---

## Task 4: Modify DashboardController

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `resources/views/dashboard.blade.php`

- [ ] **Step 1: Read existing DashboardController**

Read: `app/Http/Controllers/DashboardController.php`
Currently returns user counts and notification data.

- [ ] **Step 2: Replace with financial data**

```php
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

        // Status Selesai = terminal status (is_selesai = true)
        $selesaiId = OrderStatus::where('order_status_id_laundry', $laundryId)
            ->where('order_status_is_selesai', true)
            ->value('order_status_id');

        // Pemasukan (income) = order_total where status = Selesai
        $pemasukan = Order::where('order_id_laundry', $laundryId)
            ->where('order_status_id', $selesaiId)
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->sum('order_total');

        // Pengeluaran (expenses)
        $pengeluaran = Expense::where('expense_id_laundry', $laundryId)
            ->whereBetween('expense_tanggal', [$start, $end])
            ->sum('expense_nominal');

        // Laba Rugi
        $labaRugi = $pemasukan - $pengeluaran;

        // Pemasukan by metode pembayaran
        $pemasukanByMetode = Order::where('order_id_laundry', $laundryId)
            ->where('order_status_id', $selesaiId)
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw('order_metode_pembayaran, sum(order_total) as total')
            ->groupBy('order_metode_pembayaran')
            ->pluck('total', 'order_metode_pembayaran');

        // Pengeluaran by kategori
        $pengeluaranByKategori = Expense::where('expense_id_laundry', $laundryId)
            ->whereBetween('expense_tanggal', [$start, $end])
            ->selectRaw('expense_kategori, sum(expense_nominal) as total')
            ->groupBy('expense_kategori')
            ->pluck('total', 'expense_kategori');

        // Daily cash flow (pemasukan - pengeluaran per day)
        $dailyIncome = Order::where('order_id_laundry', $laundryId)
            ->where('order_status_id', $selesaiId)
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, sum(order_total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyExpense = Expense::where('expense_id_laundry', $laundryId)
            ->whereBetween('expense_tanggal', [$start, $end])
            ->selectRaw('expense_tanggal as date, sum(expense_nominal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Merge into daily cash flow
        $dates = collect(range(
            Carbon::parse($start)->diffInDays(Carbon::parse($end))
        ))->map(fn($i) => Carbon::parse($start)->addDays($i)->toDateString());

        $cashFlow = $dates->mapWithKeys(function ($date) use ($dailyIncome, $dailyExpense) {
            return [$date => [
                'income' => $dailyIncome->get($date, 0),
                'expense' => $dailyExpense->get($date, 0),
                'net' => ($dailyIncome->get($date, 0)) - ($dailyExpense->get($date, 0)),
            ]];
        });

        // Recent expenses
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
```

- [ ] **Step 3: Verify route works**

Run: `php artisan route:list --name=dashboard`
Expected: Dashboard route exists at `/dashboard`

---

## Task 5: Replace Dashboard View

**Files:**
- Modify: `resources/views/dashboard.blade.php` — replace entire content

- [ ] **Step 1: Replace dashboard view content**

Replace the entire `resources/views/dashboard.blade.php` with:

```blade
<x-layouts::app>
    <x-breadcrumb :items="[['url' => '', 'label' => 'Dashboard']]" />

    {{-- Filter tanggal --}}
    <form method="GET" class="mb-6">
        <div class="flex items-end gap-3 flex-wrap">
            <div class="form-control">
                <label class="label"><span class="label-text">Dari</span></label>
                <input type="date" name="start_date" value="{{ $start }}" class="input input-bordered input-sm" />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Sampai</span></label>
                <input type="date" name="end_date" value="{{ $end }}" class="input input-bordered input-sm" />
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    {{-- Ringkasan Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-success/10 border border-success/20">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-success text-3xl">trending_up</span>
                    <div>
                        <p class="text-sm text-gray-500">Pemasukan</p>
                        <p class="text-xl font-bold text-success">{{ formatAngka($pemasukan, 'Rp ') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-error/10 border border-error/20">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-error text-3xl">trending_down</span>
                    <div>
                        <p class="text-sm text-gray-500">Pengeluaran</p>
                        <p class="text-xl font-bold text-error">{{ formatAngka($pengeluaran, 'Rp ') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card {{ $labaRugi >= 0 ? 'bg-primary/10 border-primary/20' : 'bg-warning/10 border-warning/20' }}">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined {{ $labaRugi >= 0 ? 'text-primary' : 'text-warning' }} text-3xl">account_balance</span>
                    <div>
                        <p class="text-sm text-gray-500">{{ $labaRugi >= 0 ? 'Laba' : 'Rugi' }}</p>
                        <p class="text-xl font-bold {{ $labaRugi >= 0 ? 'text-primary' : 'text-warning' }}">{{ formatAngka(abs($labaRugi), 'Rp ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Cash Flow Chart --}}
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-sm">Arus Kas Harian</h3>
                <div id="cashFlowChart" class="w-full h-64"></div>
            </div>
        </div>

        {{-- Pengeluaran by Kategori --}}
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-sm">Pengeluaran per Kategori</h3>
                <div id="expenseCategoryChart" class="w-full h-64"></div>
            </div>
        </div>
    </div>

    {{-- Pemasukan by Metode + Recent Expenses --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pemasukan by Metode --}}
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-sm">Pemasukan per Metode Bayar</h3>
                <div id="incomeMethodChart" class="w-full h-64"></div>
            </div>
        </div>

        {{-- Recent Expenses --}}
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-sm">Pengeluaran Terakhir</h3>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th class="text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExpenses as $exp)
                                <tr>
                                    <td>{{ formatDate($exp->expense_tanggal) }}</td>
                                    <td>{{ $exp->expense_nama }}</td>
                                    <td><span class="badge badge-soft badge-info badge-sm">{{ $exp->expense_kategori }}</span></td>
                                    <td class="text-right font-mono text-sm">{{ formatAngka($exp->expense_nominal, 'Rp ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-gray-400">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Cash Flow Bar Chart
        const cashFlowData = @json($cashFlow);
        const cashFlowDates = Object.keys(cashFlowData);
        const cashFlowIncome = cashFlowDates.map(d => cashFlowData[d].income);
        const cashFlowExpense = cashFlowDates.map(d => cashFlowData[d].expense);
        const cashFlowNet = cashFlowDates.map(d => cashFlowData[d].net);

        new ApexCharts(document.querySelector('#cashFlowChart'), {
            chart: { type: 'bar', height: 256, toolbar: { show: false } },
            series: [
                { name: 'Pemasukan', data: cashFlowIncome },
                { name: 'Pengeluaran', data: cashFlowExpense },
                { name: 'Bersih', data: cashFlowNet, type: 'line' }
            ],
            xaxis: { categories: cashFlowDates.map(d => d.substring(8)), labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { formatter: v => 'Rp ' + v.toLocaleString('id-ID') } },
            colors: ['#22c55e', '#ef4444', '#3b82f6'],
            plotOptions: { bar: { columnWidth: '60%' } },
            stroke: { width: [0, 0, 2] },
            tooltip: { y: { formatter: v => 'Rp ' + v.toLocaleString('id-ID') } }
        }).render();

        // Expense Category Donut
        const expenseCatData = @json($pengeluaranByKategori);
        new ApexCharts(document.querySelector('#expenseCategoryChart'), {
            chart: { type: 'donut', height: 256 },
            series: Object.values(expenseCatData),
            labels: Object.keys(expenseCatData),
            colors: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#8b5cf6', '#ec4899', '#6b7280', '#14b8a6'],
            plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', formatter: () => 'Rp ' + Object.values(expenseCatData).reduce((a,b) => a+b, 0).toLocaleString('id-ID') } } } } }
        }).render();

        // Income by Method Donut
        const incomeMethodData = @json($pemasukanByMetode);
        const methodLabels = { tunai: 'Tunai', transfer: 'Transfer', dompet_digital: 'Dompet Digital' };
        new ApexCharts(document.querySelector('#incomeMethodChart'), {
            chart: { type: 'donut', height: 256 },
            series: Object.values(incomeMethodData),
            labels: Object.keys(incomeMethodData).map(k => methodLabels[k] || k),
            colors: ['#22c55e', '#3b82f6', '#8b5cf6'],
            plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', formatter: () => 'Rp ' + Object.values(incomeMethodData).reduce((a,b) => a+b, 0).toLocaleString('id-ID') } } } } }
        }).render();
    </script>
    @endpush
</x-layouts::app>
```

---

## Task 6: Verification

- [ ] **Step 1: Run migration**

Run: `php artisan migrate`
Expected: `expense` table created

- [ ] **Step 2: Run tests**

Run: `php artisan test`
Expected: All existing tests pass

- [ ] **Step 3: Run Pint**

Run: `./vendor/bin/pint`
Expected: No new violations

- [ ] **Step 4: Manual check**

- Navigate to `/dashboard` → financial dashboard loads with date filter
- Navigate to `/expense` → expense table loads
- Create expense → appears in table and dashboard
- Filter dates → charts and totals update

---

## Summary

| Feature | Source |
|---------|--------|
| **Pemasukan** | `order.order_total` where status = Selesai |
| **Pengeluaran** | `expense` table (new) |
| **Laba Rugi** | Pemasukan - Pengeluaran |
| **Arus Kas** | Daily income vs expense (bar + line chart) |
| **Pengeluaran per Kategori** | Donut chart |
| **Pemasukan per Metode** | Donut chart |
| **Recent Expenses** | Table with last 10 |
