import { query, run, generateId } from './db.js';
import { upsertProducts, upsertCategories, upsertSatuan } from './products.js';
import { getPendingOrders, markOrderSynced, markOrderConflict } from './orders.js';

const API_BASE = '/api/sync';

export function isOnline() {
    return navigator.onLine;
}

export function getLastSyncTime() {
    const result = query("SELECT value FROM sync_meta WHERE key = 'last_sync_at'");
    return result[0]?.value || null;
}

export function setLastSyncTime(time) {
    run(
        "INSERT OR REPLACE INTO sync_meta (key, value, updated_at) VALUES ('last_sync_at', ?, ?)",
        [time, new Date().toISOString()]
    );
}

export function getDeviceId() {
    let result = query("SELECT value FROM sync_meta WHERE key = 'device_id'");
    if (!result[0]?.value) {
        const deviceId = generateId();
        run(
            "INSERT OR REPLACE INTO sync_meta (key, value, updated_at) VALUES ('device_id', ?, ?)",
            [deviceId, new Date().toISOString()]
        );
        return deviceId;
    }
    return result[0].value;
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
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
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `API error: ${response.status} ${response.statusText}`);
    }

    return response.json();
}

export async function pullProducts() {
    const data = await apiRequest('/products');
    upsertProducts(data.products);
    setLastSyncTime(data.last_sync_at);
    return data.products.length;
}

export async function pullCategories() {
    const data = await apiRequest('/categories');
    upsertCategories(data.categories);
    return data.categories.length;
}

export async function pullSatuan() {
    const data = await apiRequest('/satuan');
    upsertSatuan(data.satuan);
    return data.satuan.length;
}

export async function pullCustomers() {
    const data = await apiRequest('/customers');
    return data.customers;
}

export async function pushOrders() {
    const pending = getPendingOrders();
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
        markOrderSynced(item.client_id, item.server_order_id);
    }
    for (const item of result.failed) {
        markOrderConflict(item.client_id, item.error);
    }

    return { synced: result.synced.length, failed: result.failed.length };
}

export async function syncAll() {
    if (!isOnline()) {
        return { success: false, reason: 'offline' };
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
        console.error('Sync failed:', error);
        return { success: false, error: error.message };
    }
}

let syncInterval = null;

export function startAutoSync(intervalMs = 5 * 60 * 1000) {
    if (isOnline()) {
        syncAll().catch(console.error);
    }

    window.addEventListener('online', () => {
        console.log('Connection restored, syncing...');
        syncAll().catch(console.error);
    });

    syncInterval = setInterval(() => {
        if (isOnline()) {
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
