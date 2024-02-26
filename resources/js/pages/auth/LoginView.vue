<template>
  <v-card class="w-50">
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
          <div class="d-flex flex-row justify-end">
            <v-btn
              class="mr-4"
              variant="outlined"
              @click="onCreateAccountPressed"
            >
              {{ $t("CreateAccount") }}
            </v-btn>
            <v-btn
              :loading="isLoading"
              type="submit"
              variant="elevated"
            >
              {{ $t("LoginBtn") }}
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
import { useSignInMutation } from "@/api/queries/authQueries";
import { useAuthStore } from "@/stores/Auth";

const router = useRouter();

const valid = ref(false);
const form = ref(null);
const formState = reactive({
    email: "",
    password: "",
});
const emailRules = [(value) => !!value || "Email is required"];
const passwordRules = [(value) => !!value || "Password is required"];

const { mutateAsync, isLoading, error } = useSignInMutation();
const snackbar = ref(false);

const { signIn } = useAuthStore();

const onSubmit = async () => {
    if (!valid.value) {
        return;
    }

    try {
        const {data: userData} = await mutateAsync({
            email: formState.email,
            password: formState.password,
        });

        signIn({ email: formState.email }, userData.token);

        router.push("/");
    } catch (err) {
        console.error(err.response.data.message);
        snackbar.value = true;
    }
};

const onCreateAccountPressed = () => {
    router.push("/register");
};
</script>