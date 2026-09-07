# Laundry POS + Master Data Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Multi-tenant laundry POS — master data CRUDs (customer, kategori, product), POS order flow with per-laundry custom status flow, receipt printing in 3 modes.

**Architecture:** Single DB multi-tenancy via `laundry_id` global scope keyed to `session('laundry_id')`. Masters follow the existing ControllerTrait/Route::auto/Blade-components pattern. Order creation runs through a Laravel Action with price snapshots; status flow is data-driven (`order_status` table) validated by `urutan`.

**Tech Stack:** Laravel 13, Livewire 4, BenSampo Enum, laravel-actions, dompdf, Pest.

**Spec:** `docs/superpowers/specs/2026-08-22-laundry-pos-design.md`

## Global Constraints

- PHP ^8.3, 4-space indent, double quotes, declare return types.
- Column naming: `{module}_{field}`; FK to laundry = `{table}_id_laundry`; PK = `{table}_id`.
- Models extend `App\Models\BaseModel`, use `#[Fillable]`, define `$filterColumns`, `$sortColumns`, `field_name()`, `rules()`, relations prefixed `has`.
- Every model gets `{Model}Entity` property trait in `app/Properties/` and a Policy extending `BasePolicy` in `app/Policies/` (empty body).
- Standard CRUD uses `Route::auto()` only; manual routes for struk/picker endpoints.
- All lint must pass: `composer lint:check`. Full gate: `composer test`.
- Permissions deferred: leave `config/permision.php` empty (allow-all).

---

### Task 1: Laundry tenancy foundation

**Files:**
- Create: `database/migrations/2026_08_22_000001_create_laundries_tables.php`
- Create: `app/Models/Laundry.php`
- Create: `app/Properties/LaundryEntity.php`
- Create: `app/Policies/LaundryPolicy.php`
- Create: `app/Concerns/BelongsToLaundry.php`
- Test: `tests/Feature/TenancyTest.php`

**Interfaces:**
- Produces: trait `BelongsToLaundry` (global scope on `session('laundry_id')`, auto-set on creating, relation `hasLaundry()`); model `Laundry` with PK `laundry_id`.

- [ ] **Step 1: Write failing test**

```php
<?php

namespace Tests\Feature;

use App\Concerns\BelongsToLaundry;
use App\Models\BaseModel;
use App\Models\Laundry;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TenantDummy extends BaseModel
{
    use BelongsToLaundry;

    protected $table = "tenant_dummies";
    protected $primaryKey = "tenant_dummy_id";

    #[\Illuminate\Database\Eloquent\Attributes\Fillable(["tenant_dummy_nama"])]
    protected $guarded = [];
}

it("scopes and auto-assigns laundry_id from session", function () {
    Schema::dropIfExists("tenant_dummies");
    Schema::create("tenant_dummies", function (Blueprint $table) {
        $table->id("tenant_dummy_id");
        $table->unsignedBigInteger("tenant_dummies_id_laundry")->nullable();
        $table->string("tenant_dummy_nama");
    });

    $a = Laundry::create(["laundry_nama" => "A", "laundry_kode" => "A"]);
    $b = Laundry::create(["laundry_nama" => "B", "laundry_kode" => "B"]);

    $this->withSession(["laundry_id" => $a->laundry_id])->session(["laundry_id" => $a->laundry_id]);
    session(["laundry_id" => $a->laundry_id]);
    TenantDummy::create(["tenant_dummy_nama" => "in-a"]);

    session(["laundry_id" => $b->laundry_id]);
    TenantDummy::create(["tenant_dummy_nama" => "in-b"]);

    expect(TenantDummy::count())->toBe(1)
        ->and(TenantDummy::first()->tenant_dummy_nama)->toBe("in-b")
        ->and(TenantDummy::withoutGlobalScopes()->count())->toBe(2);
});
```

Note: `Laundry::create` works before Task's migration? No — run migration first; test order: create migration (Step 3) then re-run test. Keep Step 1 failing on missing class `Laundry`.

- [ ] **Step 2: Run test, expect FAIL** — `php artisan test tests/Feature/TenancyTest.php` → error: class Laundry not found.

- [ ] **Step 3: Implement migration, model, entity, policy, concern**

Migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("laundry", function (Blueprint $table) {
            $table->id("laundry_id");
            $table->string("laundry_nama", 100);
            $table->string("laundry_kode", 20)->unique();
            $table->string("laundry_alamat", 255)->nullable();
            $table->string("laundry_telepon", 15)->nullable();
            $table->boolean("laundry_is_aktif")->default(true);
            $table->timestamps();
        });

        Schema::create("laundry_user", function (Blueprint $table) {
            $table->id();
            $table->foreignId("laundry_id")->constrained("laundry", "laundry_id")->cascadeOnDelete();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(["laundry_id", "user_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("laundry_user");
        Schema::dropIfExists("laundry");
    }
};
```

`app/Models/Laundry.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\DB;

#[Fillable(["laundry_nama", "laundry_kode", "laundry_alamat", "laundry_telepon", "laundry_is_aktif"])]
class Laundry extends BaseModel
{
    protected $table = "laundry";
    protected $primaryKey = "laundry_id";

    public static $filterColumns = ["laundry_nama"];
    public static $sortColumns = ["laundry_nama"];

    public static function field_name(): string
    {
        return "laundry_nama";
    }

    public function rules(): array
    {
        return [
            "laundry_nama" => ["required", "string", "max:100"],
            "laundry_kode" => ["required", "string", "max:20", "unique:laundry,laundry_kode"],
        ];
    }

    public function hasUsers()
    {
        return $this->belongsToMany(User::class, "laundry_user", "laundry_id", "id", "laundry_id", "id");
    }

    public static function booted(): void
    {
        // Seed default status flow for each new laundry
        static::created(function (self $laundry): void {
            $defaults = [
                ["Menunggu Konfirmasi", false, false],
                ["Diterima", false, false],
                ["Dalam Proses", false, false],
                ["Selesai Dicuci", false, false],
                ["Siap Diambil", false, false],
                ["Selesai", false, true],
                ["Dibatalkan", true, false],
            ];
            foreach ($defaults as $i => [$nama, $batal, $selesai]) {
                DB::table("order_status")->insert([
                    "order_status_id_laundry" => $laundry->laundry_id,
                    "order_status_nama" => $nama,
                    "order_status_urutan" => $i + 1,
                    "order_status_is_batal" => $batal,
                    "order_status_is_selesai" => $selesai,
                    "order_status_warna" => $batal ? "#dc2626" : "#2563eb",
                ]);
            }
        });
    }
}
```

NOTE: The `booted()` hook requires the `order_status` table from Task 7. To keep Task 1 green, guard the insert: wrap loop body in `if (Schema::hasTable("order_status")) { ... }`. Remove nothing later — the guard stays harmless.

`app/Concerns/BelongsToLaundry.php`:

```php
<?php

namespace App\Concerns;

use App\Models\Laundry;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToLaundry
{
    public static function bootBelongsToLaundry(): void
    {
        static::addGlobalScope("laundry", function (Builder $builder): void {
            $laundryId = session("laundry_id");
            if ($laundryId) {
                $builder->where($builder->getModel()->getTable()."_id_laundry", $laundryId);
            }
        });

        static::creating(function ($model): void {
            $column = $model->getTable()."_id_laundry";
            if (! $model->{$column} && session("laundry_id")) {
                $model->{$column} = session("laundry_id");
            }
        });
    }

    public function hasLaundry()
    {
        return $this->belongsTo(Laundry::class, $this->getTable()."_id_laundry", "laundry_id");
    }
}
```

`app/Properties/LaundryEntity.php`:

```php
<?php

namespace App\Properties;

trait LaundryEntity
{
    public static function field_nama() { return "laundry_nama"; }
    public static function field_kode() { return "laundry_kode"; }
    public function getFieldNamaAttribute() { return $this->{static::field_nama()}; }
}
```

`app/Policies/LaundryPolicy.php`: `class LaundryPolicy extends BasePolicy {}` (with namespace imports matching `UserPolicy.php`).

Fix the test file: dummy column name must match trait derivation — table `tenant_dummies` → column `tenant_dummies_id_laundry` (already correct above). Simplify session usage: replace first two session lines with just `session(["laundry_id" => $a->laundry_id]);`.

- [ ] **Step 4: Run test, expect PASS** — `php artisan test tests/Feature/TenancyTest.php`
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: laundry tenant foundation with global scope"`

---

### Task 2: Middleware EnsureLaundrySelected + picker

**Files:**
- Create: `app/Http/Middleware/EnsureLaundrySelected.php`
- Modify: `bootstrap/app.php` (register alias)
- Modify: `routes/web.php`
- Create: `resources/views/pages/laundry/picker.blade.php`
- Create: `app/Http/Controllers/LaundryController.php`
- Test: extend `tests/Feature/TenancyTest.php`

**Interfaces:**
- Consumes: `Laundry`, `laundry_user` pivot from Task 1.
- Produces: middleware alias `laundry.selected`; routes `laundry.picker` (GET), `laundry.select` (POST).

- [ ] **Step 1: Failing test** (append):

```php
use App\Models\User;
use Illuminate\Support\Facades\Auth;

it("redirects to picker when no laundry selected", function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get("/dashboard")
        ->assertRedirect(route("laundry.picker"));
});

it("allows dashboard when laundry in session and user is owner", function () {
    $laundry = Laundry::create(["laundry_nama" => "L1", "laundry_kode" => "L1"]);
    $user = User::factory()->create(["role" => "owner"]);
    Auth::login($user);
    $this->withSession(["laundry_id" => $laundry->laundry_id])
        ->get("/dashboard")->assertOk();
});

it("rejects non-member picking unassigned laundry", function () {
    $laundry = Laundry::create(["laundry_nama" => "L2", "laundry_kode" => "L2"]);
    $user = User::factory()->create(["role" => "karyawan"]);
    Auth::login($user);
    $this->withSession(["laundry_id" => $laundry->laundry_id])
        ->get("/dashboard")->assertRedirect(route("laundry.picker"));
});
```

If `/dashboard` requires extra setup (verified middleware etc.), use a plain probe route instead: register `GET /tenancy-probe` returning `"ok"` inside the same auth+selected group and assert against it. Adjust assertions accordingly.

- [ ] **Step 2: Run, expect FAIL** (route `laundry.picker` missing).

- [ ] **Step 3: Implement**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureLaundrySelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $laundryId = session("laundry_id");
        $user = $request->user();

        if ($user && ($user->role ?? "") === "owner") {
            if (! $laundryId || ! \App\Models\Laundry::find($laundryId)) {
                return redirect()->route("laundry.picker");
            }
            return $next($request);
        }

        if (! $laundryId || ! $user || ! DB::table("laundry_user")
            ->where("user_id", $user->id)->where("laundry_id", $laundryId)->exists()) {
            session()->forget("laundry_id");
            return redirect()->route("laundry.picker");
        }

        return $next($request);
    }
}
```

Register alias in `bootstrap/app.php` inside the existing `->withMiddleware(...)` chain: `->alias(["laundry.selected" => \App\Http\Middleware\EnsureLaundrySelected::class])` (append to whatever exists; if none, add `withMiddleware(function ($middleware) { $middleware->alias([...]); })`).

`app/Http/Controllers/LaundryController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaundryController extends Controller
{
    public function picker(Request $request)
    {
        $role = Auth::user()->role ?? "";
        $items = $role === "owner"
            ? Laundry::where("laundry_is_aktif", true)->get()
            : Laundry::whereIn("laundry_id",
                DB_table_pivot_ids())->get();

        return view("pages.laundry.picker", ["laundries" => $items]);
    }

    public function select(Request $request)
    {
        $validated = $request->validate([
            "laundry_id" => ["required", "integer"],
        ]);

        abort_unless(Laundry::find($validated["laundry_id"]), 404);
        session(["laundry_id" => (int) $validated["laundry_id"]]);

        return redirect()->route("dashboard");
    }
}

