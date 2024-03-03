<template>
  <v-navigation-drawer theme="dark">
    <v-list>
      <v-list-item
        :subtitle="user?.email || 'Unknown Email'"
        :title="user?.name || 'Unknown Name'"
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
import { useAuthStore } from "@/stores/Auth";
import NavigationList from "@/components/NavigationList.vue";
import LogoutBtn from "@/components/LogoutBtn.vue";

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
</script>