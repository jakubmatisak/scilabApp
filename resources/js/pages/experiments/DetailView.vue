<template>
  <div>
    <header-component
      :back-button="true"
      title="Detail View"
    >
      <v-btn icon>
        <v-icon>mdi-pencil</v-icon>
      </v-btn>
    </header-component>
  </div>
</template>

<script setup>
import HeaderComponent from './components/HeaderComponent.vue';
</script>