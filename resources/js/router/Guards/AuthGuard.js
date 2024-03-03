import { storeToRefs } from "pinia";
import { useAuthStore } from "../../stores/Auth";

export const authGuard = (to) => {
    const authStore = useAuthStore();
    const { currentLoggedUser } = storeToRefs(authStore);

    if (to.fullPath === "/login" || to.fullPath === "/register") {
        if (currentLoggedUser.value) {
            return { path: "/" };
        }

        return true;
    }

    if (!currentLoggedUser.value) {
        return { path: "/login" };
    }

    return true;
};
