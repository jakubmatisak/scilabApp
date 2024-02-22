<template>
  <v-main>
    <v-container class="h-100 py-5 w-100">
      <v-container
        class="bg-surface content-container elevation-3 h-100 rounded-lg"
      >
        <slot />
      </v-container>
    </v-container>
  </v-main>
</template>

<style scoped>
  .content-container {
    max-width: 90%;
  }
</style>