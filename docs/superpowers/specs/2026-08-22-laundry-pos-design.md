# Laundry POS + Master Data — Design Spec

**Date:** 2026-08-22
**Scope:** First sub-project of the Laundry Management System (from `requirements.md`): multi-tenant foundation, master data CRUDs (customer, kategori, product), POS order flow with custom status flow, and receipt printing (3 modes).
**Out of scope (later builds):** promo/diskon validation, CRM, finance module, inventory, machines, FAQ, dashboards/reports, notifications, customer self-order portal.

---

## 1. Multi-Tenancy Foundation

### Tenant table `laundry`

| Column | Type | Notes |
|---|---|---|
| laundry_id | bigint PK | |
| laundry_nama | string(100) | required |
| laundry_kode | string(20) | unique |
| laundry_alamat | string(255) | nullable |
| laundry_telepon | string(15) | nullable |
| laundry_is_aktif | boolean | default true |

### Pivot `laundry_user`
`id`, `laundry_id` FK, `user_id` FK, unique(laundry_id, user_id).

### Scoping mechanism
- Trait `App\Concerns\BelongsToLaundry`:
  - Global scope: `where({table}.laundry_id = session('laundry_id'))`.
  - Model creating hook: auto-set `laundry_id` from session.
- Middleware `EnsureLaundrySelected` (`app/Http/Middleware/`):
  - If session `laundry_id` empty → redirect to laundry picker page.
  - Validate the selected laundry exists in the user's pivot; Owner role bypasses pivot check (access to all laundries).
- Topbar switcher: select input listing allowed laundries; on change sets session and reloads.
- All tenant tables carry `*_id_laundry` FK. Uniqueness constraints are per-laundry where applicable (customer telepon, kategori nama).

---

## 2. Data Model

All tables use singular snake_case names with module-prefixed columns (per AGENTS.md WMS conventions). Every model extends `BaseModel`, uses Property traits in `app/Properties/{Model}Entity.php`, defines `$filterColumns`, `$sortColumns`, `field_name()`, `rules()`, and `has*`-prefixed relationships.

### `customer`
| Column | Type | Notes |
|---|---|---|
| customer_id | bigint PK | |
| customer_id_laundry | FK → laundry | |
| customer_nama | string(100) | required |
| customer_telepon | string(15) | required, unique per laundry, 8–15 digits |
| customer_email | string(254) | nullable, valid email |
| customer_alamat | string(255) | nullable |

### `kategori`
| Column | Type | Notes |
|---|---|---|
| kategori_id | bigint PK | |
| kategori_id_laundry | FK | |
| kategori_nama | string(100) | required, unique per laundry case-insensitive |
| kategori_deskripsi | string(500) | nullable |
| kategori_is_aktif | boolean | default true |

### `product`
| Column | Type | Notes |
|---|---|---|
| product_id | bigint PK | |
| product_id_laundry | FK | |
| product_id_kategori | FK → kategori | required |
| product_nama | string(100) | required, unique per (laundry, kategori) |
| product_satuan | enum(kg, item, pasang) | via `SatuanEnum` |
| product_harga_dasar | decimal(12,2) | required, min 0.01 |
| product_estimasi_jam | int | 1–720 |
| product_deskripsi | string(500) | nullable |
| product_is_aktif | boolean | default true |

### `order`
| Column | Type | Notes |
|---|---|---|
| order_id | bigint PK | |
| order_id_laundry | FK | |
| order_code | string(20) | unique, format `LDY-YYYYMMDD-XXXX` |
| order_id_customer | FK → customer | nullable (walk-in when null) |
| order_walkin_nama | string(100) | nullable |
| order_walkin_telepon | string(15) | nullable |
| order_id_user | FK → users | cashier/creator |
| order_metode_pengambilan | enum(antar_toko, jemput) | via `MetodePengambilanEnum` |
| order_alamat_jemput | string(255) | required if jemput |
| order_slot_waktu | datetime | required if jemput |
| order_metode_pembayaran | enum(tunai, transfer, dompet_digital) | via `MetodePembayaranEnum` |
| order_catatan | string(500) | nullable |
| order_subtotal | decimal(12,2) | sum of line items |
| order_total | decimal(12,2) | subtotal minus discount (discount = 0 this build), min 0.01 |
| order_status_id | FK → order_status | current status |
| order_estimasi_selesai | datetime | now + sum(product_estimasi_jam) |

