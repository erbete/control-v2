import { inject } from "vue";
import { Result } from "true-myth";
import { type Nothing, nothing, just, of } from "true-myth/maybe";
import type { ServiceResult } from "./types";
import type { User } from "@/models";
import { get, post } from "./fetchTools";

export interface AuthService {
    login(email: string, password: string): Promise<ServiceResult<Nothing>>;
    logout(): Promise<ServiceResult<Nothing>>;
    user(): Promise<ServiceResult<User>>;
    forgotPassword(email: string): Promise<ServiceResult<Nothing>>;
    resetPassword(password: string, confirmPassword: string, token: string, email: string): Promise<ServiceResult<Nothing>>;
    xsrfCookie(): Promise<ServiceResult<Nothing>>;
}

export class Service implements AuthService {
    public static readonly INJECTION_KEY = Symbol();

    static use(): AuthService {
        const service = inject<Service>(this.INJECTION_KEY);
        if (!service) throw new Error("Auth service did not instantiate");
        return service;
    }

    async login(email: string, password: string): Promise<ServiceResult<Nothing>> {
        try {
            const response = await post("/login", { email, password });
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: nothing(),
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error),
            });
        }
    }

    async logout(): Promise<ServiceResult<Nothing>> {
        try {
            const response = await post("/logout");
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: nothing(),
            });

        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async user(): Promise<ServiceResult<User>> {
        try {
            const response = await get("/api/auth/user");
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: await response.json() as User,
            });

        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async forgotPassword(email: string): Promise<ServiceResult<Nothing>> {
        try {
            const response = await post("/forgot-password", { email });
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: nothing(),
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async resetPassword(password: string, confirmPassword: string, token: string, email: string): Promise<ServiceResult<Nothing>> {
        try {
            const response = await post("/reset-password", { password, password_confirmation: confirmPassword, token, email });
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: nothing(),
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async xsrfCookie(): Promise<ServiceResult<Nothing>> {
        try {
            const response = await get("/sanctum/csrf-cookie");
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: nothing()
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }
}
