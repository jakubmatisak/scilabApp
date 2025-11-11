<template>
  <v-app-bar elevation="2">
    <template #prepend>
      <v-app-bar-nav-icon
        variant="tonal"
        @click.prevent="emit('toggle')"
      />
    </template>
    <template #title>
      <v-app-bar-title class="font-weight-bold">
        {{ $t("Title") }}
      </v-app-bar-title>
    </template>
    <template #append>
      <v-btn
        class="mr-2"
        color="primary"
        density="compact"
        icon="mdi-information-outline"
        @click="downloadDocs"
      />
      <v-btn
        color="primary"
        density="compact"
        icon="mdi-api"
        @click="onApiClick"
      />
      <theme-toggle />
      <language-menu />
    </template>
  </v-app-bar>
</template>

<script setup>
import LanguageMenu from "./LanguageMenu.vue";
import ThemeToggle from "./ThemeToggle.vue";

const emit = defineEmits(["toggle"]);
const onApiClick = () => {
    const apiDocumentationUrl = import.meta.env.VITE_API_URL + "documentation";
    window.open(apiDocumentationUrl, "_blank");
};

const downloadDocs = () => {
    const apiDocumentationUrl = import.meta.env.VITE_API_URL + "docs/download";
    window.open(apiDocumentationUrl, "_blank");
};
</script>
