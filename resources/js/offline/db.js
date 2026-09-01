import Dexie from 'dexie';

const db = new Dexie('LaundryPOS');

db.version(1).stores({
    products: 'product_id, product_nama, product_id_kategori',
    categories: 'id',
    satuan: 'id',
    customers: 'customer_id',
    offline_orders: 'id, sync_status, created_at',
    cart: '++id, product_id',
    sync_meta: 'key',
    dashboard_cache: 'key',
});

export default db;

export function generateId() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}