// helper defined inline below class in same file:
if (! function_exists("DB_table_pivot_ids")) {
    function DB_table_pivot_ids(): array
    {
        return \Illuminate\Support\Facades\DB::table("laundry_user")
            ->where("user_id", Auth::id())->pluck("laundry_id")->all();
    }
}
```

ponytail: free functions inside a controller file are ugly — prefer moving `pivotIds()` into the controller as a private method. Do that: replace helper call with `$this->pivotIds()`.

Routes (inside the existing `Route::middleware(['auth', 'verified', 'access'])` group, BEFORE the group add these two outside any group since picker needs no laundry):

```php
Route::middleware("auth")->group(function () {
    Route::get("/laundry/picker", [LaundryController::class, "picker"])->name("laundry.picker");
    Route::post("/laundry/select", [LaundryController::class, "select"])->name("laundry.select");
});
```

Then append `'laundry.selected'` into the existing auth group middleware array.

View `pages/laundry/picker.blade.php`:

```blade
<x-layouts::app>
    <x-breadcrumb :items="[['url' => '', 'label' => 'Pilih Laundry']]" />
    <div class="content mt-4">
        <x-card label="Pilih Laundry">
            <form method="POST" action="{{ route('laundry.select') }}">
                @csrf
                <x-select col="12" name="laundry_id" :options="$laundries->pluck('laundry_nama', 'laundry_id')->all()" />
                <button class="btn btn-primary mt-3">Masuk</button>
            </form>
        </x-card>
    </div>
