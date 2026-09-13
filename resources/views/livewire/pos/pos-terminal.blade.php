<?php
use App\Livewire\Pos\PosTerminal;

/** @var PosTerminal $this */ ?>

<div class="flex flex-col gap-4" x-data="posClientApp()" x-init="initProducts(window.__posProducts)" :class="posTab === 'belanja' ? 'max-lg:h-[calc(100svh-12rem-env(safe-area-inset-bottom))] max-lg:overflow-hidden' : ''">
<script>
// POS data injected via Blade — avoid Js::from inside Alpine attribute (breaks quoting)
window.__posProducts = @js($products->map(fn($p)=>['id'=>$p->product_id,'nama'=>$p->product_nama,'satuan'=> (string)$p->product_satuan,'harga'=>(float)$p->product_harga_dasar,'estimasi_jam'=>(int)$p->product_estimasi_jam])->values());
window.__posCustomers = @js($customers->map(fn($c) => ['id' => $c->customer_id, 'nama' => $c->customer_nama, 'telp' => $c->customer_telepon])->values());
window.__posCustomersLabel = @js($customers->map(fn($c) => ['id' => (string)$c->customer_id, 'nama' => $c->customer_nama])->values());
function posClientApp(){
    return {
        posTab: 'belanja',
        cart: [],
        productsMap: {},
        printOrder(id, mode='browser'){
            if(!id) return;
            const url = `/order/print/${id}?mode=${mode}`;
            const w = window.open(url, '_blank');
            if(!w){
                // fallback: navigate in current tab if popup blocked
                window.location.href = url;
            }
        },
        initProducts(products){
            if(!products) return;
            products.forEach(p=>{ this.productsMap[p.id]=p; });
        },
        normalizeQty(val){
            if(val===null || val==='') return null;
            if(typeof val==='number') return val;
            let s=String(val).trim().replace(/\s*[a-zA-Z]+\s*$/,'').trim();
            if(s.includes(',') && s.includes('.')){
                let lastComma=s.lastIndexOf(','), lastDot=s.lastIndexOf('.');
                if(lastComma>lastDot){ s=s.replace(/\./g,'').replace(',', '.'); } else { s=s.replace(/,/g,''); }
            } else if(s.includes(',')) s=s.replace(',', '.');
            s=s.trim();
            if(s==='' || isNaN(s)) return null;
            return parseFloat(s);
        },
        formatAngka(val){
            let n=this.normalizeQty(val) ?? 0;
            n=Math.round(n);
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },
        formatQty(val){
            let n=this.normalizeQty(val) ?? 0;
            if(Math.abs(n - Math.round(n)) < 0.0005) return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            let s=n.toFixed(3).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            s=s.replace(/,?0+$/, '').replace(/,$/,'');
            return s;
        },
        async recalcPromo(){
            try{
                let w=this.$wire;
                if(!w || !w.discountId){
                    // fallback via Livewire DOM
                    const el=document.querySelector('[wire\\:id]');
                    w = el? el.__livewire?.$wire : null;
                }
                if(w?.discountId) await w.applyPromoClient(this.cart);
            }catch(e){}
        },
        addToCart(idOrObj){
            let p=null;
            if(typeof idOrObj==='object' && idOrObj!==null){ p=idOrObj; p.id=p.id ?? p.product_id; }
            else { p=this.productsMap[idOrObj]; }
            if(!p) return;
            let pid=p.id;
            let idx=this.cart.findIndex(l=>l.product_id===pid);
            if(idx!==-1){ let cur=this.normalizeQty(this.cart[idx].qty) ?? 1; this.cart[idx].qty=Math.min(999, Math.round((cur+1)*1000)/1000); }
            else { this.cart.push({product_id:pid, nama:p.nama, satuan:p.satuan, harga:p.harga, estimasi_jam:p.estimasi_jam ?? 0, qty:1}); }
            this.recalcPromo();
        },
        bumpQty(i, delta){
            if(!this.cart[i]) return;
            let cur=this.normalizeQty(this.cart[i].qty) ?? 1;
            let d=this.normalizeQty(delta) ?? 0;
            let n=Math.round((cur+d)*1000)/1000;
            n=Math.max(0.5, Math.min(999, n));
            this.cart[i].qty=n;
            this.recalcPromo();
        },
        setQty(i, val){
            if(!this.cart[i]) return;
            let q=this.normalizeQty(val);
            if(q===null || q<0.5) q=0.5;
            q=Math.round(Math.min(999,q)*1000)/1000;
            this.cart[i].qty=q;
            this.recalcPromo();
        },
        removeLine(i){ this.cart.splice(i,1); this.recalcPromo(); },
        get subtotal(){ return this.cart.reduce((s,l)=> s + (parseFloat(l.harga)||0) * (this.normalizeQty(l.qty) ?? 0), 0); },
        get total(){
            let disc=0;
            try{ disc=parseFloat(this.$wire?.discountAmount ?? 0) || 0; }catch(e){ try{ disc=parseFloat(this.$wire?.get('discountAmount'))||0; }catch(e2){} }
            // fallback to Livewire entangle via DOM if $wire not yet ready
            if(!disc){
                try{ const el=document.querySelector('[wire\\:id]'); if(el && el.__livewire) disc=parseFloat(el.__livewire.snapshot?.data?.discountAmount ?? 0)||0; }catch(e){}
            }
            return Math.max(0, this.subtotal - disc);
        },
        get checkoutCount(){ return this.cart.length; },
        async confirmCash(){
            if(this.cart.length===0) return;
            const w=this.$wire; if(!w){ console.error('Livewire $wire not ready'); return; }
            await w.confirmCashClient(this.cart); this.cart=[];
        },
        async confirmOrder(){
            if(this.cart.length===0) return;
            const w=this.$wire; if(!w){ console.error('Livewire $wire not ready'); return; }
            await w.confirmOrderClient(this.cart); this.cart=[];
        }
    }
}
</script>
    {{-- Top bar: customer selector (redesigned searchable) --}}
    <div class="flex items-center gap-3 max-lg:shrink-0">
        <div class="flex-1 max-w-md" x-data="{ open: false, q: '', get filtered() { const list = window.__posCustomers || []; if (!this.q.trim()) return list; const s = this.q.toLowerCase(); return list.filter(c => c.nama.toLowerCase().includes(s) || (c.telp||'').includes(s)); }, get selectedLabel() { if (!$wire.customerId) return 'Walk-in'; const list = window.__posCustomersLabel || []; const found = list.find(c => c.id == $wire.customerId); return found ? found.nama : 'Walk-in'; } }" @click.away="open = false">
            <label class="text-xs font-medium text-on-surface-variant mb-1 block flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">person</span> Pelanggan</label>
            <div class="relative">
                <button type="button" @click="open = !open" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl border bg-surface-container-lowest text-sm text-left hover:border-primary/30 focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                        :class="open ? 'border-primary ring-2 ring-primary/20' : 'border-outline-variant'">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="$wire.customerId ? 'bg-primary text-on-primary' : 'bg-outline-variant/40 text-on-surface-variant'">
                        <span class="material-symbols-outlined text-[16px]" x-text="$wire.customerId ? 'person' : 'person_add'"></span>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="block text-sm font-medium truncate" x-text="selectedLabel"></span>
                        <span class="block text-xs text-on-surface-variant truncate" x-text="$wire.customerId ? 'Pelanggan terdaftar' : 'Tanpa member — isi walk-in jika perlu'"></span>
                    </span>
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>

                <div x-show="open" x-transition x-cloak class="absolute left-0 right-0 top-full mt-2 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-xl overflow-hidden z-30">
                    <div class="p-2 border-b border-outline-variant">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                            <input type="text" x-model="q" placeholder="Cari nama atau telepon..." class="w-full pl-9 pr-8 py-2 rounded-lg border border-outline-variant bg-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            <button type="button" x-show="q" @click="q=''" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full hover:bg-surface-container flex items-center justify-center"><span class="material-symbols-outlined text-[14px]">close</span></button>
                        </div>
                    </div>
                    <div class="max-h-64 overflow-y-auto py-1">
                        <button type="button" @click="$wire.set('customerId', null); open=false; q=''" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-surface-container text-left" :class="!$wire.customerId ? 'bg-primary/10' : ''">
                            <span class="w-8 h-8 rounded-full bg-outline-variant/40 flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-[16px]">person_add</span></span>
                            <span class="flex-1"><span class="block text-sm font-medium">Walk-in</span><span class="block text-xs text-on-surface-variant">Pelanggan tanpa member</span></span>
                            <span x-show="!$wire.customerId" class="material-symbols-outlined text-primary text-[18px]">check</span>
                        </button>
                        <template x-for="c in filtered" :key="c.id">
                            <button type="button" @click="$wire.set('customerId', c.id); open=false" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-surface-container text-left" :class="$wire.customerId == c.id ? 'bg-primary/10' : ''">
                                <span class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 text-xs font-bold" x-text="c.nama.charAt(0).toUpperCase()"></span>
                                <span class="flex-1 min-w-0"><span class="block text-sm font-medium truncate" x-text="c.nama"></span><span class="block text-xs text-on-surface-variant truncate" x-text="c.telp || '—'"></span></span>
                                <span x-show="$wire.customerId == c.id" class="material-symbols-outlined text-primary text-[18px]">check</span>
                            </button>
                        </template>
                        <template x-if="filtered.length === 0">
                            <p class="px-3 py-6 text-center text-sm text-on-surface-variant">Tidak ada pelanggan</p>
                        </template>
                    </div>
                    <div class="p-2 border-t border-outline-variant bg-surface-container/50 flex justify-between items-center">
                        <span class="text-xs text-on-surface-variant" x-text="filtered.length + ' pelanggan'"></span>
                        <a href="{{ route('customer.getCreate') }}" wire:navigate class="text-xs font-medium text-primary hover:underline inline-flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">add</span> Pelanggan baru</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 max-lg:flex-1 max-lg:min-h-0 max-lg:flex max-lg:flex-col">
    {{-- Mobile tabs: Service | Checkout --}}
    <div class="lg:hidden flex gap-1 p-1 rounded-2xl bg-surface-container border border-outline-variant shrink-0 sticky top-16 z-30">
        <button type="button" @click="posTab = 'belanja'"
                :class="posTab === 'belanja' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant'"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl text-sm font-bold transition-colors">
            <span class="material-symbols-outlined text-[18px]">local_laundry_service</span> Service
        </button>
        <button type="button" @click="posTab = 'checkout'"
                :class="posTab === 'checkout' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant'"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl transition-colors">
            <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
            <span class="text-left leading-tight">
                <span class="block text-[10px] opacity-80">Checkout &bull; <span x-text="cart.length"></span> item</span>
                <span class="block text-sm font-bold">Rp&nbsp;<span x-text="formatAngka(total)"></span></span>
            </span>
        </button>
    </div>
    {{-- Left panel: kategori tabs + product grid --}}
    <div :class="posTab === 'checkout' ? 'hidden lg:flex' : 'flex'" class="lg:col-span-2 flex-col min-h-0 max-lg:flex-1 max-lg:min-h-0" x-data="{ view: localStorage.getItem('pos_view') || 'grid' }" x-init="$watch('view', v => localStorage.setItem('pos_view', v))">
        {{-- Kategori tabs --}}
        <div class="flex gap-2 mb-3 overflow-x-auto pb-2 shrink-0">
            <button wire:click="$set('activeKategoriId', null)"
                    class="btn btn-sm {{ $activeKategoriId === null ? 'btn-primary' : 'btn-soft' }}">Semua</button>
            @foreach ($kategoris as $kategori)
                <button wire:click="$set('activeKategoriId', {{ $kategori->kategori_id }})"
                        class="btn btn-sm whitespace-nowrap {{ $activeKategoriId === $kategori->kategori_id ? 'btn-primary' : 'btn-soft' }}">
                    {{ $kategori->kategori_nama }}
                </button>
            @endforeach
        </div>

        {{-- Search + view toggle --}}
        <div class="flex gap-2 mb-4 shrink-0">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..."
                       class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" />
                @if($search !== '')
                    <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-surface-container hover:bg-outline-variant flex items-center justify-center">
                        <span class="material-symbols-outlined text-[14px]">close</span>
                    </button>
                @endif
            </div>
            <button type="button" @click="view = (view === 'grid' ? 'list' : 'grid')" :title="view === 'grid' ? 'Tampilkan list (1 kolom)' : 'Tampilkan grid'"
                    class="shrink-0 w-11 h-11 rounded-xl border border-outline-variant bg-surface-container-lowest hover:bg-surface-container flex items-center justify-center text-on-surface-variant hover:text-on-surface transition">
                <span class="material-symbols-outlined text-[20px]" x-text="view === 'grid' ? 'view_list' : 'grid_view'"></span>
            </button>
        </div>

        {{-- Product grid — scrollable --}}
        <div :class="view === 'list' ? 'grid grid-cols-1 gap-2' : 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2'"
             class="overflow-y-auto pb-4 max-lg:flex-1 max-lg:min-h-0 lg:max-h-[calc(100vh-20rem)]">
            @forelse ($products as $product)
                <button type="button" @click="addToCart({{ $product->product_id }})"
                        class="border border-outline-variant rounded-xl px-3 py-3 text-left bg-surface-container-lowest hover:bg-surface-container hover:shadow-md hover:border-primary/20 transition shadow-sm active:scale-[0.98]"
                        :class="view === 'list' ? 'flex flex-row items-center justify-between' : ''">
                    <p class="text-sm font-medium truncate">{{ $product->product_nama }}</p>
                    <p class="text-xs text-primary font-bold mt-1">{{ formatAngka($product->product_harga_dasar) }} / {{ $product->product_satuan?->description ?? $product->product_satuan }}</p>
                </button>
            @empty
                <p class="col-span-full text-sm text-on-surface-variant py-8 text-center">Tidak ada produk ditemukan.</p>
            @endforelse
        </div>
    </div>

    {{-- Right panel: checkout — own tab on mobile, side panel on desktop --}}
    <div :class="posTab !== 'checkout' ? 'hidden lg:block' : ''" class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm h-fit">
        <h3 class="font-bold mb-3">Checkout</h3>
        <div>
        @if (count($errors) > 0)
            <div class="mb-3 p-2 text-xs bg-error-container text-on-error-container rounded-lg">
                @foreach ($errors as $field => $messages)
                    @foreach ($messages as $message)
                        <p>{{ $message }}</p>
                    @endforeach
                @endforeach
            </div>
        @endif

        {{-- Cart lines — CLIENT SIDE (no Livewire roundtrip) --}}
        <div class="space-y-2 mb-3">
            <template x-if="cart.length===0">
                <div class="flex flex-col items-center gap-2 py-8 text-center">
                    <span class="w-14 h-14 rounded-full bg-surface-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-[28px] text-on-surface-variant">shopping_cart</span>
                    </span>
                    <p class="text-sm font-medium text-on-surface">Keranjang kosong</p>
                    <p class="text-xs text-on-surface-variant">Ketuk produk untuk menambah</p>
                </div>
            </template>
            <template x-for="(line, i) in cart" :key="line.product_id">
                <div class="flex items-center gap-2 rounded-2xl border border-outline-variant/60 bg-surface-container-lowest p-2.5">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate" x-text="line.nama"></p>
                        <p class="text-[11px] text-on-surface-variant"><span x-text="formatAngka(line.harga)"></span> / <span x-text="line.satuan"></span></p>
                        <p class="text-sm font-bold text-primary mt-0.5" x-text="formatAngka((parseFloat(line.harga)||0) * (normalizeQty(line.qty) ?? 0))"></p>
                    </div>
                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                        <button type="button" @click="removeLine(i)" class="w-7 h-7 rounded-full hover:bg-error/10 text-on-surface-variant hover:text-error flex items-center justify-center transition-colors" title="Hapus">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </button>
                        <div class="flex items-center gap-0.5 rounded-full border border-outline-variant p-0.5">
                            <button type="button" @click="bumpQty(i, -1)" class="w-7 h-7 rounded-full bg-surface-container hover:bg-outline-variant flex items-center justify-center text-base font-bold transition-colors">&minus;</button>
                            <input type="text" inputmode="decimal" :value="formatQty(line.qty)"
                                   @change="setQty(i, $el.value)" @blur="setQty(i, $el.value)" @keydown.enter="setQty(i, $el.value)"
                                   class="w-14 text-center text-sm font-bold bg-transparent border-0 focus:ring-0 p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                   title="Edit qty — bisa koma (2,3)" placeholder="1">
                            <button type="button" @click="bumpQty(i, 1)" class="w-7 h-7 rounded-full bg-primary text-on-primary hover:bg-primary/90 flex items-center justify-center text-base font-bold transition-colors">+</button>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" @click="bumpQty(i, -0.5)" class="text-[10px] px-1.5 py-0.5 rounded-full border border-outline-variant hover:bg-surface-container">-0,5</button>
                            <button type="button" @click="bumpQty(i, 0.5)" class="text-[10px] px-1.5 py-0.5 rounded-full border border-outline-variant hover:bg-surface-container">+0,5</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="shrink-0 sticky bottom-16 z-40 max-lg:bg-surface-container-lowest max-lg:border-t max-lg:border-outline-variant max-lg:p-4 max-lg:rounded-t-2xl max-lg:shadow-[0_-8px_24px_rgba(0,0,0,0.12)] lg:bottom-4 lg:z-20 lg:bg-surface-container-lowest lg:border lg:border-outline-variant/60 lg:p-3 lg:rounded-2xl lg:shadow-[0_-4px_12px_rgba(0,0,0,0.06)]">
        {{-- Promo --}}
        <div class="mb-4">
            <label class="text-xs text-on-surface-variant mb-1 block">Promo</label>
            @if ($discountId)
                <div class="flex items-center gap-2">
                    <span class="btn btn-sm btn-success gap-1">
                        <span class="material-symbols-outlined text-sm">sell</span>
                        {{ $discountNama }}
                    </span>
                    <button type="button" wire:click="removePromo" class="text-error text-xs">Hapus</button>
                </div>
            @else
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-[18px]">sell</span>
                        <input type="text" wire:model="promoKode" @keydown.enter="await $wire.set('cart', cart); await $wire.applyPromoClient(cart)"
                               placeholder="Kode promo..."
                               class="input input-sm !pl-11 w-full" />
                    </div>
                    <button type="button" @click="await $wire.set('cart', cart); await $wire.applyPromoClient(cart)" class="btn btn-sm btn-primary">Pakai</button>
                </div>
                @if (! empty($suggestedPromos))
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach ($suggestedPromos as $promo)
                            <button type="button" @click="await $wire.set('promoKode', '{{ $promo->discount_kode }}'); await $wire.applyPromoClient(cart)"
                                    class="text-[10px] px-2 py-0.5 border border-outline-variant rounded-full hover:bg-surface-container transition">
                                {{ $promo->discount_kode }}
                            </button>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

        {{-- Totals — CLIENT SIDE instant --}}
        <div class="rounded-2xl bg-primary/5 border border-primary/15 p-3 space-y-1 text-sm mb-3">
            <div class="flex justify-between text-on-surface-variant"><span>Subtotal</span><span x-text="formatAngka(subtotal)"></span></div>
            <template x-if="$wire.discountAmount > 0">
                <div class="flex justify-between text-success"><span x-text="'Diskon (' + $wire.discountNama + ')'"></span><span x-text="'-' + formatAngka($wire.discountAmount)"></span></div>
            </template>
            <div class="flex justify-between items-end pt-1"><span class="font-bold">Total</span><span class="text-2xl font-bold text-primary" x-text="formatAngka(total)"></span></div>
        </div>

        {{-- Action buttons — CLIENT SIDE, only Bayar hits server — use $wire directly in Alpine expression (guaranteed scope) --}}
        <div class="flex gap-2">
            <button type="button" @click="if(cart.length){ await $wire.confirmCashClient(cart); cart=[]; }" :disabled="cart.length===0" :class="cart.length===0 ? 'opacity-40 cursor-not-allowed' : ''"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-2xl font-bold text-[15px] border bg-success text-white border-success/20 hover:bg-success/90 transition-colors">
                <span class="material-symbols-outlined text-[20px]">payments</span> CASH
            </button>
            <button type="button" @click="if(cart.length){ await $wire.confirmOrderClient(cart); cart=[]; }" :disabled="cart.length===0" :class="cart.length===0 ? 'opacity-40 cursor-not-allowed' : ''"
                    class="btn btn-primary flex-1 inline-flex items-center justify-center gap-1.5 !py-3 !rounded-2xl !text-[15px]">
                <span class="material-symbols-outlined text-[20px]">qr_code</span> Bayar
            </button>
        </div>
        </div>
    </div>
