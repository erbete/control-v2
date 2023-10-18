import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import { useAuthStore } from "@/stores/auth";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        /// Other
        {
            path: "/",
            name: "Home",
            component: HomeView,
        },
        {
            path: "/tools",
            name: "Tools",
            component: () => import("../views/ToolsView.vue"),
        },
        {
            path: "/:pathMatch(.*)*",
            name: "NotFound",
            component: () => import("../views/NotFoundView.vue"),
        },
        {
            path: "/theme",
            name: "Theme",
            component: () => import("../views/ThemeTestPage.vue"),
        },

        /// Authentication
        {
            path: "/login",
            name: "Login",
            component: () => import("../views/LoginView.vue"),
            beforeEnter: async () => {
                const authStore = useAuthStore();
                if (authStore.isLoggedIn) return "/";

                if (!authStore.hasXSRFCookie) {
                    await authStore.getXSRFCookie();
                }
            }
        },
        {
            path: "/forgot-password",
            name: "ForgotPassword",
            component: () => import("../views/ForgotPasswordView.vue"),
            beforeEnter: async () => {
                const authStore = useAuthStore();
                if (authStore.isLoggedIn) return "/";

                if (!authStore.hasXSRFCookie) {
                    await authStore.getXSRFCookie();
                }
            }
        },
        {
            path: "/reset-password",
            name: "ResetPassword",
            component: () => import("../views/ResetPasswordView.vue"),
            beforeEnter: async () => {
                const authStore = useAuthStore();
                if (authStore.isLoggedIn) return "/";

                if (!authStore.hasXSRFCookie) {
                    await authStore.getXSRFCookie();
                }
            }
        },

        /// Administator
        {
            path: "/admin",
            name: "Admin",
            component: () => import("../views/Admin/AdminView.vue"),
            children: [
                {
                    path: "/admin/user/:id/details",
                    name: "UserDetails",
                    component: () => import("../views/Admin/AdminUserDetailsView.vue"),
                },
                {
                    path: "/admin/user/create",
                    name: "CreateUser",
                    component: () => import("../views/Admin/AdminCreateUserView.vue"),
                },
                {
                    path: "/admin/permission/:id/details",
                    name: "PermissionDetails",
                    component: () => import("../views/Admin/AdminPermissionDetailsView.vue"),
                },
                {
                    path: "/admin/permission/create",
                    name: "CreatePermission",
                    component: () => import("../views/Admin/AdminCreatePermissionView.vue"),
                },
            ],
            beforeEnter: () => {
                const authStore = useAuthStore();
                if (!authStore.hasPermission("admin")) return "/";
            },
        },

        /// Rebinding
        {
            path: "/rebinding",
            name: "Rebinding",
            component: () => import("../views/RebindingView.vue"),
            beforeEnter: () => {
                const authStore = useAuthStore();
                if (!authStore.hasPermission("rebinding")) return "/";
            }
        },
    ],
});

// Try get user if not logged in and has a XSRF cookie present
let tryCount = 0;
router.beforeEach(async (_, __, next) => {
    const authStore = useAuthStore();

    if (authStore.isLoggedIn) {
        next();
        return;
    }

    const tryGetUser = authStore.hasXSRFCookie && tryCount === 0;
    if (tryGetUser) {
        try {
            authStore.checkingSessionState = true;
            const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/auth-ping`, {
                credentials: "include",
            });

            if (response.ok) await authStore.getUser();
            tryCount += 1;
        } catch (_) {
            tryCount = 0;
        } finally {
            authStore.checkingSessionState = false;
        }
    }

    next();
});

// Allow user to route to public pages (i.e. no login required to view these pages)
router.beforeResolve(async (to, _, next) => {
    const authStore = useAuthStore();

    const publicPages = [
        "/",
        "/login",
        "/forgot-password",
        "/reset-password",
        "/tools",
        "/theme",
    ];

    const authRequired = !publicPages.includes(to.path);

    if (to.name !== "login" && !authStore.isLoggedIn && authRequired) {
        next({ name: "login" });
        return;
    }

    next();
});

export default router;
