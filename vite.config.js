import { defineConfig } from 'vite';
import vue from "@vitejs/plugin-vue";
import Unimport from 'unimport/unplugin'
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        vue(),
        Unimport.vite({
            addons: {
                vueTemplate: true
            },
            imports: [{ name: 'push', from: 'notivue' }]
        }),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
