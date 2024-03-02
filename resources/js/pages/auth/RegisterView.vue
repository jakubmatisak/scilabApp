<template>
  <v-card class="w-50">
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
            :label="$t('Username')"
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
          <div class="d-flex flex-row justify-end">
            <v-btn
              class="mr-4"
              variant="outlined"
              @click="onAlreadyHavenAnAccountPressed"
            >
              {{ $t("LoginToAccount") }}
            </v-btn>
            <v-btn
              :loading="isLoading"
              type="submit"
              variant="elevated"
            >
              {{ $t("RegisterBtn") }}
            </v-btn>
          </div>
          <v-snackbar
            v-model="snackbar"
            color="error"
            rounded="pill"
            :timeout="2000"
          >
            {{ error?.response?.data?.message || "Error ocurred" }}
          </v-snackbar>
        </v-form>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useSignUpMutation } from "@/api/queries/authQueries";
import { useAuthStore } from "@/stores/Auth";
import { useNotificationStore } from "@/stores/NotificationService";

const router = useRouter();

const valid = ref(false);
const form = ref(null);
const formState = reactive({
    username: "",
    email: "",
    password: "",
    passwordRepeat: "",
});
const usernameRules = [(value) => !!value || "Username is required"];
const emailRules = [
    (value) => !!value || "Email is required",
    (value) => /.+@.+\..+/.test(value) || "Email must be valid",
];
const passwordRules = [
    (value) => !!value || "Password is required",
    (value) =>
        (value && value.length >= 6) ||
        "Password must be at least 6 characters long",
];
const passwordRepeatRules = [
    (value) => !!value || "Password Repeat is required",
    (value) => value === formState.password || "Passwords doesn't match",
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
        const {data: userData} = await mutateAsync({
            name: formState.username,
            email: formState.email,
            password: formState.password,
        });

        signIn(
            { name: formState.username, email: formState.email },
            userData.token
        );

        router.push("/");
    } catch (err) {
        console.error(err);
        showSnackbar(err?.response?.data?.message || "Error ocurred", "error");
    }
};
</script>