Walk-in rule: when `order_id_customer` is null, `order_walkin_nama` + `order_walkin_telepon` are required. If cashier checks "simpan sebagai pelanggan", a `customer` row is created at confirm time and linked.

### `order_item`
| Column | Type | Notes |
|---|---|---|
| order_item_id | bigint PK | |
| order_item_id_order | FK → order | |
| order_item_id_product | FK → product | |
| order_item_nama_product | string(100) | snapshot at confirm |
| order_item_satuan | string(10) | snapshot |
| order_item_harga | decimal(12,2) | snapshot |
| order_item_qty | int | 1–999 |
| order_item_subtotal | decimal(12,2) | harga × qty |

### `order_status` (customizable status flow)
| Column | Type | Notes |
|---|---|---|
| order_status_id | bigint PK | |
| order_status_id_laundry | FK | |
| order_status_nama | string(50) | e.g. "Dalam Proses" |
| order_status_urutan | int | flow ordering |
| order_status_is_batal | boolean | cancellation terminal flag |
| order_status_is_selesai | boolean | success terminal flag |
| order_status_warna | string(7) | hex color for badges |

Seeded for each new laundry (urutan 1..7): Menunggu Konfirmasi, Diterima, Dalam Proses, Selesai Dicuci, Siap Diambil, Selesai (is_selesai), Dibatalkan (is_batal). Admin can add/rename/reorder statuses per laundry later — the transition rules only depend on `urutan` and terminal flags.

### `order_status_log`
| Column | Type | Notes |
|---|---|---|
| order_status_log_id | bigint PK | |
| order_status_log_id_order | FK → order | |
| order_status_log_id_from | FK → order_status | nullable (first entry) |
| order_status_log_id_to | FK → order_status | |
| order_status_log_id_user | FK → users | who changed |
| order_status_log_keterangan | string(255) | nullable; REQUIRED for cancellation |
| created_at | timestamp | transition time |

---

## 3. POS Order Flow

### Component
`App\Livewire\Pos\PosTerminal` + view `resources/views/livewire/pos/pos-terminal.blade.php`, rendered inside `<x-layouts::app>`.

Public state: `$search`, `$activeKategoriId`, `$cart` (array of lines: product_id, nama, satuan, harga, estimasi_jam, qty), `$customerId` / `$walkinNama` / `$walkinTelepon` / `$saveWalkinAsCustomer`, `$metodePengambilan`, `$alamatJemput`, `$slotWaktu`, `$metodePembayaran`, `$catatan`.

### Layout (two panels)
- **Left panel:** kategori tabs ("Semua" + active kategoris) → grid of active product cards (nama, harga, satuan) grouped by kategori, filtered by search. Click card = add/increment cart line.
- **Right panel (cart):**
  - Customer picker: Tom Select search over customers (nama/telepon) OR "Walk-in" toggle → walk-in nama+telepon fields + optional "simpan sebagai pelanggan" checkbox.
  - Cart lines with qty stepper (1–999) and remove.
  - Running total + estimated finish (now + sum of estimasi_jam).
  - Metode pengambilan radio (antar_toko / jemput); jemput → alamat + slot waktu required.
  - Metode pembayaran radio; catatan textarea.

### Confirmation
`confirmOrder()` → server-side validate → `App\Actions\CreateOrderAction::run()` inside a DB transaction:
1. Create `customer` row if walk-in + save-as-customer checked.
2. Generate `order_code`: daily counter query `count of today's orders + 1` inside lock, format `LDY-YYYYMMDD-XXXX`, retry on unique collision.
3. Insert order with price snapshots per item; compute subtotal, total, `order_estimasi_selesai`.
4. Insert initial `order_status_log` (status_from null).
5. Redirect to `pages/order/show` (struk page) with print buttons.

