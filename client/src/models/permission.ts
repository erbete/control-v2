import type { User } from "./user";

export type Permission = {
    id: string,
    name: string,
    slug: string,
    description: string,
    createdAt: string,
    updatedAt: string,
    users: User[],
};
