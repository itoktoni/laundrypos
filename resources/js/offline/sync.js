import db, { generateId } from './db.js';
import { upsertProducts, upsertCategories, upsertSatuan } from './products.js';
import { getPendingOrders, markOrderSynced, markOrderConflict } from './orders.js';

const API_BASE = '/api/sync';

export function isOnline() {
    return navigator.onLine;
}

export async function getLastSyncTime() {
    const entry = await db.sync_meta.get('last_sync_at');
    return entry?.value || null;
}

export async function setLastSyncTime(time) {
    await db.sync_meta.put({
        key: 'last_sync_at',
        value: time,
        updated_at: new Date().toISOString(),
    });
}

export async function getDeviceId() {
    const entry = await db.sync_meta.get('device_id');
    if (entry?.value) return entry.value;

    const deviceId = generateId();
    await db.sync_meta.put({
        key: 'device_id',
        value: deviceId,
        updated_at: new Date().toISOString(),
    });
    return deviceId;
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
}

function isAuthenticated() {
    return !!document.querySelector('meta[name="user-id"]')?.content;
}

async function apiRequest(path, options = {}) {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
        ...options.headers,
    };

    const response = await fetch(`${API_BASE}${path}`, {
        credentials: 'same-origin',
        ...options,
        headers,
    });

    if (!response.ok) {
        if (response.status === 401) {
            const err = new Error('Unauthenticated');
            err.status = 401;
            throw err;
        }
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `API error: ${response.status} ${response.statusText}`);
    }

    return response.json();
}

export async function pullProducts() {
    const data = await apiRequest('/products');
    await upsertProducts(data.products);
    await setLastSyncTime(data.last_sync_at);
    return data.products.length;
}

export async function pullCategories() {
    const data = await apiRequest('/categories');
    await upsertCategories(data.categories);
    return data.categories.length;
}

export async function pullSatuan() {
    const data = await apiRequest('/satuan');
    await upsertSatuan(data.satuan);
    return data.satuan.length;
}

export async function pullCustomers() {
    const data = await apiRequest('/customers');
    return data.customers;
}

export async function pushOrders() {
    const pending = await getPendingOrders();
    if (pending.length === 0) return { synced: 0, failed: 0 };

    const payload = {
        orders: pending.map((o) => ({
            id: o.id,
            customer_id: o.customer_id,
            items: o.items,
            total: o.total,
            discount: o.discount,
            payment_method: o.payment_method,
            notes: o.notes,
            created_at: o.created_at,
        })),
    };

    const result = await apiRequest('/orders', {
        method: 'POST',
        body: JSON.stringify(payload),
    });

    for (const item of result.synced) {
        await markOrderSynced(item.client_id, item.server_order_id);
    }
    for (const item of result.failed) {
        await markOrderConflict(item.client_id, item.error);
    }

    return { synced: result.synced.length, failed: result.failed.length };
}

export async function syncAll() {
    if (!isOnline()) {
        return { success: false, reason: 'offline' };
    }
    if (!isAuthenticated()) {
        return { success: false, reason: 'unauthenticated' };
    }

    try {
        const pushResult = await pushOrders();

        const productCount = await pullProducts();
        const categoryCount = await pullCategories();
        const satuanCount = await pullSatuan();

        return {
            success: true,
            pushed: pushResult.synced,
            pulled: { products: productCount, categories: categoryCount, satuan: satuanCount },
            timestamp: new Date().toISOString(),
        };
    } catch (error) {
        if (error.status === 401) {
            return { success: false, reason: 'unauthenticated' };
        }
        console.error('Sync failed:', error);
        return { success: false, error: error.message };
    }
}

let syncInterval = null;

export function startAutoSync(intervalMs = 5 * 60 * 1000) {
    if (!isAuthenticated()) return;
    if (isOnline()) {
        syncAll().catch(console.error);
    }

    window.addEventListener('online', () => {
        if (!isAuthenticated()) return;
        console.log('Connection restored, syncing...');
        syncAll().catch(console.error);
    });

    syncInterval = setInterval(() => {
        if (isOnline() && isAuthenticated()) {
            syncAll().catch(console.error);
        }
    }, intervalMs);
}

export function stopAutoSync() {
    if (syncInterval) {
        clearInterval(syncInterval);
        syncInterval = null;
    }
}
