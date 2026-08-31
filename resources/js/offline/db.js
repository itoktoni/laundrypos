import initSqlJs from 'sql.js';
import { SCHEMA, SEED_DATA } from './schema.js';

let db = null;
let SQL = null;

export async function initDB() {
    if (db) return db;

    SQL = await initSqlJs({
        locateFile: (file) => `/js/${file}`,
    });

    const savedDB = await loadFromIndexedDB();
    if (savedDB) {
        db = new SQL.Database(new Uint8Array(savedDB));
    } else {
        db = new SQL.Database();
        db.run(SCHEMA);
        seedDefaults();
        await saveToIndexedDB();
    }

    return db;
}

export function getDB() {
    if (!db) throw new Error('Database not initialized. Call initDB() first.');
    return db;
}

export function query(sql, params = []) {
    const stmt = db.prepare(sql);
    if (params.length) stmt.bind(params);
    const results = [];
    while (stmt.step()) {
        results.push(stmt.getAsObject());
    }
    stmt.free();
    return results;
}

export function run(sql, params = []) {
    db.run(sql, params);
    saveToIndexedDB();
}

export function runTransaction(fn) {
    db.run('BEGIN TRANSACTION');
    try {
        fn(db);
        db.run('COMMIT');
        saveToIndexedDB();
    } catch (e) {
        db.run('ROLLBACK');
        throw e;
    }
}

function seedDefaults() {
    for (const [table, rows] of Object.entries(SEED_DATA)) {
        for (const row of rows) {
            const cols = Object.keys(row).join(', ');
            const placeholders = Object.keys(row).map(() => '?').join(', ');
            const values = Object.values(row);
            run(`INSERT OR IGNORE INTO ${table} (${cols}) VALUES (${placeholders})`, values);
        }
    }
}

const DB_NAME = 'laundry-pos-sqlite';
const DB_STORE = 'database';
const DB_KEY = 'main';

async function loadFromIndexedDB() {
    return new Promise((resolve) => {
        const request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = (e) => {
            e.target.result.createObjectStore(DB_STORE);
        };
        request.onsuccess = (e) => {
            const idb = e.target.result;
            const tx = idb.transaction(DB_STORE, 'readonly');
            const store = tx.objectStore(DB_STORE);
            const get = store.get(DB_KEY);
            get.onsuccess = () => resolve(get.result || null);
            get.onerror = () => resolve(null);
        };
        request.onerror = () => resolve(null);
    });
}

async function saveToIndexedDB() {
    if (!db) return;
    const data = db.export();
    const buffer = data.buffer;
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = (e) => {
            e.target.result.createObjectStore(DB_STORE);
        };
        request.onsuccess = (e) => {
            const idb = e.target.result;
            const tx = idb.transaction(DB_STORE, 'readwrite');
            const store = tx.objectStore(DB_STORE);
            store.put(buffer, DB_KEY);
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        };
        request.onerror = () => reject(request.error);
    });
}

export function exportDB() {
    if (!db) return null;
    return db.export();
}

export function importDB(data) {
    db = new SQL.Database(new Uint8Array(data));
    saveToIndexedDB();
}
