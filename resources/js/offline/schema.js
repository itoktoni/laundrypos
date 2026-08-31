export const SCHEMA = `
CREATE TABLE IF NOT EXISTS products (
    product_id INTEGER PRIMARY KEY,
    product_nama TEXT NOT NULL,
    product_harga_jual REAL DEFAULT 0,
    product_stok INTEGER DEFAULT 0,
    product_id_satuan INTEGER,
    product_id_kategori INTEGER,
    product_foto TEXT,
    updated_at TEXT,
    sync_status TEXT DEFAULT 'synced'
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS satuan (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS customers (
    customer_id INTEGER PRIMARY KEY,
    customer_nama TEXT,
    customer_telepon TEXT,
    customer_alamat TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS offline_orders (
    id TEXT PRIMARY KEY,
    user_id INTEGER NOT NULL,
    laundry_id INTEGER NOT NULL,
    customer_id INTEGER,
    items JSON NOT NULL,
    total REAL NOT NULL,
    discount REAL DEFAULT 0,
    payment_method TEXT DEFAULT 'cash',
    payment_status TEXT DEFAULT 'pending',
    status TEXT DEFAULT 'offline_pending',
    notes TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    sync_status TEXT DEFAULT 'pending',
    server_order_id INTEGER,
    sync_error TEXT
);

CREATE TABLE IF NOT EXISTS cart (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    product_nama TEXT,
    qty INTEGER DEFAULT 1,
    price REAL DEFAULT 0,
    added_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS sync_meta (
    key TEXT PRIMARY KEY,
    value TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS dashboard_cache (
    key TEXT PRIMARY KEY,
    value JSON,
    updated_at TEXT
);
`;

export const SEED_DATA = {
    sync_meta: [
        { key: 'last_sync_at', value: null },
        { key: 'sync_version', value: '0' },
        { key: 'device_id', value: null },
    ],
};
