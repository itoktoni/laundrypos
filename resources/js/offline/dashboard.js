import { query, run } from './db.js';

export function cacheDashboard(data) {
    const now = new Date().toISOString();
    for (const [key, value] of Object.entries(data)) {
        run(
            'INSERT OR REPLACE INTO dashboard_cache (key, value, updated_at) VALUES (?, ?, ?)',
            [key, JSON.stringify(value), now]
        );
    }
}

export function getDashboardCache(key) {
    const result = query('SELECT value FROM dashboard_cache WHERE key = ?', [key]);
    if (result.length === 0) return null;
    return JSON.parse(result[0].value);
}

export function getOfflineStats() {
    const pendingOrders = query(
        "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total FROM offline_orders WHERE sync_status = 'pending'"
    );
    const syncedOrders = query(
        "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total FROM offline_orders WHERE sync_status = 'synced'"
    );
    const conflictOrders = query(
        "SELECT COUNT(*) as count FROM offline_orders WHERE sync_status = 'conflict'"
    );

    return {
        pending: pendingOrders[0] || { count: 0, total: 0 },
        synced: syncedOrders[0] || { count: 0, total: 0 },
        conflicts: conflictOrders[0]?.count || 0,
    };
}

export function clearDashboardCache() {
    run('DELETE FROM dashboard_cache');
}
