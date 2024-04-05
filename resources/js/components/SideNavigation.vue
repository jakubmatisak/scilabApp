<template>
  <v-navigation-drawer v-model="isDrawerOpen">
    <v-list>
      <v-list-item
        :subtitle="currentLoggedUser?.email || 'Unknown Email'"
        :title="currentLoggedUser?.name || 'Unknown Name'"
      >
        <template #prepend>
          <v-avatar
            color="grey"
            icon="mdi-account"
          />
        </template>
      </v-list-item>
    </v-list>
    <v-divider thickness="3" />
    <navigation-list />
    <template #append>
      <logout-btn />
    </template>
  </v-navigation-drawer>
</template>

<script setup>
import { storeToRefs } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "@/stores/Auth";
import NavigationList from "@/components/NavigationList.vue";
import LogoutBtn from "@/components/LogoutBtn.vue";

const authStore = useAuthStore();
const { currentLoggedUser } = storeToRefs(authStore);
const isDrawerOpen = ref(window.innerWidth >= 1280);

defineExpose({
    isDrawerOpen,
});
</script>
