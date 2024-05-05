import axios from "axios";
import { useAuthStore } from "@/stores/Auth";
import { useNotificationStore } from "../stores/NotificationService";
import router from "@/router";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: {
        "Content-Type": "application/json",
    },
});

api.interceptors.request.use(
    (config) => {
        const { token } = useAuthStore();
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const { showSnackbar } = useNotificationStore();
        const { signOut } = useAuthStore();
        if (error.response) {
            if (error.response.status === 401) {
                showSnackbar("Your session expired.", "error");
                signOut();
                router.push("/login");
            }
        }
        return Promise.reject(error);
    }
);

export default api;