Validation errors render inline via Livewire; transaction rollback leaves no orphan rows.

### Status transitions
- Only forward, one step at a time: target `order_status_urutan` must be exactly current + 1 (Req 5.1 forbids skipping or going backward).
- Cancellation allowed from any non-terminal status → status with `is_batal = true`; requires `order_status_log_keterangan` (1–255 chars).
- Implemented as `Order::transitStatus(OrderStatus $to, ?string $keterangan)`: validates, writes log row, updates `order_status_id`. Invalid transitions throw ValidationException listing allowed statuses.
- Detail page shows chronological timeline from log + action buttons for the single valid next status and Batalkan.

---

## 4. Printing (3 modes)

Shared struk partial `resources/views/pdf/struk.blade.php` (order_code, confirmation datetime, customer data, items with qty/harga, total, estimation in days). Three outputs from the show page:

1. **PDF** — `OrderController::getStrukPdf($id)` → dompdf, A5.
2. **Browser print view** — `pages/order/print.blade.php`, print CSS + `window.print()`.
3. **Thermal receipt** — same view with `mode=thermal`: fixed 80mm width, monospace font.

Custom routes (edge-case pattern): `GET /order/{id}/struk-pdf`, `GET /order/{id}/print?mode=browser|thermal`.

---

## 5. Controllers / Routes / Views

Standard CRUD per AGENTS.md pattern:

| Controller | Route | Views |
|---|---|---|
| CustomerController | `Route::auto('/customer')` | pages/customer/{table,form} |
| KategoriController | `Route::auto('/kategori')` | pages/kategori/{table,form} |
| ProductController | `Route::auto('/product', share: kategoriOptions)` | pages/product/{table,form} |
| OrderController | `Route::auto('/order')` + custom struk routes | pages/order/{table,show} |

Policies (all extend `BasePolicy`): `LaundryPolicy`, `CustomerPolicy`, `KategoriPolicy`, `ProductPolicy`, `OrderPolicy`, `OrderStatusPolicy`. Registered in `config/menu.php`; permission entries deferred (auth-only for now, per user decision).

Enums: `SatuanEnum`, `MetodePengambilanEnum`, `MetodePembayaranEnum` (BenSampo + EnumTrait, `getOptions()` used in share()).

Migrations: one timestamped migration per table, following existing naming style.

Seeder: `LaundrySeeder` creates demo laundry + default status set + links admin user.

---

## 6. Error Handling

- Field-level validation from model `rules()`; inline errors in Livewire and Blade forms.
- Duplicate order_code collision: retry generation (bounded).
- Invalid status transition: ValidationException with message listing allowed next status(es).
- Cancel without keterangan: rejected inline.
- Transaction failure: full rollback, TOAST_FAILED toast, no partial data.
- No laundry in session: redirect to picker by middleware.

## 7. Testing (Pest)

- **CreateOrderAction:** walk-in vs registered customer; save-walkin-as-customer creates row; price snapshot correctness; order_code format + daily reset; jemput requires alamat+slot; estimation = sum(estimasi_jam); initial status log written; rollback on failure.
- **Status transitions:** forward step ok; backward/skip rejected; cancel requires keterangan; log rows written with user + timestamps.
- **Tenancy isolation:** laundry A cannot list/view/update laundry B's customers/products/orders; POS queries scoped; struk access scoped; pivot enforcement in middleware.
- **Master CRUD:** telepon uniqueness per laundry; kategori nama uniqueness case-insensitive per laundry; product nama unique per kategori; inactive products hidden from POS.
- **Print routes:** return 200 for pdf/browser/thermal modes.

## 8. Success Criteria

1. Cashier can create an order end-to-end in POS (< 30s interaction), pick registered or walk-in customer, get printed struk in all 3 modes.
2. Two laundries can be operated from one install with fully isolated data.
3. Each laundry can define its own status flow; transitions enforced by urutan rules.
4. All Pest tests pass; `composer test` green.
