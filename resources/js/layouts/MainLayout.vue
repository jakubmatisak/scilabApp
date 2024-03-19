<template>
  <v-layout>
    <app-header @toggle="onSideBarToggle" />
    <side-navigation ref="drawerRef" />
    <main-placeholder>
      <router-view />
    </main-placeholder>
  </v-layout>
</template>

<script setup>
import { ref } from "vue";
import AppHeader from "@/components/AppHeader.vue";
import MainPlaceholder from "@/components/MainPlaceholder.vue";
import SideNavigation from "@/components/SideNavigation.vue";

const drawerRef = ref(null);
const onSideBarToggle = () => {
    drawerRef.value.isDrawerOpen = !drawerRef.value.isDrawerOpen;
};
</script>
