<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $order->order_code }}</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: monospace; font-size: 13px; color: #000; }
    </style>
    @if ($mode === 'thermal')
    <style>
        @page { size: 80mm auto; margin: 3mm; }
        body { width: 74mm; font-size: 12px; }
        table { font-size: 11px; }
    </style>
    @else
    <style>
        body { width: 120mm; margin: 0 auto; }
    </style>
    @endif
</head>
<body onload="window.print()">
    @include('pdf.struk', ['order' => $order])
</body>
</html>
