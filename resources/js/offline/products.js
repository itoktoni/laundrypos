import { query, run } from './db.js';

function safe(v) {
    return v === undefined ? null : v;
}

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
                safe(p.product_id), safe(p.product_nama), safe(p.product_harga_jual), safe(p.product_stok),
                safe(p.product_id_satuan), safe(p.product_id_kategori), safe(p.product_foto), safe(p.updated_at),
            ]
        );
    }
}

export function upsertCategories(categories) {
    run('DELETE FROM categories');
    for (const c of categories) {
        run(
            'INSERT INTO categories (id, name, updated_at) VALUES (?, ?, ?)',
            [safe(c.id), safe(c.name), safe(c.updated_at)]
        );
    }
}

export function upsertSatuan(satuanList) {
    run('DELETE FROM satuan');
    for (const s of satuanList) {
        run(
            'INSERT INTO satuan (id, name, updated_at) VALUES (?, ?, ?)',
            [safe(s.id), safe(s.name), safe(s.updated_at)]
        );
    }
}

export function getProductCount() {
    const result = query('SELECT COUNT(*) as count FROM products');
    return result[0]?.count || 0;
}
