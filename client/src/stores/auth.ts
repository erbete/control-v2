import type { Nothing } from "true-myth/maybe";
import { defineStore } from "pinia";
import { useAppStore } from "./app";
import type { User } from "@/models";
import type { ServiceResult } from "@/services/types";

interface AuthState {
    user: User | null,
    isLoggedIn: boolean,
    checkingSessionState: boolean,
    hasXSRFCookie: boolean,
}

export const useAuthStore = defineStore("auth", {
    state: (): AuthState => {
        return {
            user: null,
            isLoggedIn: false,
            checkingSessionState: false,
            hasXSRFCookie: document.cookie.indexOf("XSRF-TOKEN") !== -1,
        };
    },

    getters: {
        hasPermission: (state) => {
            return (permission: string) =>
                state.user?.permissions.some(p => p.slug === permission.toLowerCase());
        }
    },

    actions: {
        async login(email: string, password: string) {
            if (this.isLoggedIn) return;
            const appStore = useAppStore();
            const result = await this.authService.login(email, password) as ServiceResult<Nothing>;

            result.match({
                Ok: () => {
                    this.isLoggedIn = true;
                },
                Err: (issue) => {
                    this.$reset();
                    appStore.onProblemNotify(issue);
                }
            });
        },

        async logout() {
            if (!this.isLoggedIn) return;
            const appStore = useAppStore();
            const result = await this.authService.logout() as ServiceResult<Nothing>;

            result.match({
                Ok: () => this.$reset(),
                Err: (issue) => appStore.onProblemNotify(issue),
            });
        },

        async forgotPassword(email: string) {
            const appStore = useAppStore();
            const result = await this.authService.forgotPassword(email) as ServiceResult<Nothing>;

            result.match({
                Ok: () => this.$reset(),
                Err: (issue) => appStore.onProblemNotify(issue),
            });
        },

        async getUser() {
            const appStore = useAppStore();
            const result = await this.authService.user() as ServiceResult<User>;

            result.match({
                Ok: (value) => {
                    this.user = value.data;
                    this.isLoggedIn = true;
                },
                Err: (issue) => {
                    this.$reset();
                    appStore.onProblemNotify(issue);
                }
            });
        },

        async getXSRFCookie() {
            const appStore = useAppStore();
            const result = await this.authService.xsrfCookie() as ServiceResult<Nothing>;

            result.match({
                Ok: () => {
                    this.hasXSRFCookie = true;
                },
                Err: (issue) => {
                    this.hasXSRFCookie = false;
                    appStore.onProblemNotify(issue);
                }
            });
        }
    },
});
