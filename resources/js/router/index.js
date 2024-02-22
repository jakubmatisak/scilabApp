import { createRouter, createWebHashHistory } from "vue-router";
import AuthLayout from "../layouts/AuthLayout.vue";
import MainLayout from "../layouts/MainLayout.vue";
import LoginView from "../pages/LoginView.vue";
import RegisterView from "../pages/RegisterView.vue";
import { authGuard } from "./Guards/AuthGuard";

const routes = [
    {
        path: "/login",
        component: AuthLayout,
        children: [
            {
                path: "",
                component: LoginView,
            },
        ],
        beforeEnter: [authGuard],
    },
    {
        path: "/register",
        component: AuthLayout,
        children: [
            {
                path: "",
                component: RegisterView,
            },
        ],
        beforeEnter: [authGuard],
    },
    {
        path: "/",
        component: MainLayout,
        beforeEnter: [authGuard],
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;
