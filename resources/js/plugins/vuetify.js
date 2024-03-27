import "vuetify/styles";
import { createVuetify } from "vuetify";
import { sk, en } from "vuetify/locale";
import { md3 } from "vuetify/blueprints";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";

const lightTheme = {
    dark: false,
    colors: {
        background: "#F4F6F9",
        surface: "#FFFFFF",
        primary: "#1867C0",
        secondary: "#48A9A6",
    },
};

const vuetify = createVuetify({
    components,
    directives,
    blueprint: md3,
    theme: {
        defaultTheme: "lightTheme",
        themes: {
            lightTheme,
        },
    },
    locale: {
        locale: "sk",
        fallback: "sk",
        messages: { sk, en },
    },
});

export default vuetify;
