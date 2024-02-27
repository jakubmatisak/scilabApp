<template>
  <v-toolbar
    class="rounded-t-lg"
    color="primary"
  >
    <v-btn
      v-if="backButton"
      icon
      to="/experiments"
    >
      <v-icon>mdi-arrow-left</v-icon>
    </v-btn>
    <v-toolbar-title class="text-h4">
      {{ title }}
    </v-toolbar-title>
    <v-spacer />
    <slot />
  </v-toolbar>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    backButton: {
        type: Boolean,
        default: false
    }
});
</script>

<style scoped>
.v-toolbar {
  border-bottom: 1px solid black;
}
</style>