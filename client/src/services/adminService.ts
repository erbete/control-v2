import { inject } from "vue";
import { Result } from "true-myth";
import { get, post, put } from "./fetchTools";
import { type Nothing, just, of, nothing } from "true-myth/maybe";
import type {
    User,
    NewUser,
    EditUser,
    Permission,
    NewPermission,
    EditPermission
} from "@/models";
import type { ServiceResult } from "./types";

export interface AdminService {
    users(): Promise<ServiceResult<User[]>>;
    user(id: string): Promise<ServiceResult<User>>;
    registerUser(newUser: NewUser): Promise<ServiceResult<User>>;
    editUser(user: EditUser): Promise<ServiceResult<User>>;
    permissions(): Promise<ServiceResult<Permission[]>>;
    permission(id: string): Promise<ServiceResult<Permission>>;
    createPermission(permission: NewPermission): Promise<ServiceResult<Permission>>;
    editPermission(permission: EditPermission): Promise<ServiceResult<Permission>>;
    detachUserFromPermission(permissionId: string, userId: string): Promise<ServiceResult<Nothing>>;
}

export class Service implements AdminService {
    public static readonly INJECTION_KEY = Symbol();

    static use(): AdminService {
        const service = inject<Service>(this.INJECTION_KEY);
        if (!service) throw new Error("AdminService: not instantiated");
        return service;
    }

    async users(): Promise<ServiceResult<User[]>> {
        try {
            const response = await get("/api/admin/users");
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: await response.json() as User[],
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async user(id: string): Promise<ServiceResult<User>> {
        try {
            const response = await get(`/api/admin/users/${id}`);
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

    async permissions(): Promise<ServiceResult<Permission[]>> {
        try {
            const response = await get("/api/admin/permissions");
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: await response.json() as Permission[],
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async permission(id: string): Promise<ServiceResult<Permission>> {
        try {
            const response = await get(`/api/admin/permissions/${id}`);
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: await response.json() as Permission,
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async registerUser(newUser: NewUser): Promise<ServiceResult<User>> {
        try {
            const response = await post("/api/admin/users/register", newUser);
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

    async editUser(user: EditUser): Promise<ServiceResult<User>> {
        try {
            const response = await put(`/api/admin/users/${user.id}/edit`, user);
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

    async createPermission(permission: NewPermission): Promise<ServiceResult<Permission>> {
        try {
            const response = await post("/api/admin/permissions/create", permission);
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: await response.json() as Permission,
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async editPermission(permission: EditPermission): Promise<ServiceResult<Permission>> {
        try {
            const response = await put(`/api/admin/permissions/${permission.id}/edit`, permission);
            if (!response.ok) return Result.err({
                status: just(response.status),
                problem: of(await response.json()),
            });

            return Result.ok({
                status: response.status,
                data: await response.json() as Permission,
            });
        } catch (error) {
            return Result.err({
                status: nothing(),
                problem: just(error)
            });
        }
    }

    async detachUserFromPermission(permissionId: string, userId: string): Promise<ServiceResult<Nothing>> {
        try {
            const response = await post("/api/admin/permissions/detach", { permissionId, userId });
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
}
