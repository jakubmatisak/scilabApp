<template>
  <div>
    <v-toolbar class="rounded-t-xl">
      <v-btn
        icon
        to="/experiments"
      >
        <v-icon>mdi-arrow-left</v-icon>
      </v-btn>
      <v-toolbar-title class="text-h4">
        {{ title }}
      </v-toolbar-title>
      <v-spacer />
      <v-btn icon>
        <v-icon>mdi-content-save</v-icon>
      </v-btn>
    </v-toolbar>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const isEditView = ref(route.path.includes('edit'));
const title = computed(()=> isEditView.value ? 'Edit View' : 'Create View');
</script>