</x-layouts::app>
```

- [ ] **Step 4: Run tests PASS**, `composer lint:check` fix issues.
- [ ] **Step 5: Commit** — `git commit -m "feat: laundry selection middleware and picker"`

---

### Task 3: Enums

**Files:**
- Create: `app/Enums/SatuanEnum.php`, `app/Enums/MetodePengambilanEnum.php`, `app/Enums/MetodePembayaranEnum.php`

**Produces:** each has constants + `getDescription` via match + usable via `getOptions()` from `EnumTrait`. Pattern copy of AGENTS.md StatusEnum.

- [ ] **Step 1:** Write the three enums following `StatusEnum` exactly:

SatuanEnum: `KG='kg'; ITEM='item'; PASANG='pasang';` descriptions "Per Kg","Per Item","Per Pasang".
MetodePengambilanEnum: `ANTAR_TOKO='antar_toko'; JEMPUT='jemput';` descriptions "Antar ke Toko","Dijemput".
MetodePembayaranEnum: `TUNAI='tunai'; TRANSFER='transfer'; DOMPET_DIGITAL='dompet_digital';` descriptions "Tunai","Transfer","Dompet Digital".

- [ ] **Step 2:** Verify via tinker: `php artisan tinker --execute="dump(App\Enums\SatuanEnum::getOptions());"` shows select array.
- [ ] **Step 3: Commit** — `git commit -m "feat: order-related enums"`

---

### Task 4–6: Master CRUDs (Kategori, Customer, Product)

Three identical-shape vertical slices. One task each; pattern identical — write out fully for Kategori, then mirror exactly (repeat code, do not reference) for Customer and Product with their own columns/rules.

**Shared shape per module M ∈ {kategori, customer, product}:**
- Migration `database/migrations/2026_08_22_0000{2..4}_create_{M}.php`
- Model `app/Models/{Name}.php` using `BelongsToLaundry`
- Entity `app/Properties/{Name}Entity.php`
- Policy `app/Policies/{Name}Policy.php` extends BasePolicy
- Controller `app/Http/Controllers/{Name}Controller.php` (ControllerTrait)
- Views `resources/views/pages/{m}/{table,form}.blade.php` (copy structure of `pages/users/table.blade.php` + `form.blade.php` verbatim, swapping fields)
- Route line `Route::auto('/{m}', '{Name}Controller', ['name' => '{m}']);` inside auth+selected group
- Feature test asserting: create scoped to current laundry; cross-laundry record invisible; uniqueness rules.

**Kategori columns:** `kategori_id` PK, `kategori_id_laundry`, `kategori_nama`(100), `kategori_deskripsi`(500 nullable), `kategori_is_aktif` bool default 1, timestamps.
Unique per laundry case-insensitive: rule `"unique:kategori,kategori_nama"` plus closure rule checking `Kategori::whereRaw("LOWER(kategori_nama) = ?", [strtolower($v)])->where("kategori_id", "!=", $modelId ?? 0)->exists()`.

rules():
```php
return [
    "kategori_nama" => ["required", "string", "max:100"],
    "kategori_deskripsi" => ["nullable", "string", "max:500"],
    "kategori_is_aktif" => ["nullable", "boolean"],
];
```

Controller share adds nothing special. Table view columns: kategori_nama, kategori_is_aktif.

**Customer columns:** `customer_id` PK, `customer_id_laundry`, `customer_nama`(100), `customer_telepon`(15 unique per laundry), `customer_email`(254 nullable email), `customer_alamat`(255 nullable).
Telepon validation: `["required", "regex:/^[0-9]{8,15}$/", Rule::unique("customer", "customer_telepon")->ignore($modelId ?? null)->where(fn ($q) => $q->where("customer_id_laundry", session("laundry_id")))]`.
Filter columns: nama, telepon, email.

**Product columns:** `product_id` PK, `product_id_laundry`, `product_id_kategori` FK, `product_nama`(100 unique per laundry+kategori), `product_satuan` (in SatuanEnum values), `product_harga_dasar` decimal(12,2) min 0.01, `product_estimasi_jam` int 1–720, `product_deskripsi`(500 nullable), `product_is_aktif` bool default 1.
Controller overrides `share()` adding `'kategoriOptions' => Kategori::orderBy('kategori_nama')->get()->pluck('kategori_nama', 'kategori_id')->all()` and casts satuan enum in form via `SatuanEnum::getOptions()`.

Each task steps:
1. Failing feature test (scoped create + invisible cross-tenant + duplicate rejected):
```php
it("creates kategori scoped to active laundry", function () {
    $l1 = Laundry::create(["laundry_nama" => "A", "laundry_kode" => "A"]);
    $l2 = Laundry::create(["laundry_nama" => "B", "laundry_kode" => "B"]);
    session(["laundry_id" => $l1->laundry_id]);
    $k = Kategori::create(["kategori_nama" => "Pakaian"]);
    session(["laundry_id" => $l2->laundry_id]);
    expect(Kategori::count())->toBe(0)
        ->and(Kategori::withoutGlobalScopes()->find($k->kategori_id)->kategori_nama)->toBe("Pakaian");
});
```
2. Run FAIL → 3. migrate/model/controller/views/routes → 4. Run PASS + HTTP smoke: acting user GET `/kategori` 200 → 5. `composer lint:check` → 6. commit `feat: kategori master` / `feat: customer master` / `feat: product master`.

---

### Task 7: OrderStatus model

**Files:**
- Create: `database/migrations/2026_08_22_000005_create_order_status.php`
- Create: `app/Models/OrderStatus.php`, `app/Properties/OrderStatusEntity.php`, `app/Policies/OrderStatusPolicy.php`
- Test: `tests/Feature/OrderStatusTest.php`

**Produces:** model with PK `order_status_id`; accessors `is_batal`, `is_selesai` (bools); scope ordered by `order_status_urutan`.

- [ ] **Step 1: Failing test** — default statuses seeded by `Laundry::create` (from Task 1 hook): assert new laundry has 7 statuses, last-but-one `is_selesai` true, last `is_batal` true, urutan 1..7 ascending.
- [ ] **Step 2: Run FAIL** (missing table/class) → **Step 3: implement**:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(["order_status_id_laundry", "order_status_nama", "order_status_urutan", "order_status_is_batal", "order_status_is_selesai", "order_status_warna"])]
class OrderStatus extends BaseModel
{
    use \App\Concerns\BelongsToLaundry;

    protected $table = "order_status";
    protected $primaryKey = "order_status_id";

    protected function casts(): array
    {
        return [
            "order_status_is_batal" => "boolean",
            "order_status_is_selesai" => "boolean",
        ];
    }

    public static $sortColumns = ["order_status_urutan"];
    public static function field_name(): string { return "order_status_nama"; }

    public function rules(): array
    {
        return [
            "order_status_nama" => ["required", "string", "max:50"],
            "order_status_urutan" => ["required", "integer", "min:1"],
            "order_status_warna" => ["nullable", "string", "max:7"],
        ];
    }
}
```

