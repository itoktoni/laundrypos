import { query, run } from './db.js';

export function addToCart(productId, productName, price, qty = 1) {
    const existing = query(
        'SELECT * FROM cart WHERE product_id = ?',
        [productId]
    );

    if (existing.length > 0) {
        run(
            'UPDATE cart SET qty = qty + ? WHERE product_id = ?',
            [qty, productId]
        );
    } else {
        run(
            'INSERT INTO cart (product_id, product_nama, qty, price) VALUES (?, ?, ?, ?)',
            [productId, productName, qty, price]
        );
    }
}

export function updateCartQty(cartId, qty) {
    if (qty <= 0) {
        removeFromCart(cartId);
        return;
    }
    run('UPDATE cart SET qty = ? WHERE id = ?', [qty, cartId]);
}

export function removeFromCart(cartId) {
    run('DELETE FROM cart WHERE id = ?', [cartId]);
}

export function getCart() {
    return query(
        'SELECT *, (qty * price) as subtotal FROM cart ORDER BY added_at ASC'
    );
}

export function getCartTotal() {
    const result = query('SELECT SUM(qty * price) as total FROM cart');
    return result[0]?.total || 0;
}

export function getCartCount() {
    const result = query('SELECT SUM(qty) as count FROM cart');
    return result[0]?.count || 0;
}

export function clearCart() {
    run('DELETE FROM cart');
}

export function updateCartItemPrice(cartId, newPrice) {
    run('UPDATE cart SET price = ? WHERE id = ?', [newPrice, cartId]);
}
