import { defineStore } from "pinia";
import { ref } from "vue";

export const useAuthStore = defineStore(
    "auth",
    () => {
        const user = ref();
        const token = ref("");

        const signIn = (signedInUser, newToken) => {
            user.value = signedInUser;
            token.value = newToken;
        };

        const signOut = () => {
            user.value = undefined;
            token.value = "";
        };

        return { user, token, signIn, signOut };
    },
    {
        persist: true,
    }
);
