import { defineConfig } from "vite";
import vuetify, { transformAssetUrls } from "vite-plugin-vuetify";
import laravel from "laravel-vite-plugin";
import eslintPlugin from "vite-plugin-eslint";
import vue from "@vitejs/plugin-vue";
import i18n from "laravel-vue-i18n/vite";
import fs from "fs";

export default defineConfig(({ mode }) => {
    const isProduction = mode === "production";
    const host = "site19.webte.fei.stuba.sk";

    return {
        plugins: [
            vue({
                template: {
                    transformAssetUrls,
                },
                isProduction,
            }),
            laravel({
                input: ["resources/js/app.js"],
                refresh: true,
                detectTls: isProduction && host,
            }),
            i18n(),
            eslintPlugin({ cache: false, fix: true }),
            vuetify({
                autoImport: true,
            }),
        ],
        resolve: {
            alias: {
                "@": "/resources/js",
            },
            extensions: [".js", ".json", ".jsx", ".mjs", ".ts", ".tsx", ".vue"],
        },
        server: {
            host: true,
            port: 3000,
            https: isProduction && {
                cert: fs.readFileSync(
                    "./docker/prod/certificates/webte_fei_stuba_sk.pem"
                ),
                key: fs.readFileSync(
                    "./docker/prod/certificates/webte.fei.stuba.sk.key"
                ),
            },
            hmr: isProduction ? false : { port: 3000 },
        },
        base: "./",
    };
});
