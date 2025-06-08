import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path'; // Thêm dòng này

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/js'),            // Thêm alias này để dùng @
            '@core': path.resolve(__dirname, 'resources/js/core'),   // Dùng cho @core/...
            '@layouts': path.resolve(__dirname, 'resources/js/layouts'),
            '@styles': path.resolve(__dirname, 'resources/js/styles'),
        },
    },
});
