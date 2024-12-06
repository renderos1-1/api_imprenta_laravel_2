import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/react-app.jsx'
            ],
            refresh: true,
        }),
        react(),
    ],
    server: {
        proxy: {
            // Proxy API requests to Laravel backend during development
            '/api': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false
            }
        }
    }
});
