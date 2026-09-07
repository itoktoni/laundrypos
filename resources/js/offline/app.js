import './db.js';
import { getProducts, searchProducts, getProductById } from './products.js';
import { addToCart, updateCartQty, removeFromCart, getCart, getCartTotal, getCartCount, clearCart } from './cart.js';
import { createOfflineOrder, getOfflineOrders, getPendingOrderCount, getOrderById } from './orders.js';
import { syncAll, isOnline, startAutoSync, stopAutoSync, getLastSyncTime } from './sync.js';

let currentMode = 'unknown';
let onModeChange = null;

export async function initOfflineMode() {
    const offlineEnabled = document.querySelector('meta[name="offline-enabled"]')?.content !== 'false';
    if (!offlineEnabled) {
        console.log('Offline mode disabled via settings');
        updateHeaderDot('offline');
        // Tandai header sebagai sync mati (tooltip)
        const header = document.getElementById('app-header');
        if (header) header.title = 'Sync offline dimatikan';
        return;
    }

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

    updateStatusBanner('online');

    const isAuth = !!document.querySelector('meta[name="user-id"]')?.content;
    if (isAuth) {
        startAutoSync();
        const result = await syncAll();
        // Jangan spam console jika hanya unauthenticated (halaman login)
        if (result.reason !== 'unauthenticated') {
            console.log('Initial sync result:', result);
        }
        if (onModeChange) onModeChange('online', result);
    } else {
        console.log('Skipping sync: not authenticated');
        if (onModeChange) onModeChange('online', { success: false, reason: 'unauthenticated' });
    }
}

async function switchToOffline() {
    currentMode = 'offline';
    console.log('Switched to OFFLINE mode');

    stopAutoSync();
    updateStatusBanner('offline');

    if (onModeChange) onModeChange('offline', null);
}

function updateStatusBanner(mode) {
    // Top persistent banner is handled by Alpine component #offline-status-indicator; just sync pending count
    refreshPendingCount();
    updateHeaderDot(mode);
}

function updateHeaderDot(mode) {
    const header = document.getElementById('app-header');
    if (header) {
        // hijau saat online, merah saat offline — hanya border bawah
        header.style.borderBottomColor = mode === 'offline' ? '#dc2626' : '#16a34a';
        header.style.borderBottomWidth = '3px';
    }
    // Keep bottom-right pill in sync if present
    const dot = document.getElementById('header-offline-dot');
    if (dot) {
        const circle = dot.querySelector('span:first-child');
        const label = dot.querySelector('.label');
        dot.classList.remove('hidden');
        if (mode === 'offline') {
            dot.className = 'hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border bg-amber-100 border-amber-300 text-amber-800';
            if (circle) circle.className = 'w-2 h-2 rounded-full bg-amber-500';
            if (label) label.textContent = 'Offline';
        } else {
            dot.className = 'hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border bg-success/10 border-success/20 text-success';
            if (circle) circle.className = 'w-2 h-2 rounded-full bg-success animate-pulse';
            if (label) label.textContent = 'Online';
        }
    }
}

async function refreshPendingCount() {
    try {
        const count = await getPendingOrderCount();
        localStorage.setItem('offline_pending', String(count));
        window.dispatchEvent(new CustomEvent('offline-pending', { detail: count }));
    } catch (e) {}
    // Also update after syncs periodically
}

setInterval(refreshPendingCount, 5000);

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
