import type { Permission } from ".";

export type User = {
    id: string,
    name: string,
    email: string,
    blocked: boolean,
    blockedAt: string,
    permissions: Permission[],
    createdAt: string,
    updatedAt: string,
};
