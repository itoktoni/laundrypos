<?php

namespace App\Livewire\Pos;

use App\Actions\CreateOrderAction;
use App\Models\Customer;
use App\Models\Kategori;
use App\Models\Product;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PosTerminal extends Component
{
    public string $search = '';

    public $activeKategoriId = null;

    /** Cart lines: [product_id, nama, satuan, harga, estimasi_jam, qty] */
    public array $cart = [];

    public $customerId = null;

    public bool $walkinMode = false;

    public string $walkinNama = '';

    public string $walkinTelepon = '';

    public bool $saveWalkinAsCustomer = false;

    public array $errors = [];

    public function addToCart(int $productId): void
    {
        $product = Product::where('product_is_aktif', true)->find($productId);
        if ($product === null) {
            return;
        }

        foreach ($this->cart as $i => $line) {
            if ($line['product_id'] === $productId) {
                $this->cart[$i]['qty'] = min(999, $line['qty'] + 1);

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

    public function bumpQty(int $index, int $delta): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $this->cart[$index]['qty'] = max(1, min(999, $this->cart[$index]['qty'] + $delta));
    }

    public function removeLine(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($line) => $line['harga'] * $line['qty']);
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
            $walkinNama = $this->walkinNama;
            $walkinTelepon = $this->walkinTelepon;
        }

        try {
            $order = CreateOrderAction::run([
                'customer_id' => $this->customerId ?: null,
                'walkin_nama' => $walkinNama,
                'walkin_telepon' => $walkinTelepon,
                'save_walkin_customer' => ! $this->customerId && $this->saveWalkinAsCustomer,
                'metode_pengambilan' => 'antar_toko',
                'metode_pembayaran' => 'tunai',
                'items' => collect($this->cart)->map(fn ($line) => [
                    'product_id' => $line['product_id'],
                    'qty' => $line['qty'],
                ])->all(),
            ]);
        } catch (ValidationException $e) {
            $this->errors = $e->errors();

            return;
        }

        $this->reset('cart', 'customerId', 'walkinNama', 'walkinTelepon', 'saveWalkinAsCustomer', 'walkinMode');

        return redirect()->route('order.getShow', ['id' => $order->getKey()]);
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
            'customerOptions' => Customer::orderBy('customer_nama')->limit(100)->get()
                ->pluck('customer_nama', 'customer_id')->all(),
        ]);
    }
}