Migration mirrors spec §2 columns. NOTE: remove the temporary `Schema::hasTable` guard note from Task 1 only if desired — guard can stay harmlessly; leave it.
- [ ] **Step 4: PASS** → **Step 5: Commit** `feat: customizable order status model`

---

### Task 8: Order tables + models + code generator

**Files:**
- Create: `database/migrations/2026_08_22_000006_create_order_tables.php`
- Create: `app/Models/{Order,OrderItem,OrderStatusLog}.php` + entities + policies ×3
- Test: `tests/Feature/OrderCodeTest.php`

**Produces:** `Order::generateCode(): string` → `LDY-YYYYMMDD-XXXX` daily-reset counter, collision-retried; `Order` casts + `hasItems()`, `hasCustomer()`, `hasUser()`, `hasStatus()`, `hasStatusLogs()`.

Migration columns exactly per spec §2 (`order`, `order_item`, `order_status_log`). FKs: `order_item_id_order` → order cascade; log FKs likewise.

Code generator:

```php
public static function generateCode(): string
{
    $prefix = "LDY-".now()->format("Ymd")."-";

    return DB::transaction(function () use ($prefix) {
        $last = self::query()->lockForUpdate()
            ->where("order_code", "like", $prefix."%")
            ->max("order_code");

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, "0", STR_PAD_LEFT);
    });
}
```

