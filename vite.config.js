import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig(({ isSsrBuild }) => ({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            ssr: 'resources/js/ssr.jsx',
            refresh: true,
        }),
        react(),
    ],
    build: {
        ...(!isSsrBuild && {
            rollupOptions: {
                output: {
                    manualChunks: {
                        'vendor-inertia': ['react', 'react-dom', '@inertiajs/react'],
                        'vendor-headlessui': ['@headlessui/react'],
                    },
                },
            },
        }),
        chunkSizeWarningLimit: 500,
    },
    ssr: {
        noExternal: ['@inertiajs/react'],
    },
}));
