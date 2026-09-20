import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/base.css',
                'resources/css/admin/produk.css',
                'resources/css/admin/user.css',
                'resources/css/about.css',
                'resources/css/auth.css',
                'resources/css/basket-bar.css',
                'resources/css/checkout.css',
                'resources/css/home.css',
                'resources/css/menu.css',
                'resources/css/order-confirmed.css',
                'resources/css/product.css',
                'resources/css/settings.css',
                'resources/js/app.js',
                'resources/js/basket.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});