Wrap caller with retry on QueryException unique violation (max 3 attempts, bump suffix) — put retry inside `CreateOrderAction`.

- [ ] Steps: failing test (format matches regex `/^LDY-\d{8}-\d{4}$/`, increments across creates, resets next day via `Carbon::setTestNow`) → implement models/migration → PASS → lint → commit `feat: order models and code generator`.

---

### Task 9: CreateOrderAction

**Files:**
- Create: `app/Actions/CreateOrderAction.php`
- Test: `tests/Feature/CreateOrderTest.php`

**Interfaces:**
- Produces: `CreateOrderAction::run(array $data): Order` where `$data` keys: `customer_id|null`, `walkin_nama|walkin_telepon|null`, `save_walkin_customer(bool)`, `metode_pengambilan`, `alamat_jemput|null`, `slot_waktu|null`, `metode_pembayaran`, `catatan|null`, `items` = `[[product_id, qty], ...]`.

Validation logic (throws ValidationException):
- items min 1; each qty int 1..999; products must exist, be active, belong to current laundry.
- jemput ⇒ alamat_jemput + slot_waktu required (alamat max 255); walk-in (no customer_id) ⇒ walkin_nama + walkin_telepon required.

handle():
1. DB transaction (+ retry loop around generateCode unique clash).
2. If save_walkin_customer: `Customer::create([customer_nama, customer_telepon])` and set customer_id.
3. Snapshot items: nama/satuan/harga from product rows; subtotal = harga×qty.
4. `order_estimasi_selesai = now()->addHours(sum estimasi_jam)`.
5. Initial status = `OrderStatus::orderBy(order_status_urutan)->first()`; insert order with `order_code`, `order_subtotal`, `order_total` = subtotal (min 0.01), `order_status_id`.
6. Insert `order_status_log` row (from null, to initial, user_id = Auth::id()).

Tests: walk-in ok & save-as-customer creates row; registered path; jemput missing address rejected; inactive product rejected; snapshot prices survive later product price change; estimation equals sum; code format; rollback leaves zero orders when forced failure (e.g., invalid status absence).

Steps: failing tests → action → PASS → lint → commit `feat: create-order action`.

---

### Task 10: transitStatus + timeline

**Files:**
- Modify: `app/Models/Order.php` (add method)
- Test: `tests/Feature/OrderTransitionTest.php`

Method:

```php
public function transitStatus(OrderStatus $to, ?string $keterangan = null): void
{
    $current = $this->hasStatus;

    if ($current && $current->order_status_is_selesai) {
        throw ValidationException::withMessages(["status" => ["Order sudah selesai, tidak dapat diubah."]]);
    }

    if ($to->order_status_is_batal) {
        if (! $keterangan || trim($keterangan) === "") {
            throw ValidationException::withMessages(["keterangan" => ["Alasan pembatalan wajib diisi."]]);
        }
    } else {
        $allowed = OrderStatus::orderBy("order_status_urutan")
            ->get()->values();
        $currentIndex = $allowed->search(fn ($s) => $s->getKey() === $current?->getKey());
        $targetIndex = $allowed->search(fn ($s) => $s->getKey() === $to->getKey());
        if ($currentIndex === false || $targetIndex !== $currentIndex + 1) {
            throw ValidationException::withMessages([
                "status" => ["Perpindahan status tidak valid. Status berikutnya yang diizinkan: ".($allowed[$currentIndex + 1]->order_status_nama ?? "-")],
            ]);
        }
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($to, $keterangan) {
        OrderStatusLog::create([
            "order_status_log_id_order" => $this->getKey(),
            "order_status_log_id_from" => $this->order_status_id,
            "order_status_log_id_to" => $to->getKey(),
            "order_status_log_id_user" => auth()->id(),
            "order_status_log_keterangan" => $to->order_status_is_batal ? $keterangan : null,
        ]);
        $this->update(["order_status_id" => $to->getKey()]);
    });
}
```

Tests: forward step OK writes log; skip/backward rejected with allowed-status message; cancel without keterangan rejected; cancel with keterangan OK from mid-flow; terminal Selesai locked.
Steps: fail → implement → pass → lint → commit `feat: order status transitions with timeline`.

---

### Task 11: POS Livewire component