</div>
</div>

{{-- Cash Success Modal --}}
@if ($showCash)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeCash">
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 text-center">
        <div class="flex flex-col items-center gap-3 py-2">
            <div class="w-20 h-20 rounded-full bg-success-container flex items-center justify-center">
                <span class="material-symbols-outlined text-[48px] text-on-success-container">check_circle</span>
            </div>
            <h3 class="text-lg font-bold text-on-surface">Pembayaran Tunai Berhasil</h3>
            <p class="text-sm text-on-surface-variant">{{ $cashOrderCode }}</p>
            <p class="text-2xl font-bold text-success">{{ formatAngka($cashTotal) }}</p>
            <p class="text-xs text-on-surface-variant">Metode: Tunai — dibayar sesuai nominal</p>
        </div>
        <div class="flex gap-2 mt-4">
            <a href="{{ $cashOrderId ? route('order.getShow', ['id' => $cashOrderId]) : '#' }}" wire:navigate class="btn btn-outline flex-1">Lihat Order</a>
            <button type="button" @click="printOrder($wire.cashOrderId, 'browser')" class="btn btn-outline flex-1 inline-flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-[16px]">print</span> Cetak
            </button>
            <button type="button" wire:click="closeCash" class="btn btn-primary flex-1">Selesai</button>
        </div>
    </div>
