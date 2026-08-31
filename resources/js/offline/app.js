import { initDB } from './db.js';
import { getProducts, searchProducts, getProductById } from './products.js';
import { addToCart, updateCartQty, removeFromCart, getCart, getCartTotal, getCartCount, clearCart } from './cart.js';
import { createOfflineOrder, getOfflineOrders, getPendingOrderCount, getOrderById } from './orders.js';
import { syncAll, isOnline, startAutoSync, stopAutoSync, getLastSyncTime } from './sync.js';

let currentMode = 'unknown';
let onModeChange = null;

export async function initOfflineMode() {
    await initDB();

    const deviceId = localStorage.getItem('device_id') || crypto.randomUUID();
    localStorage.setItem('device_id', deviceId);

    if (isOnline()) {
        await switchToOnline();
    } else {
        await switchToOffline();
    }

    window.addEventListener('online', () => switchToOnline());
    window.addEventListener('offline', () => switchToOffline());

    console.log('Offline mode initialized. Current mode:', currentMode);
}

async function switchToOnline() {
    currentMode = 'online';
    console.log('Switched to ONLINE mode');

    startAutoSync();
    updateStatusBanner('online');

    const result = await syncAll();
    console.log('Initial sync result:', result);

    if (onModeChange) onModeChange('online', result);
}

async function switchToOffline() {
    currentMode = 'offline';
    console.log('Switched to OFFLINE mode');

    stopAutoSync();
    updateStatusBanner('offline');

    if (onModeChange) onModeChange('offline', null);
}

function updateStatusBanner(mode) {
    let banner = document.getElementById('offline-banner');
    if (!banner) {
        banner = document.createElement('div');
        banner.id = 'offline-banner';
        banner.style.cssText = `
            position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
            padding: 8px 16px; text-align: center; font-size: 14px;
            font-weight: 500; transition: transform 0.3s ease;
        `;
        document.body.prepend(banner);
    }

    if (mode === 'offline') {
        banner.style.backgroundColor = '#fef3c7';
        banner.style.color = '#92400e';
        banner.innerHTML = '<span class="mr-2">📡</span> Anda sedang offline. Mode offline aktif.';
        banner.style.transform = 'translateY(0)';
    } else {
        banner.style.backgroundColor = '#d1fae5';
        banner.style.color = '#065f46';
        banner.innerHTML = '<span class="mr-2">✅</span> Online. Semua fitur tersedia.';
        banner.style.transform = 'translateY(0)';

        setTimeout(() => {
            banner.style.transform = 'translateY(-100%)';
        }, 3000);
    }
}

export function getMode() {
    return currentMode;
}

export function onModeChangeCallback(callback) {
    onModeChange = callback;
}

export {
    getProducts,
    searchProducts,
    getProductById,
    addToCart,
    updateCartQty,
    removeFromCart,
    getCart,
    getCartTotal,
    getCartCount,
    clearCart,
    createOfflineOrder,
    getOfflineOrders,
    getPendingOrderCount,
    getOrderById,
    syncAll,
    isOnline,
    getLastSyncTime,
};
