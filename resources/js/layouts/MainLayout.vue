<template>
  <v-layout>
    <app-header />
    <side-navigation />
    <main-placeholder>
      <router-view />
    </main-placeholder>
  </v-layout>
</template>

<script setup>
import AppHeader from "@/components/AppHeader.vue";
import MainPlaceholder from "@/components/MainPlaceholder.vue";
import SideNavigation from "@/components/SideNavigation.vue";
</script>