import db from './db.js';

export async function cacheDashboard(data) {
    const now = new Date().toISOString();
    const entries = Object.entries(data).map(([key, value]) => ({
        key,
        value,
        updated_at: now,
    }));
    await db.dashboard_cache.bulkPut(entries);
}

export async function getDashboardCache(key) {
    const entry = await db.dashboard_cache.get(key);
    return entry?.value ?? null;
}

export async function getOfflineStats() {
    const pending = await db.offline_orders.where('sync_status').equals('pending').toArray();
    const synced = await db.offline_orders.where('sync_status').equals('synced').toArray();
    const conflicts = await db.offline_orders.where('sync_status').equals('conflict').count();

    return {
        pending: {
            count: pending.length,
            total: pending.reduce((sum, o) => sum + (o.total || 0), 0),
        },
        synced: {
            count: synced.length,
            total: synced.reduce((sum, o) => sum + (o.total || 0), 0),
        },
        conflicts,
    };
}

export async function clearDashboardCache() {
    await db.dashboard_cache.clear();
}
