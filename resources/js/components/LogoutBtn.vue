<template>
  <div class="pa-2">
    <v-btn
      block
      prepend-icon="mdi-logout"
      @click="onLogoutClicked"
    >
      {{ $t("Logout") }}
    </v-btn>
  </div>
</template>

<script setup>
import { useAuthStore } from "@/stores/Auth";
import { useRouter } from "vue-router";

const router = useRouter();
const { signOut } = useAuthStore();

const onLogoutClicked = () => {
    signOut();
    router.push("/login");
};
</script>
