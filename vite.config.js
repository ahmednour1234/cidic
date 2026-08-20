import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/site.css',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/js/cv-thumbs.js',
            ],
            refresh: true,
        }),
    ],
});
