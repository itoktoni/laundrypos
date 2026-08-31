const CACHE_NAME = 'laundry-pos-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    '/',
    '/dashboard',
    '/offline.html',
    '/manifest.json',
    '/favicon.ico',
    '/favicon.svg',
    '/js/sql-wasm-browser.wasm',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET') return;
    if (url.pathname.startsWith('/livewire/')) return;
    if (url.pathname.startsWith('/api/sync/')) return;

    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                })
                .catch(() => {
                    return caches.match(request).then((cached) => {
                        return cached || caches.match(OFFLINE_URL);
                    });
                })
        );
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) return cached;
            return fetch(request).then((response) => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                }
                return response;
            });
        }).catch(() => {
            if (request.destination === 'image') {
                return new Response(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect fill="#e5e7eb" width="100" height="100"/><text fill="#9ca3af" x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="14">Offline</text></svg>',
                    { headers: { 'Content-Type': 'image/svg+xml' } }
                );
            }
        })
    );
});

self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-orders') {
        event.waitUntil(syncPendingOrders());
    }
});

async function syncPendingOrders() {
    const clients = await self.clients.matchAll();
    clients.forEach((client) => {
        client.postMessage({ type: 'SYNC_ORDERS' });
    });
}
