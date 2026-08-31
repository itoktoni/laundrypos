import { query, run } from './db.js';
import { getCart, clearCart } from './cart.js';

function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

export function createOfflineOrder({
    userId,
    laundryId,
    customerId = null,
    discount = 0,
    paymentMethod = 'cash',
    notes = '',
}) {
    const cart = getCart();
    if (cart.length === 0) {
        throw new Error('Cart is empty');
    }

    const total = cart.reduce((sum, item) => sum + item.subtotal, 0) - discount;
    const orderId = generateUUID();
    const now = new Date().toISOString();

    const items = cart.map((item) => ({
        product_id: item.product_id,
        product_nama: item.product_nama,
        qty: item.qty,
        price: item.price,
        subtotal: item.subtotal,
    }));

    run(
        `INSERT INTO offline_orders
         (id, user_id, laundry_id, customer_id, items, total, discount,
          payment_method, status, notes, created_at, updated_at, sync_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'offline_pending', ?, ?, ?, 'pending')`,
        [
            orderId,
            userId,
            laundryId,
            customerId,
            JSON.stringify(items),
            total,
            discount,
            paymentMethod,
            notes,
            now,
            now,
        ]
    );

    clearCart();

    return {
        id: orderId,
        total,
        items: items.length,
        status: 'offline_pending',
    };
}

export function getOfflineOrders(status = null) {
    let sql = 'SELECT * FROM offline_orders';
    const params = [];
    if (status) {
        sql += ' WHERE sync_status = ?';
        params.push(status);
    }
    sql += ' ORDER BY created_at DESC';
    const orders = query(sql, params);
    return orders.map((o) => ({
        ...o,
        items: JSON.parse(o.items),
    }));
}

export function getPendingOrders() {
    return getOfflineOrders('pending');
}

export function getPendingOrderCount() {
    const result = query(
        "SELECT COUNT(*) as count FROM offline_orders WHERE sync_status = 'pending'"
    );
    return result[0]?.count || 0;
}

export function markOrderSynced(clientId, serverOrderId) {
    run(
        `UPDATE offline_orders
         SET sync_status = 'synced', server_order_id = ?, updated_at = ?
         WHERE id = ?`,
        [serverOrderId, new Date().toISOString(), clientId]
    );
}

export function markOrderConflict(clientId, error) {
    run(
        `UPDATE offline_orders
         SET sync_status = 'conflict', sync_error = ?, updated_at = ?
         WHERE id = ?`,
        [error, new Date().toISOString(), clientId]
    );
}

export function getOrderById(id) {
    const results = query('SELECT * FROM offline_orders WHERE id = ?', [id]);
    if (results.length === 0) return null;
    return { ...results[0], items: JSON.parse(results[0].items) };
}

export function deleteOfflineOrder(id) {
    run('DELETE FROM offline_orders WHERE id = ?', [id]);
}
