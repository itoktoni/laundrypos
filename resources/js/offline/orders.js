import db from './db.js';
import { getCart, clearCart } from './cart.js';
import { generateId } from './db.js';

export async function createOfflineOrder({
    userId,
    laundryId,
    customerId = null,
    discount = 0,
    paymentMethod = 'cash',
    notes = '',
}) {
    const cart = await getCart();
    if (cart.length === 0) {
        throw new Error('Cart is empty');
    }

    const total = cart.reduce((sum, item) => sum + item.subtotal, 0) - discount;
    const orderId = generateId();
    const now = new Date().toISOString();

    const items = cart.map((item) => ({
        product_id: item.product_id,
        product_nama: item.product_nama,
        qty: item.qty,
        price: item.price,
        subtotal: item.subtotal,
    }));

    await db.offline_orders.add({
        id: orderId,
        user_id: userId,
        laundry_id: laundryId,
        customer_id: customerId,
        items,
        total,
        discount,
        payment_method: paymentMethod,
        status: 'offline_pending',
        notes,
        created_at: now,
        updated_at: now,
        sync_status: 'pending',
        server_order_id: null,
        sync_error: null,
    });

    await clearCart();

    return {
        id: orderId,
        total,
        items: items.length,
        status: 'offline_pending',
    };
}

export async function getOfflineOrders(status = null) {
    if (status) {
        return db.offline_orders.where('sync_status').equals(status).reverse().sortBy('created_at');
    }
    return db.offline_orders.orderBy('created_at').reverse().toArray();
}

export async function getPendingOrders() {
    return db.offline_orders.where('sync_status').equals('pending').toArray();
}

export async function getPendingOrderCount() {
    return db.offline_orders.where('sync_status').equals('pending').count();
}

export async function markOrderSynced(clientId, serverOrderId) {
    await db.offline_orders.update(clientId, {
        sync_status: 'synced',
        server_order_id: serverOrderId,
        updated_at: new Date().toISOString(),
    });
}

export async function markOrderConflict(clientId, error) {
    await db.offline_orders.update(clientId, {
        sync_status: 'conflict',
        sync_error: error,
        updated_at: new Date().toISOString(),
    });
}

export async function getOrderById(id) {
    return db.offline_orders.get(id);
}

export async function deleteOfflineOrder(id) {
    await db.offline_orders.delete(id);
}
