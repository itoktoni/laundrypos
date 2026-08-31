import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import { copyFileSync, mkdirSync, existsSync } from 'fs';
import { resolve } from 'path';

export default defineConfig(() => {
    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/notifications.js'],
                refresh: true,
            }),
            tailwindcss(),
            {
                name: 'copy-sql-wasm',
                buildStart() {
                    const src = resolve('node_modules/sql.js/dist/sql-wasm.wasm');
                    const dest = resolve('public/js/sql-wasm.wasm');
                    if (!existsSync(resolve('public/js'))) {
                        mkdirSync(resolve('public/js'), { recursive: true });
                    }
                    if (existsSync(src)) {
                        copyFileSync(src, dest);
                    }
                },
            },
        ],
        server: {
            cors: true,
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
            proxy: {
                '/api': {
                    target: 'http://127.0.0.1:8000',
                    changeOrigin: true,
                    secure: false,
                },
            },
        },
    };
});
