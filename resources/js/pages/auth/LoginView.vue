<template>
  <v-container class="card-container">
    <v-card>
      <v-card-title class="bg-primary">
        {{ $t("LoginTitle") }}
      </v-card-title>
      <v-card-text class="ma-0 pa-0">
        <v-container>
          <v-form
            ref="form"
            v-model="valid"
            @submit.prevent="onSubmit"
          >
            <v-text-field
              v-model="formState.email"
              :label="$t('Email')"
              name="email"
              prepend-icon="mdi-account"
              :rules="emailRules"
              type="text"
            />
            <v-text-field
              id="password"
              v-model="formState.password"
              :label="$t('Password')"
              name="password"
              prepend-icon="mdi-lock"
              :rules="passwordRules"
              type="password"
            />
            <v-row justify="end">
              <v-spacer />
              <v-col class="no-grow pb-0">
                <v-btn
                  :disabled="isLoading"
                  :size="width < 600 ? 'small' : 'default'"
                  variant="outlined"
                  @click="onCreateAccountPressed"
                >
                  {{ $t("CreateAccount") }}
                </v-btn>
              </v-col>
              <v-col class="no-grow">
                <v-btn
                  :loading="isLoading"
                  :size="width < 600 ? 'small' : 'default'"
                  type="submit"
                  variant="elevated"
                >
                  {{ $t("LoginBtn") }}
                </v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-container>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useSignInMutation } from "@/api/queries/authQueries";
import { useAuthStore } from "@/stores/Auth";
import { useNotificationStore } from "@/stores/NotificationService";
import { useWindowSize } from "@vueuse/core";
import { trans } from "laravel-vue-i18n";

const { width } = useWindowSize();
const router = useRouter();
const { showSnackbar } = useNotificationStore();

const valid = ref(false);
const form = ref(null);
const formState = reactive({
    email: "",
    password: "",
});
const emailRules = [
    (value) => !!value || trans("EmailRequired"),
    (value) => /.+@.{2,}\..{2,3}$/.test(value) || trans("EmailValid"),
];
const passwordRules = [(value) => !!value || trans("PasswordRequired")];

const { mutateAsync, isLoading } = useSignInMutation();

const { signIn } = useAuthStore();

const onSubmit = async () => {
    if (!valid.value) {
        return;
    }

    try {
        const { data: userData } = await mutateAsync({
            email: formState.email,
            password: formState.password,
        });

        signIn(userData.user, userData.token);

        router.push("/");
    } catch (err) {
        console.error(err);
        showSnackbar(err.response?.data?.message || "Error ocurred", "error");
    }
};

const onCreateAccountPressed = () => {
    router.push("/register");
};
</script>

<style scoped lang="scss">
.card-container {
    max-width: 900px;

    .no-grow {
        flex-grow: 0;
    }
}
</style>
