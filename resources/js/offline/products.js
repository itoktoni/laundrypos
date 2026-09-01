import db from './db.js';

export async function getProducts(categoryId = null) {
    if (categoryId) {
        return db.products.where('product_id_kategori').equals(categoryId).toArray();
    }
    return db.products.toArray();
}

export async function searchProducts(keyword) {
    const lower = keyword.toLowerCase();
    return db.products
        .filter((p) => p.product_nama && p.product_nama.toLowerCase().includes(lower))
        .toArray();
}

export async function getProductById(id) {
    return db.products.get(id);
}

export async function getCategories() {
    return db.categories.toArray();
}

export async function getSatuan() {
    return db.satuan.toArray();
}

export async function upsertProducts(products) {
    await db.products.clear();
    await db.products.bulkAdd(products.map((p) => ({
        product_id: p.product_id,
        product_nama: p.product_nama,
        product_harga_jual: p.product_harga_jual ?? 0,
        product_stok: p.product_stok ?? 0,
        product_id_satuan: p.product_id_satuan ?? null,
        product_id_kategori: p.product_id_kategori ?? null,
        product_foto: p.product_foto ?? null,
        product_satuan: p.product_satuan ?? null,
        product_estimasi_jam: p.product_estimasi_jam ?? null,
        product_deskripsi: p.product_deskripsi ?? null,
        product_is_aktif: p.product_is_aktif ?? true,
        updated_at: p.updated_at ?? null,
    })));
}

export async function upsertCategories(categories) {
    await db.categories.clear();
    await db.categories.bulkAdd(categories.map((c) => ({
        id: c.id,
        name: c.name,
        updated_at: c.updated_at ?? null,
    })));
}

export async function upsertSatuan(satuanList) {
    await db.satuan.clear();
    await db.satuan.bulkAdd(satuanList.map((s) => ({
        id: s.id,
        name: s.name,
        updated_at: s.updated_at ?? null,
    })));
}

export async function getProductCount() {
    return db.products.count();
}
