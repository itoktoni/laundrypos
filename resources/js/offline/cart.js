import db from './db.js';

export async function addToCart(productId, productName, price, qty = 1) {
    const existing = await db.cart.where('product_id').equals(productId).first();

    if (existing) {
        await db.cart.update(existing.id, { qty: existing.qty + qty });
    } else {
        await db.cart.add({
            product_id: productId,
            product_nama: productName,
            qty,
            price,
            added_at: new Date().toISOString(),
        });
    }
}

export async function updateCartQty(cartId, qty) {
    if (qty <= 0) {
        await removeFromCart(cartId);
        return;
    }
    await db.cart.update(cartId, { qty });
}

export async function removeFromCart(cartId) {
    await db.cart.delete(cartId);
}

export async function getCart() {
    const items = await db.cart.toArray();
    return items.map((item) => ({
        ...item,
        subtotal: item.qty * item.price,
    }));
}

export async function getCartTotal() {
    const items = await db.cart.toArray();
    return items.reduce((sum, item) => sum + item.qty * item.price, 0);
}

export async function getCartCount() {
    const items = await db.cart.toArray();
    return items.reduce((sum, item) => sum + item.qty, 0);
}

export async function clearCart() {
    await db.cart.clear();
}

export async function updateCartItemPrice(cartId, newPrice) {
    await db.cart.update(cartId, { price: newPrice });
}
