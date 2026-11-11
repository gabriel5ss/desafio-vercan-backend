import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'Modules/Audit/resources/assets/js/app.js',
                'resources/css/ubold.css',
                'resources/js/ubold.js',
                'Modules/Supplier/resources/assets/js/app.js',
            ],
            assets: ['resources/images/ubold/**'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
            clientPort: 5173,
        },
        watch: {
            usePolling: true,
            interval: 250,
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
