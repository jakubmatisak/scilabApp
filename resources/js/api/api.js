import axios from "axios";

const api = axios.create({
    baseURL: "http://127.0.0.1:8000/api/",
    headers: {
        "Content-Type": "application/json",
    },
});

api.interceptors.request.use(
    (config) => {
        const authStore = localStorage.getItem("auth");
        const authObject = JSON.parse(authStore);
        if (authObject?.token) {
            config.headers.Authorization = `Bearer ${authObject.token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default api;
