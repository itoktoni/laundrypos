<?php /** @var \App\Livewire\Pos\PosTerminal $this */ ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Left panel: kategori tabs + product grid --}}
    <div class="lg:col-span-2">
        <div class="flex gap-2 mb-3 overflow-x-auto pb-1">
            <button wire:click="$set('activeKategoriId', null)"
                    class="btn btn-sm {{ $activeKategoriId === null ? 'btn-primary' : 'btn-soft' }}">Semua</button>
            @foreach ($kategoris as $kategori)
                <button wire:click="$set('activeKategoriId', {{ $kategori->kategori_id }})"
                        class="btn btn-sm whitespace-nowrap {{ $activeKategoriId === $kategori->kategori_id ? 'btn-primary' : 'btn-soft' }}">
                    {{ $kategori->kategori_nama }}
                </button>
            @endforeach
        </div>

        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..."
               class="input w-full mb-4" />

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
            @forelse ($products as $product)
                <button type="button" wire:click="addToCart({{ $product->product_id }})"
                        class="border border-outline-variant rounded-xl p-4 text-left bg-surface-container-lowest hover:bg-surface-container transition shadow-sm">
                    <p class="font-semibold text-sm truncate">{{ $product->product_nama }}</p>
                    <p class="text-primary font-bold mt-2">{{ formatAngka($product->product_harga_dasar) }}</p>
                    <p class="text-xs text-on-surface-variant">{{ $product->product_satuan?->description ?? $product->product_satuan }}</p>
                </button>
            @empty
                <p class="col-span-full text-sm text-on-surface-variant">Tidak ada produk ditemukan.</p>
            @endforelse
        </div>
    </div>

    {{-- Right panel: cart --}}
    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm h-fit">
        <h3 class="font-bold mb-3">Keranjang</h3>

        @if (count($errors) > 0)
            <div class="mb-3 p-2 text-xs bg-error-container text-on-error-container rounded-lg">
                @foreach ($errors as $field => $messages)
                    @foreach ($messages as $message)
                        <p>{{ $message }}</p>
                    @endforeach
                @endforeach
            </div>
        @endif

        {{-- Cart lines --}}
        <div class="space-y-2 mb-4 max-h-64 overflow-y-auto">
            @forelse ($cart as $i => $line)
                <div class="flex items-center gap-2 border-b border-outline-variant/50 pb-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $line['nama'] }}</p>
                        <p class="text-xs text-on-surface-variant">{{ formatAngka($line['harga']) }} / {{ $line['satuan'] }}</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="bumpQty({{ $i }}, -1)" class="btn btn-xs">−</button>
                        <span class="w-8 text-center text-sm">{{ $line['qty'] }}</span>
                        <button type="button" wire:click="bumpQty({{ $i }}, 1)" class="btn btn-xs">+</button>
                    </div>
                    <p class="w-20 text-right text-sm font-medium">{{ formatAngka($line['harga'] * $line['qty']) }}</p>
                    <button type="button" wire:click="removeLine({{ $i }})" class="text-error text-xs">×</button>
                </div>
            @empty
                <p class="text-sm text-on-surface-variant">Keranjang kosong.</p>
            @endforelse
        </div>

        {{-- Customer --}}
        <div class="space-y-2 mb-4">
            @if (! $walkinMode)
                <label class="text-xs text-on-surface-variant">Pelanggan</label>
                <select wire:model="customerId" class="select w-full">
                    <option value="">-- Pilih pelanggan / Walk-in --</option>
                    @foreach ($customerOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" wire:model="walkinMode" class="checkbox checkbox-sm"> Pelanggan Walk-in
                </label>
            @else
                <label class="text-xs text-on-surface-variant">Walk-in</label>
                <input type="text" wire:model="walkinNama" placeholder="Nama" class="input w-full" />
                <input type="text" wire:model="walkinTelepon" placeholder="Telepon" class="input w-full" />
                <label class="flex items-start gap-2 text-xs cursor-pointer">
                    <input type="checkbox" wire:model="saveWalkinAsCustomer" class="checkbox checkbox-sm mt-0.5">
                    <span>Simpan sebagai pelanggan</span>
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" wire:model="walkinMode" class="checkbox checkbox-sm"> Pilih pelanggan terdaftar
                </label>
            @endif
        </div>

        {{-- Totals --}}
        <div class="border-t border-outline-variant pt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><span class="font-bold">{{ formatAngka($this->subtotal) }}</span></div>
            <div class="flex justify-between text-on-surface-variant"><span>Estimasi selesai</span><span>{{ $this->estimasiJam }} jam</span></div>
        </div>

        <button type="button" wire:click="confirmOrder" wire:loading.attr="disabled"
                class="btn btn-primary w-full mt-4" @disabled(count($cart) === 0)>
            Konfirmasi Order
        </button>
    </div>
</div>