**Files:**
- Create: `app/Livewire/Pos/PosTerminal.php`
- Create: `resources/views/livewire/pos/pos-terminal.blade.php`
- Create: `resources/views/pages/order/pos.blade.php` (wrapper: layouts::app + breadcrumb + `<livewire:pos.pos-terminal />`)
- Modify: `routes/web.php` — `Route::livewire('/order/pos', ...)` OR simple controller-less route rendering wrapper: `Route::view('/pos', 'pages.order.pos')->name('pos.index');`
- Test: `tests/Feature/PosComponentTest.php` (Livewire::test)

Component essentials:

```php
<?php

namespace App\Livewire\Pos;

use App\Actions\CreateOrderAction;
use App\Models\Kategori;
use App\Models\OrderStatus;
use App\Models\Product;
use Livewire\Component;

class PosTerminal extends Component
{
    public string $search = "";
    public ?int $activeKategoriId = null;
    public array $cart = []; // lines: product_id, nama, satuan, harga, estimasi_jam, qty
    public $customerId = null;
    public string $walkinNama = "";
    public string $walkinTelepon = "";
    public bool $saveWalkinAsCustomer = false;
    public string $metodePengambilan = "antar_toko";
    public string $alamatJemput = "";
    public $slotWaktu = null;
    public string $metodePembayaran = "tunai";
    public string $catatan = "";

    public function addToCart(int $productId): void
    {
        $p = Product::where("product_is_aktif", true)->findOrFail($productId);
        foreach ($this->cart as $i => $line) {
            if ($line["product_id"] === $productId) {
                $this->cart[$i]["qty"] = min(999, $line["qty"] + 1);
                return;
            }
        }
        $this->cart[] = [
            "product_id" => $p->getKey(),
            "nama" => $p->product_nama,
            "satuan" => $p->product_satuan,
            "harga" => (float) $p->product_harga_dasar,
            "estimasi_jam" => (int) $p->product_estimasi_jam,
            "qty" => 1,
        ];
    }

    public function bumpQty(int $index, int $delta): void
    {
        $this->cart[$index]["qty"] = max(1, min(999, $this->cart[$index]["qty"] + $delta));
    }

    public function removeLine(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($l) => $l["harga"] * $l["qty"]);
    }

    public function confirmOrder()
    {
        try {
            $order = CreateOrderAction::run([
                "customer_id" => $this->customerId ?: null,
                "walkin_nama" => $this->customerId ? null : $this->walkinNama,
                "walkin_telepon" => $this->customerId ? null : $this->walkinTelepon,
                "save_walkin_customer" => ! $this->customerId && $this->saveWalkinAsCustomer,
                "metode_pengambilan" => $this->metodePengambilan,
                "alamat_jemput" => $this->metodePengambilan === "jemput" ? $this->alamatJemput : null,
                "slot_waktu" => $this->metodePengambilan === "jemput" ? $this->slotWaktu : null,
                "metode_pembayaran" => $this->metodePembayaran,
                "catatan" => $this->catatan ?: null,
                "items" => collect($this->cart)->map(fn ($l) => ["product_id" => $l["product_id"], "qty" => $l["qty"]])->all(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch("pos-error", errors: $e->errors());
            return;
        }

        $this->reset("cart", "customerId", "walkinNama", "walkinTelepon", "saveWalkinAsCustomer", "alamatJemput", "slotWaktu", "catatan");

        return redirect()->route("order.show", ["id" => $order->getKey()]);
    }

    public function render()
    {
        $products = Product::where("product_is_aktif", true)
            ->when($this->activeKategoriId, fn ($q) => $q->where("product_id_kategori", $this->activeKategoriId))
            ->when($this->search !== "", fn ($q) => $q->where("product_nama", "like", "%".$this->search."%"))
            ->orderBy("product_nama")->get();

        return view("livewire.pos.pos-terminal", [
            "kategoris" => Kategori::where("kategori_is_aktif", true)->orderBy("kategori_nama")->get(),
            "products" => $products,
        ]);
    }
}
```

View: two-panel grid (Tailwind): left kategori tabs + product cards (`wire:click="addToCart({{ $p->product_id }})"`); right cart lines with `wire:click="bumpQty({{$i}},1)"` etc., totals `{{ formatAngka($this->subtotal) }}`, radios for metode fields bound `wire:model`, walk-in toggle, confirm button. Validation error bag rendered near fields.

Tests (Livewire::test(PosTerminal::class)): addToCart builds line & merges duplicates; bumpQty clamps 1..999; confirmOrder redirects on success; jemput without address surfaces errors.

Steps: failing tests → implement → pass → lint → commit `feat: POS livewire terminal`.

---

