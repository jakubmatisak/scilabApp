import { createRouter, createWebHashHistory } from "vue-router";
import AuthLayout from "@/layouts/AuthLayout.vue";
import MainLayout from "@/layouts/MainLayout.vue";
import LoginView from "@/pages/auth/LoginView.vue";
import RegisterView from "@/pages/auth/RegisterView.vue";
import ExperimentListView from "@/pages/experiments/ExperimentListView.vue";
import ExperimentSaveView from "@/pages/experiments/save/SaveView";
import ExperimentDetailView from "@/pages/experiments/DetailView";
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
        path: "/experiments",
        component: MainLayout,
        beforeEnter: [authGuard],
        children: [
            {
                path: "",
                component: ExperimentListView,
            },
            {
                path: "add",
                component: ExperimentSaveView,
            },
            {
                path: ":id",
                component: ExperimentDetailView,
            },
            {
                path: ":id/edit",
                component: ExperimentSaveView,
            },
        ],
    },
    {
        path: "/",
        redirect: "/experiments",
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;
