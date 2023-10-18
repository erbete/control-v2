<script setup lang="ts">
import useDateTimeFormatter from "@/modules/formatDateTime";
import { useAdminStore } from "@/stores/admin";

const store = useAdminStore();
const { getUsers } = store;
const { formatDateTime } = useDateTimeFormatter();

if (store.users.length === 0) {
    await getUsers();
}
</script>

<template>
    <table class="admin-table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Navn</th>
                <th scope="col">E-post</th>
                <th class="text-center" scope="col">Blokkeringsstatus</th>
                <th class="text-center" scope="col">Blokkeringsdato</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            <tr v-for="(user, index) in store.users" :key="user.id" scope="row">
                <td scope="row">{{ index + 1 }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td class="text-center" :class="{ 'user-blocked': user.blocked, 'user-not-blocked': !user.blocked }">{{
                    user.blocked ? 'Blokkert' : 'Ikke blokkert' }}</td>
                <td class="text-center">{{ user.blocked ? formatDateTime(user.blockedAt) : 'Ikke angitt' }}</td>
                <td>
                    <router-link class="contrast details-link" :to="{ name: 'UserDetails', params: { id: user.id } }">
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
</template>

<style scoped>
.user-not-blocked {
    color: var(--pico-ins-color);
}

.user-blocked {
    color: var(--pico-del-color);
}

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

.admin-table th:nth-child(4),
.admin-table th:nth-child(5),
.admin-table td:nth-child(4),
.admin-table td:nth-child(5),
.admin-table td:nth-child(6) {
    text-align: center;
}
</style>
