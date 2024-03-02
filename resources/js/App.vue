<template>
  <v-app>
    <router-view />
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      rounded="pill"
    >
      {{ snackbar.message }}
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { useNotificationStore } from '@/stores/NotificationService';

const {snackbar} = useNotificationStore();
</script>
