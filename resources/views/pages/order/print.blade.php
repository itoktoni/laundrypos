<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Struk {{ $order->order_code }} — {{ $order->hasLaundry?->laundry_nama ?? config('website.name') }}</title>
    <style>
        :root{
            --primary: {{ config('website.colors.primary','#00288e') }};
            --primary-soft: #eef2ff;
            --ink:#0f172a;
            --muted:#64748b;
            --line:#e2e8f0;
            --surface:#ffffff;
            --radius:14px;
        }
        *{box-sizing:border-box}
        html,body{margin:0;padding:0;background:#f1f5f9;color:var(--ink);font-family:Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"; -webkit-font-smoothing:antialiased}
        a{color:inherit;text-decoration:none}
        .no-print{display:flex}
        @media print{
            .no-print{display:none !important}
            html,body{background:#fff}
            @page{margin:10mm}
            body{ -webkit-print-color-adjust:economy; print-color-adjust:economy }
            /* bluetooth 58mm — force no background */
            .paper,.grid,.card,.foot{background:#fff !important}
            .chip{background:#fff !important;color:#000 !important;border-color:#000 !important}
            .mark{background:#fff !important;color:#000 !important;border:1.5px solid #000 !important}
            .row{background:#fff !important;color:#000 !important}
            .row.total{background:#fff !important;color:#000 !important;border-top:1.5px solid #000;border-bottom:1.5px solid #000}
            .note-box{background:#fff !important;color:#000 !important;border:1px dashed #000 !important}
        }
        /* layout */
        .shell{max-width:820px;margin:24px auto;padding:0 16px}
        .paper{background:var(--surface);border:1px solid var(--line);border-radius:12px;overflow:hidden}
        .header{padding:22px 28px 18px;display:flex;gap:20px;justify-content:space-between;align-items:flex-start;border-bottom:1px solid var(--line)}
        .brand{display:flex;gap:14px;align-items:center}
        .mark{width:44px;height:44px;border-radius:12px;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:800;letter-spacing:.04em}
        .brand h1{margin:0;font-size:16px;line-height:1.1;font-weight:800;letter-spacing:-.02em}
        .brand p{margin:2px 0 0;font-size:12px;color:var(--muted);line-height:1.4;max-width:34ch}
        .meta{text-align:right;min-width:220px}
        .kicker{font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);font-weight:700}
        .code{margin:4px 0 6px;font-size:22px;font-weight:900;letter-spacing:-.03em;line-height:1}
        .chips{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;margin-top:8px}
        .chip{font-size:11px;font-weight:700;letter-spacing:.02em;padding:6px 10px;border-radius:999px;border:1px solid var(--line);background:#fff;color:var(--ink)}
        .chip.tunai{background:#dcfce7;border-color:#bbf7d0;color:#166534}
        .chip.nontunai{background:#dbeafe;border-color:#bfdbfe;color:#1e3a8a}
        .chip.status{background:var(--primary);color:#fff;border-color:var(--primary)}
        .grid{padding:18px 28px;display:grid;grid-template-columns:1fr 1fr;gap:18px;background:#f8fafc;border-bottom:1px solid var(--line)}
        .card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:14px}
        .label{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);font-weight:700;margin-bottom:6px}
        .value{font-size:13px;font-weight:700;line-height:1.4}
        .sub{font-size:12px;color:var(--muted);margin-top:2px;line-height:1.4}
        .items{padding:8px 28px 0}
        .tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:13px}
        .tbl th{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);font-weight:700;text-align:left;padding:12px 8px;border-bottom:1px solid var(--line);white-space:nowrap}
        .tbl th.r,.tbl td.r{text-align:right}
        .tbl td{padding:12px 8px;border-bottom:1px solid #f1f5f9;vertical-align:top}
        .tbl td:first-child{padding-left:0}
        .tbl td:last-child{padding-right:0}
        .name{font-weight:700;line-height:1.3}
        .satuan{font-size:11px;color:var(--muted);font-weight:600}
        .mono{font-variant-numeric:tabular-nums}
        .totals{padding:16px 28px 20px}
        .totals-box{margin-left:auto;max-width:360px;border:1px solid var(--line);border-radius:12px;overflow:hidden}
        .row{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;background:#fff}
        .row:last-child{border-bottom:0}
        .row.total{background:var(--primary);color:#fff;font-weight:800;font-size:15px;letter-spacing:-.01em}
        .row.total span:last-child{font-size:18px}
        .note{padding:0 28px 18px}
        .note-box{background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:12px 14px;font-size:12px;line-height:1.5;color:#92400e}
        .foot{padding:16px 28px 22px;display:flex;gap:16px;justify-content:space-between;align-items:center;border-top:1px dashed var(--line);background:#f8fafc}
        .foot p{margin:0;font-size:11px;color:var(--muted);line-height:1.5}
        .barcode{ text-align:right; }
        .barcode svg{max-width:220px;height:44px}
        .cut{display:none;border-top:1px dashed #94a3b8;margin:10px 0;position:relative}
        .cut span{position:absolute;left:50%;top:-8px;transform:translateX(-50%);background:#fff;padding:0 8px;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
        /* mobile focus — pakai di browser mode pada HP */
        @media (max-width: 640px){
            .shell{margin:0 auto;padding:0}
            .paper{border-radius:0;border-left:0;border-right:0;box-shadow:none}
            .header{padding:16px;flex-direction:column;align-items:stretch;gap:14px}
            .meta{text-align:left;min-width:auto;width:100%}
            .chips{justify-content:flex-start}
            .grid{grid-template-columns:1fr;gap:12px;padding:12px;background:#fff}
            .items{padding:0 12px}
            .totals{padding:12px}
            .totals-box{max-width:none}
            .foot{flex-direction:column;align-items:stretch;padding:14px;gap:12px}
            .barcode{text-align:left}
            .no-print{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid var(--line);padding:12px 16px calc(12px + env(safe-area-inset-bottom));z-index:50;box-shadow:0 -8px 24px rgba(15,23,42,.12);margin:0;max-width:none}
            body{padding-bottom:76px}
            @media print{ body{padding-bottom:0} .no-print{display:none !important} }
        }
        /* thermal 58mm bluetooth — no background, pure black on white */
        @if ($mode === 'thermal')
        html,body{background:#fff}
        .shell{max-width:58mm;margin:0 auto;padding:0}
        .paper{border:0;box-shadow:none;border-radius:0;background:#fff}
        .header{padding:8px 6px 6px;flex-direction:column;align-items:center;text-align:center;border-bottom:1px dashed #000}
        .brand{flex-direction:column}
        .brand p{max-width:none}
        .meta{text-align:center;min-width:auto;width:100%}
        .code{font-size:16px}
        .chips{justify-content:center}
        .chip{background:#fff !important;color:#000 !important;border-color:#000 !important}
        .mark{background:#fff !important;color:#000 !important;border:1.5px solid #000}
        .grid{grid-template-columns:1fr;gap:6px;padding:6px;background:#fff;border-bottom:1px dashed #000}
        .card{padding:6px;background:#fff;border:1px solid #000;border-radius:0}
        .items{padding:0 6px}
        .tbl{font-size:10px}
        .tbl th{padding:6px 3px;border-bottom:1px solid #000;color:#000}
        .tbl td{padding:6px 3px;border-bottom:1px dashed #999}
        .totals{padding:6px}
        .totals-box{max-width:none;border:0;border-top:1px dashed #000;border-radius:0}
        .row{padding:6px 3px;background:#fff !important;color:#000 !important;border-bottom:1px dashed #999}
        .row.total{background:#fff !important;color:#000 !important;border-top:1.5px solid #000;border-bottom:1.5px solid #000}
        .note{padding:0 6px 6px}
        .note-box{background:#fff !important;border:1px dashed #000;color:#000;border-radius:0}
        .foot{flex-direction:column;align-items:stretch;padding:6px;text-align:center;background:#fff !important;border-top:1px dashed #000}
        .barcode{text-align:center}
        .barcode div:last-child{background:repeating-linear-gradient(90deg, #000 0 1.5px, transparent 1.5px 3px) !important;opacity:1 !important}
        .cut{display:block;border-color:#000}
        .cut span{background:#fff;color:#000}
        @page{size:58mm auto;margin:2mm}
        @media print{ *{ -webkit-print-color-adjust:economy !important; print-color-adjust:economy !important } }
        @endif
    </style>
</head>
<body @if($mode!=='thermal') onload="window.print()" @else onload="setTimeout(()=>window.print(),300)" @endif>
@php
    $customerNama = $order->hasCustomer?->customer_nama ?? $order->order_walkin_nama ?? '-';
    $customerTelepon = $order->hasCustomer?->customer_telepon ?? $order->order_walkin_telepon ?? '-';
    $laundryNama = $order->hasLaundry?->laundry_nama ?? config('website.name','Laundry');
    $laundryAlamat = $order->hasLaundry?->laundry_alamat ?? config('website.alamat','');
    $laundryTelp = $order->hasLaundry?->laundry_telepon ?? config('website.telepon','');
    $isTunai = ($order->order_metode_pembayaran?->value ?? 'tunai') === 'tunai';
    $statusNama = $order->hasStatus?->order_status_nama ?? '-';
@endphp
<div class="no-print" style="max-width:820px;margin:0 auto;padding:12px 16px;gap:10px;align-items:center;justify-content:space-between;">
    <a href="{{ route('order.getShow',['id'=>$order->order_id]) }}" style="font-size:13px;font-weight:700;color:#0f172a;border:1px solid #e2e8f0;background:#fff;padding:8px 12px;border-radius:999px;">← Kembali</a>
    <div style="display:flex;gap:8px;">
        <button onclick="window.print()" style="font-size:13px;font-weight:800;color:#fff;background:var(--primary);border:0;padding:10px 16px;border-radius:999px;cursor:pointer;">Cetak</button>
        <a href="{{ route('order.getStrukPdf',['id'=>$order->order_id]) }}" style="font-size:13px;font-weight:700;color:var(--primary);border:1px solid var(--primary);background:#fff;padding:10px 16px;border-radius:999px;">Unduh PDF</a>
    </div>
</div>

<div class="shell">
<div class="paper">
    <div class="header">
        <div class="brand">
            <div class="mark">L</div>
            <div>
                <h1>{{ $laundryNama }}</h1>
                @if($laundryAlamat || $laundryTelp)
                <p>{{ $laundryAlamat }}@if($laundryAlamat && $laundryTelp) · @endif{{ $laundryTelp }}</p>
                @else
                <p>{{ config('website.tagline','Layanan Laundry Profesional') }}</p>
                @endif
            </div>
        </div>
        <div class="meta">
            <div class="kicker">Struk Laundry</div>
            <div class="code">{{ $order->order_code }}</div>
            <div class="sub" style="font-size:12px;color:var(--muted)">{{ formatDate($order->created_at) }} · {{ $order->created_at->format('H:i') }} WIB</div>
            <div class="chips">
                <span class="chip {{ $isTunai ? 'tunai' : 'nontunai' }}">{{ $isTunai ? 'Tunai' : 'Non Tunai' }}</span>
                <span class="chip status">{{ $statusNama }}</span>
            </div>
        </div>
    </div>

    {{-- Grid Pelanggan & Cabang di-hide — info sudah di header Laundry Bersih --}}
    {{-- <div class="grid">...</div> --}}

    <div class="items">
        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:42%">Produk</th>
                    <th class="r">Harga</th>
                    <th class="r">Qty</th>
                    <th class="r">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->hasItems as $item)
                <tr>
                    <td>
                        <div class="name">{{ $item->order_item_nama_product }}</div>
                        <div class="satuan">per {{ $item->order_item_satuan }}</div>
                    </td>
                    <td class="r mono">{{ formatAngka($item->order_item_harga) }}</td>
                    <td class="r mono">{{ formatQty($item->order_item_qty) }}</td>
                    <td class="r mono" style="font-weight:800">{{ formatAngka($item->order_item_subtotal) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @php
        $diskonPrint = (float)($order->order_diskon ?? 0);
        if($diskonPrint <= 0 && (float)$order->order_subtotal > (float)$order->order_total){
            $diskonPrint = (float)$order->order_subtotal - (float)$order->order_total;
        }
    @endphp
    <div class="totals">
        <div class="totals-box">
            <div class="row"><span>Subtotal</span><span class="mono">{{ formatAngka($order->order_subtotal) }}</span></div>
            @if($diskonPrint > 0)
            <div class="row"><span>Diskon @if($order->order_id_discount) · Promo @endif</span><span class="mono" style="color:#16a34a">-{{ formatAngka($diskonPrint) }}</span></div>
            @endif
            <div class="row total"><span>Total</span><span class="mono">Rp {{ formatAngka($order->order_total) }}</span></div>
        </div>
    </div>

    @if ($order->order_catatan)
    <div class="note">
        <div class="note-box"><strong>Catatan:</strong> {{ $order->order_catatan }}</div>
    </div>
    @endif

    <div class="foot">
        <p>
            Terima kasih telah menggunakan layanan kami.<br>
            Simpan struk ini sebagai bukti pengambilan.
            @if($mode!=='thermal')<br><span style="color:var(--muted)">Dicetak {{ now()->format('d/m/Y H:i') }} WIB</span>@endif
        </p>
        <div class="barcode">
            {{-- Simple code128 as text fallback for print reliability --}}
            <div style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size:10px;letter-spacing:.12em;color:var(--muted);text-transform:uppercase">{{ $order->order_code }}</div>
            <div style="height:32px;margin-top:6px;background:repeating-linear-gradient(90deg, #0f172a 0 2px, transparent 2px 4px);border-radius:4px;opacity:.9"></div>
        </div>
    </div>
    <div class="cut"><span>gunting disini</span></div>
</div>
</div>
</body>
</html>
