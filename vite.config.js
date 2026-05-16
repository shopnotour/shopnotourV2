import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { createVuePlugin } from 'vite-plugin-vue2';
import vue from '@vitejs/plugin-vue2';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/js/flight-search-app.js',
            ],
            refresh: true,
        }),
    ],
});
