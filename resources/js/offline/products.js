import { query, run } from './db.js';

export function getProducts(categoryId = null) {
    let sql = 'SELECT * FROM products';
    const params = [];
    if (categoryId) {
        sql += ' WHERE product_id_kategori = ?';
        params.push(categoryId);
    }
    sql += ' ORDER BY product_nama ASC';
    return query(sql, params);
}

export function searchProducts(keyword) {
    return query(
        'SELECT * FROM products WHERE product_nama LIKE ? ORDER BY product_nama ASC',
        [`%${keyword}%`]
    );
}

export function getProductById(id) {
    const results = query('SELECT * FROM products WHERE product_id = ?', [id]);
    return results[0] || null;
}

export function getCategories() {
    return query('SELECT * FROM categories ORDER BY name ASC');
}

export function getSatuan() {
    return query('SELECT * FROM satuan ORDER BY name ASC');
}

export function upsertProducts(products) {
    run('DELETE FROM products');
    for (const p of products) {
        run(
            `INSERT INTO products (product_id, product_nama, product_harga_jual, product_stok,
             product_id_satuan, product_id_kategori, product_foto, updated_at, sync_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'synced')`,
            [
                p.product_id, p.product_nama, p.product_harga_jual, p.product_stok,
                p.product_id_satuan, p.product_id_kategori, p.product_foto, p.updated_at,
            ]
        );
    }
}

export function upsertCategories(categories) {
    run('DELETE FROM categories');
    for (const c of categories) {
        run(
            'INSERT INTO categories (id, name, updated_at) VALUES (?, ?, ?)',
            [c.id, c.name, c.updated_at]
        );
    }
}

export function upsertSatuan(satuanList) {
    run('DELETE FROM satuan');
    for (const s of satuanList) {
        run(
            'INSERT INTO satuan (id, name, updated_at) VALUES (?, ?, ?)',
            [s.id, s.name, s.updated_at]
        );
    }
}

export function getProductCount() {
    const result = query('SELECT COUNT(*) as count FROM products');
    return result[0]?.count || 0;
}
