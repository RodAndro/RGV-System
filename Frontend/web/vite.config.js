import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: false,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            ignored: [
                '**/Backend/public/images/**',
                '**/Backend/public/project-highlights/**',
            ],
        },
    },
    build: {
        outDir: '../../Backend/public/build',
        emptyOutDir: true,
        manifest: 'manifest.json',
    },
    plugins: [
        laravel({
            publicDirectory: '../../Backend/public',
            buildDirectory: 'build',
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                '../../Backend/resources/views/**',
                '../../Backend/app/View/Components/**',
                '../../Backend/routes/**',
            ],
        }),
    ],
});
