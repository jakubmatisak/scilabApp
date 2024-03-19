<template>
  <v-navigation-drawer
    v-model="isDrawerOpen"
    theme="dark"
  >
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
    <div class="px-2">
      <v-switch
        v-model="currentTheme"
        color="primary"
        false-value="lightTheme"
        hide-details
        inset
        :label="$t('DarkMode')"
        true-value="dark"
        @update:model-value="onThemeChange"
      />
    </div>
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
import { useTheme } from "vuetify";

const authStore = useAuthStore();
const theme = useTheme();
const { currentLoggedUser } = storeToRefs(authStore);
const isDrawerOpen = ref(window.innerWidth >= 1280);
const currentTheme = ref("lightTheme");

const onThemeChange = () => {
    theme.global.name.value = currentTheme.value;
};

defineExpose({
    isDrawerOpen,
});
</script>
