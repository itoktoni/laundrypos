<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="offline-enabled" content="{{ (\App\Models\WebsiteSetting::merged()['offline_enabled'] ?? config('website.offline_enabled', true)) ? 'true' : 'false' }}"/>
    <meta name="notification-enabled" content="{{ config('langkahkecil.notification_enable') ? 'true' : 'false' }}"/>
    @if(config('centrifugo.url'))
    <meta name="centrifugo-url" content="{{ str_replace(['http://', 'https://'], ['ws://', 'wss://'], config('centrifugo.url')) }}/connection/websocket"/>
    @endif
    @auth
    <meta name="user-id" content="{{ auth()->id() }}"/>
    @endauth
    @php
        $faviconUrl = \App\Models\WebsiteSetting::fileUrl(\App\Models\WebsiteSetting::merged()['favicon'] ?? null) ?? '/favicon.ico';
    @endphp
    <title>{{ config('website.name', $title ?? 'CMS') }}</title>
    <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
    {{-- Fonts self-hosted via @fontsource (offline-ready) + CDN fallback untuk Material Symbols --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/notifications.js'])
    @livewireStyles
    <script src="{{ asset('js/table.js') }}"></script>
    {{-- Dynamic theme color overrides from website settings --}}
    <style>
        :root {
            --color-primary: {{ \App\Models\WebsiteSetting::primaryColor() }};
        }
    </style>
    @include('components.printer-js')
    @stack('styles')
</head>
