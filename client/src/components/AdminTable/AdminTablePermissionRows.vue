<script setup lang="ts">
import { useAdminStore } from "@/stores/admin";
import { storeToRefs } from "pinia";
import { ref } from "vue";

const adminStore = useAdminStore();
const { users, permissions } = storeToRefs(adminStore);
const { getUsers, getPermissions } = adminStore;

const detachUserFromPermission = async (permissionId: string, userId: string) => {
    await adminStore.detachUserFromPermission(permissionId, userId);
    getPermissionsAndUsers();
};

type PermissionAndUser = {
    permissionName: string;
    userName: string,
    userId: string;
    permissionId: string
};
const permissionsAndUsers = ref([] as PermissionAndUser[]);
const getPermissionsAndUsers = () => {
    const data: PermissionAndUser[] = [];
    for (const user of users.value) {
        for (const permission of user.permissions) {
            const obj = {
                permissionName: permission.name,
                userName: user.name,
                userId: user.id,
                permissionId: permission.id,
            };
            data.push(obj);
        }
    }

    permissionsAndUsers.value = data.sort((a, b) => (a.userName > b.userName) ? 1 : -1);
};

if (users.value.length === 0 || permissions.value.length === 0) {
    await Promise.all([
        getUsers(),
        getPermissions(),
    ]);
}

getPermissionsAndUsers();
</script>

<template>
    <table class="admin-table permissions-table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Navn</th>
                <th scope="col">Slug</th>
                <th scope="col">Beskrivelse</th>
                <th scope="col"></th>
            </tr>
        </thead>

        <tbody>
            <tr v-for="(permission, index) in permissions" :key="permission.id">
                <td>{{ index + 1 }}</td>
                <td>{{ permission.name }}</td>
                <td>{{ permission.slug }}</td>
                <td class="description-cell">{{ permission.description }}</td>
                <td>
                    <router-link class="contrast details-link"
                        :to="{ name: 'PermissionDetails', params: { id: permission.id } }">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="details-icon">
                            <path fill-rule="evenodd"
                                d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.993 6.993 0 011.929 1.115l1.598-.54a1 1 0 011.186.447l1.18 2.044a1 1 0 01-.205 1.251l-1.267 1.113a7.047 7.047 0 010 2.228l1.267 1.113a1 1 0 01.206 1.25l-1.18 2.045a1 1 0 01-1.187.447l-1.598-.54a6.993 6.993 0 01-1.929 1.115l-.33 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.993 6.993 0 01-1.929-1.115l-1.598.54a1 1 0 01-1.186-.447l-1.18-2.044a1 1 0 01.205-1.251l1.267-1.114a7.05 7.05 0 010-2.227L1.821 7.773a1 1 0 01-.206-1.25l1.18-2.045a1 1 0 011.187-.447l1.598.54A6.993 6.993 0 017.51 3.456l.33-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                clip-rule="evenodd" />
                        </svg>
                        Detaljer
                    </router-link>
                </td>
            </tr>
        </tbody>
    </table>

    <br />

    <h4>Tilganger og brukere</h4>
    <table class="admin-table permissions-and-users-table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Tilgang</th>
                <th scope="col">Bruker</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(data, index) in permissionsAndUsers" :key="index">
                <td scope="row">{{ index + 1 }}</td>
                <td>{{ data.permissionName }}</td>
                <td>{{ data.userName }}</td>
                <td>
                    <a class="remove-btn" @click="detachUserFromPermission(data.permissionId, data.userId)">Fjern</a>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<style scoped>
.admin-table {
    white-space: nowrap;
}

.admin-table thead tr th {
    background-color: var(--pico-card-background-color);
}

.admin-table th {
    border: none;
}

.admin-table th:last-child {
    border-top-right-radius: 2px;
}

.admin-table .description-cell {
    max-width: 150px;
}

.admin-table .details-icon {
    width: 1rem;
}

.admin-table .details-link {
    white-space: nowrap;
}

.remove-btn {
    color: var(--pico-del-color);
    text-decoration: none;
}

.remove-btn:hover {
    cursor: pointer;
    font-weight: bold;
}

.permissions-table th:nth-child(5),
.permissions-table td:nth-child(5) {
    text-align: center;
}

.permissions-table td:nth-child(4) {
    overflow: hidden;
    text-overflow: ellipsis;
}

.permissions-and-users-table th:nth-child(4),
.permissions-and-users-table td:nth-child(4) {
    text-align: center;
}
</style>
