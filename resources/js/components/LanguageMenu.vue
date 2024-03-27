<template>
  <v-menu>
    <template #activator="{ props }">
      <v-btn
        color="primary"
        icon="mdi-translate-variant"
        v-bind="props"
      />
    </template>
    <v-list>
      <v-list-item
        v-for="item of languages"
        :key="item.value"
        :value="item.value"
      >
        <v-list-item-title @click="onLanguateSelected(item)">
          {{ item.label }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<script setup>
import { loadLanguageAsync } from "laravel-vue-i18n";
import { useLocale } from "vuetify";

const languages = [
    { value: "SK", label: "Slovenčina", i18n: "sk" },
    { value: "EN", label: "English", i18n: "en" },
];

const { current } = useLocale();

const onLanguateSelected = (language) => {
    loadLanguageAsync(language.i18n);
    current.value = language.i18n;
};
</script>
