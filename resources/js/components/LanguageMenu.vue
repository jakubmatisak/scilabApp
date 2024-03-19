<template>
  <v-menu>
    <template #activator="{ props }">
      <v-btn
        color="primary"
        variant="tonal"
        v-bind="props"
      >
        {{ currentLanguage.value }}
      </v-btn>
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
import { ref } from "vue";
import { loadLanguageAsync } from "laravel-vue-i18n";

const languages = [
    { value: "SK", label: "Slovenčina", i18n: "sk" },
    { value: "EN", label: "English", i18n: "en" },
];

const currentLanguage = ref(languages[0]);

const onLanguateSelected = (language) => {
    currentLanguage.value = language;
    loadLanguageAsync(language.i18n);
};
</script>
