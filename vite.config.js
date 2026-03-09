import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/analysis.css',
                'resources/css/app.css',
                'resources/css/genome.css',
                'resources/css/loading.css',
                'resources/css/particles.css',
                'resources/css/repository-card.css',
                'resources/js/app.js',
                'resources/js/loading.js',
                'resources/js/genome.js',
                'resources/js/particles.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