### Task 12: Order show page + struk printing (3 modes)

**Files:**
- Create: `app/Http/Controllers/OrderController.php` (ControllerTrait; override `getShow`; add `getStrukPdf`, `getPrint`)
- Create: `resources/views/pages/order/table.blade.php`, `show.blade.php`
- Create: `resources/views/pdf/struk.blade.php` (shared partial: header order_code/datetime/customer/items/total/estimasi hari)
- Create: `resources/views/pages/order/print.blade.php` (browser mode: print CSS + `window.print()`; thermal mode: `@if($mode==='thermal')` fixed 80mm monospace layout `@endif`)
- Modify: `routes/web.php`:

```php
Route::auto("/order", "OrderController", ["name" => "order"]);
Route::get("/order/{id}/struk-pdf", [OrderController::class, "getStrukPdf"])->name("order.strukpdf");
Route::get("/order/{id}/print", [OrderController::class, "getPrint"])->name("order.print");
```

OrderController:

```php
<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ControllerTrait;

    public function __construct(Order $model)
    {
        $this->model = $model::getModel();
    }

    protected function getData()
    {
        return $this->model->with(["hasItems", "hasStatus"])->orderByDesc("created_at");
    }

    public function getShow(Request $request, $id)
    {
        $order = $this->model->with(["hasItems", "hasStatus", "hasStatusLogs.hasUser"])->findOrFail($id);

        return $this->views("pages.order.show", ["order" => $order]);
    }

    public function getStrukPdf(Request $request, $id)
    {
        $order = $this->model->with(["hasItems", "hasStatus"])->findOrFail($id);

        return Pdf::loadView("pdf.struk", ["order" => $order])
            ->setPaper("a5")->download($order->order_code.".pdf");
    }

    public function getPrint(Request $request, $id)
    {
        $mode = $request->input("mode", "browser"); // browser|thermal
        $order = $this->model->with(["hasItems", "hasStatus"])->findOrFail($id);

        return view("pages.order.print", ["order" => $order, "mode" => $mode]);
    }
}
```

show.blade.php: card with order info + items table + timeline (loop `order->has_status_logs` showing created_at, from→to names, user, keterangan) + status action button posting next-status transition (simple form POST to be wired in a later build or a small Livewire-free endpoint: add minimal `postTransit` route+method now — include in this task: `Route::post("/order/{id}/transit", ...)` validating `order_status_id` + optional `keterangan` and calling `transitStatus`). Print buttons link to the three modes.

Estimasi hari display: `ceil($order->order_estimasi_selesai->diffInHours($order->created_at) / 24)` hari.

Tests: show page 200 contains order_code; pdf route returns application/pdf; print browser/thermal 200; cross-tenant order id → 404.

Steps: failing tests → views/controller/routes → PASS → lint → commit `feat: order detail and struk printing`.

---

### Task 13: Menu entries + final gate

**Files:**
- Modify: `config/menu.php`

Sidebar additions:

```php
[
    'label' => 'Laundry',
    'items' => [
        ['route' => 'pos.index', 'icon' => 'point_of_sale', 'label' => 'POS Kasir'],
        ['route' => 'order.getTable', 'icon' => 'receipt_long', 'label' => 'Orders', 'match' => ['order.*']],
        ['route' => 'customer.getTable', 'icon' => 'people', 'label' => 'Customers', 'match' => ['customer.*']],
        ['route' => 'kategori.getTable', 'icon' => 'category', 'label' => 'Kategori', 'match' => ['kategori.*']],
        ['route' => 'product.getTable', 'icon' => 'local_laundry_service', 'label' => 'Products', 'match' => ['product.*']],
    ],
],
```

- [ ] Step 1: edit config → Step 2: `php artisan test` full suite green → `composer lint:check` green → Step 3: commit `feat: laundry menus`.

## Self-Review Notes

- Spec coverage: tenancy (T1–2), masters (T4–6), custom status (T1 hook + T7), order/code/action (T8–9), transitions/timeline (T10), POS (T11), print ×3 (T12), menu (T13). Promo/CRM/finance/inventory/machine/FAQ/dashboard intentionally excluded (later sub-projects).
- Type consistency: `CreateOrderAction::run(array): Order` used by PosTerminal; `transitStatus(OrderStatus, ?string)` used by postTransit; session key `laundry_id` everywhere.
- Known risk: `BasePolicy` allow-all while `config/permision.php` empty — acceptable per deferred permissions decision.
