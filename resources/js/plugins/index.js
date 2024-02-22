import { i18nVue } from "laravel-vue-i18n";
import { VueQueryPlugin } from "@tanstack/vue-query";
import api from "../api/api.js";
import i18nConfig from "./i18n";
import pinia from "./pinia";
import router from "../router";
import VueAxios from "vue-axios";
import vuetify from "./vuetify";

export const registerPlugins = (app) => {
    app.use(pinia)
        .use(vuetify)
        .use(router)
        .use(VueAxios, api)
        .use(VueQueryPlugin)
        .use(i18nVue, i18nConfig);
};
