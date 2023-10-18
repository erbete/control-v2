import { defineStore } from "pinia";
import { type Nothing } from "true-myth/maybe";
import { SnackbarType, useAppStore } from "./app";
import type { ServiceResult } from "@/services/types";
import type {
    EditPermission,
    EditUser,
    NewUser,
    Permission,
    User,
    NewPermission
} from "@/models";

interface AdminState {
    users: User[],
    permissions: Permission[],
}

export const useAdminStore = defineStore("admin", {
    state: (): AdminState => {
        return {
            users: [],
            permissions: [],
        };
    },

    getters: {
        getUserById: (state) => {
            return (id: string) => state.users.find((u) => u.id === id);
        },

        getPermissionById: (state) => {
            return (id: string) => state.permissions.find((p) => p.id === id);
        },
    },

    actions: {
        async getUsers() {
            const appStore = useAppStore();
            const result = await this.adminService.users() as ServiceResult<User[]>;

            result.match({
                Ok: (value) => {
                    this.users = value.data;
                },
                Err: (issue) => appStore.onProblemNotify(issue)
            });
        },

        async registerUser(newUser: NewUser) {
            const appStore = useAppStore();
            const result = await this.adminService.registerUser(newUser) as ServiceResult<User>;

            return result.match<boolean>({
                Ok: (value) => {
                    this.users.push(value.data);

                    // update permissions state
                    for (let i = 0; i < this.permissions.length; i++) {
                        this.permissions[i].users = this.permissions[i].users.filter(u => u.id !== value.data.id);
                    }
                    for (const permission of value.data.permissions) {
                        const pidx = this.permissions.findIndex(p => p.id === permission.id);
                        this.permissions[pidx].users.push(value.data);
                    }

                    appStore.showSnackbar("Bruker er opprettet", SnackbarType.Success);
                    return true;
                },
                Err: (issue) => {
                    appStore.onProblemNotify(issue);
                    return false;
                }
            });
        },

        async editUser(user: EditUser) {
            const appStore = useAppStore();
            const result = await this.adminService.editUser(user) as ServiceResult<User>;

            return result.match<boolean>({
                Ok: (value) => {
                    const cachedUserIdx = this.users.findIndex(u => u.id === value.data.id);
                    this.users[cachedUserIdx] = value.data;

                    // update permissions state
                    for (let i = 0; i < this.permissions.length; i++) {
                        this.permissions[i].users = this.permissions[i].users.filter(u => u.id !== value.data.id);
                    }
                    for (const permission of value.data.permissions) {
                        const pidx = this.permissions.findIndex(p => p.id === permission.id);
                        this.permissions[pidx].users.push(value.data);
                    }

                    appStore.showSnackbar("Bruker oppdatert", SnackbarType.Success);
                    return true;
                },
                Err: (value) => {
                    appStore.onProblemNotify(value);
                    return false;
                }
            });
        },

        async getPermissions() {
            const appStore = useAppStore();
            const result = await this.adminService.permissions() as ServiceResult<Permission[]>;

            result.match({
                Ok: (value) => {
                    this.permissions = value.data;
                },
                Err: (issue) => appStore.onProblemNotify(issue)
            });
        },

        async createPermission(permission: NewPermission) {
            const appStore = useAppStore();
            const result = await this.adminService.createPermission(permission) as ServiceResult<Permission>;

            return result.match<boolean>({
                Ok: (value) => {
                    this.permissions.push(value.data);
                    appStore.showSnackbar("Ny tilgang opprettet", SnackbarType.Success);
                    return true;
                },
                Err: (issue) => {
                    appStore.onProblemNotify(issue);
                    return false;
                }
            });
        },

        async editPermission(permission: EditPermission) {
            const appStore = useAppStore();
            const result = await this.adminService.editPermission(permission) as ServiceResult<Permission>;

            return result.match<boolean>({
                Ok: (value) => {
                    const cachedPermissionIdx = this.permissions.findIndex(p => p.id === value.data.id);
                    if (cachedPermissionIdx !== -1) {
                        this.permissions[cachedPermissionIdx] = value.data;
                    }

                    for (let i = 0; i < this.users.length; i++) {
                        const user = this.users[i];
                        for (let j = 0; j < user.permissions.length; j++) {
                            if (user.permissions[j].id !== value.data.id) continue;
                            this.users[i].permissions[j] = value.data;
                        }
                    }

                    appStore.showSnackbar("Tilgangen er oppdatert", SnackbarType.Success);
                    return true;
                },
                Err: (value) => {
                    appStore.onProblemNotify(value);
                    return false;
                }
            });
        },

        async detachUserFromPermission(permissionId: string, userId: string) {
            const appStore = useAppStore();
            const result = await this.adminService.detachUserFromPermission(permissionId, userId) as ServiceResult<Nothing>;

            return result.match<boolean>({
                Ok: () => {
                    for (const permission of this.permissions) {
                        if (permission.id !== permissionId) continue;
                        permission.users = permission.users.filter(u => u.id !== userId);
                    }
                    for (const user of this.users) {
                        if (user.id !== userId) continue;
                        user.permissions = user.permissions.filter(p => p.id !== permissionId);
                    }

                    appStore.showSnackbar("Tilgang fjernet fra bruker", SnackbarType.Success);
                    return true;
                },
                Err: (issue) => {
                    appStore.onProblemNotify(issue);
                    return false;
                }
            });
        },
    }
});
