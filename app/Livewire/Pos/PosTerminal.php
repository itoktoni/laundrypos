<?php

namespace App\Livewire\Pos;

use App\Actions\CreateOrderAction;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PosTerminal extends Component
{
    public string $search = '';

    public $activeKategoriId = null;

    /** Cart lines: [product_id, nama, satuan, harga, estimasi_jam, qty] */
    public array $cart = [];

    public $customerId = null;

    public string $walkinNama = '';

    public string $walkinTelepon = '';

    public bool $saveWalkinAsCustomer = false;

    public string $promoKode = '';

    public $discountId = null;

    public string $discountNama = '';

    public float $discountAmount = 0;

    public array $errors = [];

    // QR modal state
    public bool $showQr = false;

    public string $qrOrderId = '';

    public string $qrOrderCode = '';

    public float $qrTotal = 0;

    public int $qrSuffix = 0;

    public string $qrDataUri = '';

    public bool $qrPaid = false;

    public int $qrStatusId = 0;

    public int $qrTimeLeft = 0;

    // Cash success state
    public bool $showCash = false;

    public string $cashOrderCode = '';

    public float $cashTotal = 0;

    public string $cashOrderId = '';

    public function applyPromo(): void
    {
        $this->discountId = null;
        $this->discountNama = '';
        $this->discountAmount = 0;

        if ($this->promoKode === '') {
            return;
        }

        $discount = Discount::aktif()
            ->where('discount_kode', strtoupper($this->promoKode))
            ->first();

        if (! $discount) {
            $this->errors = ['promo' => ['Kode promo tidak valid atau sudah kedaluwarsa']];

            return;
        }

        $diskon = $discount->hitungDiskon($this->subtotal);

        if ($diskon <= 0) {
            $this->errors = ['promo' => ['Minimal pembelian '.formatAngka($discount->discount_min_pembelian)]];

            return;
        }

        $this->discountId = $discount->discount_id;
        $this->discountNama = $discount->discount_nama.' ('.($discount->discount_tipe === 'persen' ? $discount->discount_nilai.'%' : formatAngka($discount->discount_nilai)).')';
        $this->discountAmount = $diskon;
        unset($this->errors['promo']);
    }

    public function removePromo(): void
    {
        $this->promoKode = '';
        $this->discountId = null;
        $this->discountNama = '';
        $this->discountAmount = 0;
        unset($this->errors['promo']);
    }

    public function addToCart(int $productId): void
    {
        $product = Product::where('product_is_aktif', true)->find($productId);
        if ($product === null) {
            return;
        }

        foreach ($this->cart as $i => $line) {
            if ($line['product_id'] === $productId) {
                $cur = normalizeQty($line['qty'], 1) ?? 1;
                $this->cart[$i]['qty'] = min(999, round($cur + 1, 3));

                return;
            }
        }

        $this->cart[] = [
            'product_id' => $product->getKey(),
            'nama' => $product->product_nama,
            'satuan' => (string) $product->product_satuan,
            'harga' => (float) $product->product_harga_dasar,
            'estimasi_jam' => (int) $product->product_estimasi_jam,
            'qty' => 1,
        ];
    }

    public function bumpQty(int $index, mixed $delta): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $current = normalizeQty($this->cart[$index]['qty'], 1) ?? 1;
        $delta = normalizeQty($delta, 0) ?? 0;
        // Untuk satuan kg/liter, step 0.1 lebih alami; pcs tetap 1 — deteksi otomatis
        $newQty = $current + $delta;
        $newQty = round($newQty, 3);
        $newQty = max(0.5, min(999, $newQty));
        $this->cart[$index]['qty'] = $newQty;

        // Recalculate discount if promo applied
        if ($this->discountId) {
            $this->applyPromo();
        }
    }

    public function setQty(int $index, mixed $value): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $qty = normalizeQty($value, null);
        if ($qty === null || $qty < 0.5) {
            $qty = 0.5;
        }
        $qty = round(min(999, $qty), 3);
        $this->cart[$index]['qty'] = $qty;

        if ($this->discountId) {
            $this->applyPromo();
        }
    }

    public function updatedCart(): void
    {
        foreach ($this->cart as $i => $line) {
            $raw = $line['qty'] ?? null;
            // Biarkan input intermediate seperti "", "2,", "2." tetap apa adanya agar user bisa lanjut ketik
            if (is_string($raw)) {
                $trim = trim($raw);
                if ($trim === '' || $trim === ',' || $trim === '.' || $trim === '-' || str_ends_with($trim, ',') || str_ends_with($trim, '.')) {
                    continue;
                }
            }
            $qty = normalizeQty($raw, null);
            if ($qty === null) {
                continue;
            }
            if ($qty < 0.5) {
                $qty = 0.5;
            }
            $qty = round(min(999, $qty), 3);
            if (! isset($line['qty']) || $qty != (float) normalizeQty($line['qty'], $qty)) {
                $this->cart[$i]['qty'] = $qty;
            }
        }
        if ($this->discountId) {
            $this->applyPromo();
        }
    }

    public function removeLine(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);

        // Recalculate discount if promo applied
        if ($this->discountId) {
            $this->applyPromo();
        }
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->cart)->sum(fn ($line) => (float) $line['harga'] * (float) normalizeQty($line['qty'], 0));
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal - $this->discountAmount);
    }

    public function getSubtotalProperty(): float
    {
        return $this->subtotal;
    }

    public function getTotalProperty(): float
    {
        return $this->total;
    }

    public function getEstimasiJamProperty(): int
    {
        return (int) collect($this->cart)->sum('estimasi_jam');
    }

    public function confirmOrder()
    {
        $this->errors = [];

        if ($this->customerId) {
            $walkinNama = null;
            $walkinTelepon = null;
        } else {
            $walkinNama = $this->walkinNama !== '' ? $this->walkinNama : 'Walk-in';
            $walkinTelepon = $this->walkinTelepon !== '' ? $this->walkinTelepon : '-';
        }

        try {
            $order = CreateOrderAction::run([
                'customer_id' => $this->customerId ?: null,
                'walkin_nama' => $walkinNama,
                'walkin_telepon' => $walkinTelepon,
                'save_walkin_customer' => ! $this->customerId && $this->saveWalkinAsCustomer,
                'metode_pengambilan' => 'antar_toko',
                'metode_pembayaran' => 'tunai',
                'discount_id' => $this->discountId,
                'diskon' => $this->discountAmount,
                'items' => collect($this->cart)->map(fn ($line) => [
                    'product_id' => $line['product_id'],
                    'qty' => $line['qty'],
                ])->all(),
            ]);
        } catch (ValidationException $e) {
            $this->errors = $e->errors();

            return;
        }

        $this->reset('cart', 'customerId', 'walkinNama', 'walkinTelepon', 'saveWalkinAsCustomer', 'promoKode', 'discountId', 'discountNama', 'discountAmount');

        // Show QRIS modal - QR generation tidak boleh bikin order gagal
        $this->qrOrderId = $order->getKey();
        $this->qrOrderCode = $order->order_code;
        $this->qrTotal = (float) $order->order_total;
        $this->qrSuffix = random_int(10, 99);
        $this->qrStatusId = $order->order_status_id;
        $this->qrTimeLeft = config('app.qris_timeout', 300);
        try {
            $qrText = nominalQRIS(config('app.qris_data'), $this->qrTotal + $this->qrSuffix);
            $this->qrDataUri = qrCodeDataUri($qrText, 8, 2);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('QRIS generate failed', ['order' => $order->order_code, 'msg' => $e->getMessage()]);
            $this->qrDataUri = '';
        }
        $this->qrPaid = false;
        $this->showQr = true;
    }

    public function confirmCash()
    {
        $this->errors = [];

        if (empty($this->cart)) {
            $this->errors = ['cart' => ['Keranjang kosong']];
            return;
        }

        if ($this->customerId) {
            $walkinNama = null;
            $walkinTelepon = null;
        } else {
            $walkinNama = $this->walkinNama !== '' ? $this->walkinNama : 'Walk-in';
            $walkinTelepon = $this->walkinTelepon !== '' ? $this->walkinTelepon : '-';
        }

        try {
            $order = CreateOrderAction::run([
                'customer_id' => $this->customerId ?: null,
                'walkin_nama' => $walkinNama,
                'walkin_telepon' => $walkinTelepon,
                'save_walkin_customer' => ! $this->customerId && $this->saveWalkinAsCustomer,
                'metode_pengambilan' => 'antar_toko',
                'metode_pembayaran' => 'tunai',
                'discount_id' => $this->discountId,
                'diskon' => $this->discountAmount,
                'items' => collect($this->cart)->map(fn ($line) => [
                    'product_id' => $line['product_id'],
                    'qty' => $line['qty'],
                ])->all(),
            ]);
        } catch (ValidationException $e) {
            $this->errors = $e->errors();
            return;
        }

        $this->cashOrderId = $order->getKey();
        $this->cashOrderCode = $order->order_code;
        $this->cashTotal = (float) $order->order_total;
        $this->showCash = true;

        $this->reset('cart', 'customerId', 'walkinNama', 'walkinTelepon', 'saveWalkinAsCustomer', 'promoKode', 'discountId', 'discountNama', 'discountAmount');
    }

    public function closeCash(): void
    {
        $this->showCash = false;
        $this->cashOrderId = '';
        $this->cashOrderCode = '';
        $this->cashTotal = 0;
    }

    public function closeQr(): void
    {
        $this->showQr = false;
        $this->qrOrderId = '';
        $this->qrOrderCode = '';
        $this->qrTotal = 0;
        $this->qrSuffix = 0;
        $this->qrDataUri = '';
        $this->qrPaid = false;
        $this->qrStatusId = 0;
        $this->qrTimeLeft = 0;
    }

    public function pollQrStatus(): void
    {
        if (! $this->showQr || $this->qrPaid || $this->qrOrderId === '') {
            return;
        }

        $order = Order::find($this->qrOrderId);
        if ($order && $order->order_status_id !== $this->qrStatusId) {
            $this->qrPaid = true;
            $this->dispatch('qr-paid');
        }
    }

    public function decrementTimer(): void
    {
        if (! $this->showQr || $this->qrPaid) {
            return;
        }

        if ($this->qrTimeLeft > 0) {
            $this->qrTimeLeft--;
        }
    }

    public function getCustomerResultsProperty()
    {
        return Customer::where('customer_nama', 'like', '%'.$this->customerSearch.'%')
            ->orderBy('customer_nama')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        $products = Product::where('product_is_aktif', true)
            ->when($this->activeKategoriId, fn ($q) => $q->where('product_id_kategori', $this->activeKategoriId))
            ->when($this->search !== '', fn ($q) => $q->where('product_nama', 'like', '%'.$this->search.'%'))
            ->orderBy('product_nama')
            ->get();

        return view('livewire.pos.pos-terminal', [
            'kategoris' => Kategori::where('kategori_is_aktif', true)->get(),
            'products' => $products,
            'customers' => Customer::orderBy('customer_nama')->get(),
            'suggestedPromos' => Discount::aktif()->limit(5)->get(),
        ]);
    }
}
