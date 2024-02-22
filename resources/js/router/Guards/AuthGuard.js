import { storeToRefs } from "pinia";
import { useAuthStore } from "../../stores/Auth";

export const authGuard = (to) => {
    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);

    if (to.fullPath === "/login" || to.fullPath === "/register") {
        if (user.value) {
            return { path: "/" };
        }

        return true;
    }

    if (!user.value) {
        return { path: "/login" };
    }

    return true;
};
