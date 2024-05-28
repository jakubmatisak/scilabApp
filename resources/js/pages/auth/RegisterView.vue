<template>
  <v-container class="card-container">
    <v-card>
      <v-card-title class="bg-primary">
        {{ $t("Register") }}
      </v-card-title>
      <v-divider />
      <v-card-text class="ma-0 pa-0">
        <v-container>
          <v-form
            ref="form"
            v-model="valid"
            @submit.prevent="onSubmit"
          >
            <v-text-field
              id="username"
              v-model="formState.username"
              :label="$t('FullName')"
              name="username"
              prepend-icon="mdi-account"
              :rules="usernameRules"
              type="text"
            />
            <v-text-field
              id="email"
              v-model="formState.email"
              :label="$t('Email')"
              name="email"
              prepend-icon="mdi-email"
              :rules="emailRules"
              type="email"
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
            <v-text-field
              id="passwordRepeat"
              v-model="formState.passwordRepeat"
              :label="$t('RepeatPassword')"
              name="passwordRepeat"
              prepend-icon="mdi-lock"
              :rules="passwordRepeatRules"
              type="password"
            />
            <v-row justify="end">
              <v-spacer />
              <v-col class="no-grow pb-0">
                <v-btn
                  :disabled="isLoading"
                  :size="width < 600 ? 'small' : 'default'"
                  variant="outlined"
                  @click="onAlreadyHavenAnAccountPressed"
                >
                  {{ $t("LoginToAccount") }}
                </v-btn>
              </v-col>
              <v-col class="no-grow">
                <v-btn
                  :loading="isLoading"
                  :size="width < 600 ? 'small' : 'default'"
                  type="submit"
                  variant="elevated"
                >
                  {{ $t("RegisterBtn") }}
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
import { useSignUpMutation } from "@/api/queries/authQueries";
import { useAuthStore } from "@/stores/Auth";
import { useNotificationStore } from "@/stores/NotificationService";
import { useWindowSize } from "@vueuse/core";
import { trans } from "laravel-vue-i18n";

const { width } = useWindowSize();
const router = useRouter();

const valid = ref(false);
const form = ref(null);
const formState = reactive({
    username: "",
    email: "",
    password: "",
    passwordRepeat: "",
});
const usernameRules = [(value) => !!value || trans("FullNameRequired")];
const emailRules = [
    (value) => !!value || trans("EmailRequired"),
    (value) => /.+@.{2,}\..{2,3}$/.test(value) || trans("EmailValid"),
];
const passwordRules = [
    (value) => !!value || trans("PasswordRequired"),
    (value) => (value && value.length >= 6) || trans("PasswordSecure"),
];
const passwordRepeatRules = [
    (value) => !!value || trans("PasswordRepeatRequired"),
    (value) => value === formState.password || trans("PasswordMatch"),
];

const { mutateAsync, isLoading } = useSignUpMutation();
const { showSnackbar } = useNotificationStore();

const { signIn } = useAuthStore();

const onAlreadyHavenAnAccountPressed = () => {
    router.push("/login");
};

const onSubmit = async () => {
    if (!valid.value) {
        return;
    }

    try {
        const { data: userData } = await mutateAsync({
            name: formState.username,
            email: formState.email,
            password: formState.password,
        });

        signIn(userData.user, userData.token);

        router.push("/");
    } catch (err) {
        if (err?.response?.data?.errors) {
            showSnackbar(
                Object.values(err?.response?.data?.errors)[0][0],
                "error"
            );
        } else {
            showSnackbar(
                err?.response?.data?.message || "Error ocurred",
                "error"
            );
        }
    }
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
