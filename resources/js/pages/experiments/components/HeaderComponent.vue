<template>
  <v-toolbar
    class="rounded-t-lg"
    color="primary"
  >
    <v-btn
      v-if="backButton"
      :density="width < 960 ? 'comfortable' : 'default'"
      icon
      :size="width < 600 ? 'small' : 'default'"
      to="/experiments"
    >
      <v-icon>mdi-arrow-left</v-icon>
    </v-btn>
    <v-toolbar-title class="text-md-h4 text-sm-h5">
      {{ title }}
    </v-toolbar-title>
    <slot />
  </v-toolbar>
</template>

<script setup>
import { useWindowSize } from "@vueuse/core";

defineProps({
    title: {
        type: String,
        required: true,
    },
    backButton: {
        type: Boolean,
        default: false,
    },
});

const { width } = useWindowSize();
</script>
