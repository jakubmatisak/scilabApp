const i18nConfig = {
    lang: "sk",
    resolve: async (lang) => {
        const langs = import.meta.glob("../../../lang/*.json");

        try {
            const langModule = await langs[`../../../lang/${lang}.json`]();
            return langModule;
        } catch (error) {
            console.error(
                `Could not load ${lang}, falling back to default language.`
            );
            const fallbackLang = "sk";
            const fallbackLangModule = await langs[
                `../../../lang/${fallbackLang}.json`
            ]();
            return fallbackLangModule;
        }
    },
};

export default i18nConfig;