</div>
@endif

{{-- QRIS Modal — tidak boleh dismiss via backdrop, harus Cetak (lunas) atau Lihat Order --}}
@if ($showQr)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
     wire:poll.1s="pollQrAndTick">
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 text-center">

        @if ($qrPaid)
            {{-- PAID SUCCESS — jangan auto-dismiss, user cetak dulu (Non Tunai) --}}
            <div class="flex flex-col items-center gap-3 py-4">
                <div class="w-20 h-20 rounded-full bg-success-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-[48px] text-on-success-container">check_circle</span>
                </div>
                <h3 class="text-lg font-bold text-on-surface">Pembayaran Berhasil</h3>
                <p class="text-sm text-on-surface-variant">{{ $qrOrderCode }}</p>
                <p class="text-xl font-bold text-success">{{ formatAngka($qrTotal + $qrSuffix) }}</p>
                <div class="flex gap-2 mt-3 w-full">
                    <button type="button" @click="printOrder($wire.qrOrderId, 'browser'); $wire.closeQr()" class="btn btn-primary flex-1 inline-flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">print</span> Cetak & Selesai
                    </button>
                    <button type="button" @click="printOrder($wire.qrOrderId, 'thermal'); $wire.closeQr()" class="btn btn-outline flex-1 inline-flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">receipt</span> Thermal
                    </button>
                </div>
                <p class="text-xs text-on-surface-variant mt-2">Bayar: Non Tunai — cetak untuk menutup</p>
            </div>
        @else
            {{-- QR CODE --}}
            <h3 class="text-lg font-bold mb-1">QRIS Pembayaran</h3>
            <p class="text-sm text-on-surface-variant mb-1">{{ $qrOrderCode }}</p>
            <p class="text-2xl font-bold text-primary mb-3">Rp {{ formatAngka($qrTotal + $qrSuffix) }}</p>

                <div class="flex justify-center mb-3">
                    @if($qrDataUri)
                        <img src="{{ $qrDataUri }}" alt="QRIS" class="w-52 h-52">
                    @else
                        <div class="w-52 h-52 flex items-center justify-center text-sm text-gray-400">Memuat QR...</div>
                    @endif
                </div>

            {{-- Timer --}}
            @php $minutes = intdiv($qrTimeLeft, 60); $seconds = $qrTimeLeft % 60; @endphp
            @if ($qrTimeLeft > 0)
                <div class="flex items-center justify-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">schedule</span>
                    <span class="text-sm font-mono text-on-surface-variant">{{ sprintf('%02d:%02d', $minutes, $seconds) }}</span>
                </div>
            @else
                <p class="text-sm text-error font-medium mb-3">QRIS kedaluwarsa</p>
            @endif

            {{-- Polling indicator --}}
            <div class="flex items-center justify-center gap-1.5 mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                <span class="text-xs text-on-surface-variant">Menunggu pembayaran...</span>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('order.getShow', ['id' => $qrOrderId]) }}" wire:navigate
                   class="btn btn-outline flex-1">
                    Lihat Order
                </a>
                <span class="btn btn-outline flex-1 opacity-50 cursor-not-allowed inline-flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">hourglass_empty</span> Menunggu Bayar
                </span>
            </div>
        @endif

    </div>
</div>
@endif
</div>
