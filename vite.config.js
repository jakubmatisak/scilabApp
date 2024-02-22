import { defineConfig } from "vite";
import vuetify, {transformAssetUrls} from 'vite-plugin-vuetify';
import laravel from "laravel-vite-plugin";
import eslintPlugin from "vite-plugin-eslint";
import vue from "@vitejs/plugin-vue";
import i18n from "laravel-vue-i18n/vite";
import {fileURLToPath, URL} from 'node:url';

export default defineConfig({
    define: {'process.env': {}},
    plugins: [
        vue({
            template: {transformAssetUrls}
        }),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        i18n(),
        eslintPlugin({cache: false, fix: true}),
        vuetify({
            autoImport: true,
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js/', import.meta.url))
        },
        extensions: [
            '.js',
            '.json',
            '.jsx',
            '.mjs',
            '.ts',
            '.tsx',
            '.vue',
        ],
    },
    server: {
        port: 3000,
    },
    base: "./"
});
