<template>
  <v-btn
    color="primary"
    icon="mdi-theme-light-dark"
    @click="onThemeToggle"
  />
</template>

<script setup>
import { ref } from "vue";
import { useTheme } from "vuetify";

const theme = useTheme();
const currentTheme = ref("lightTheme");

const onThemeToggle = () => {
    const newTheme =
        currentTheme.value === "lightTheme" ? "dark" : "lightTheme";
    theme.global.name.value = newTheme;
    currentTheme.value = newTheme;
};
</script>
