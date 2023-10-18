<script setup lang="ts">
import { computed, onBeforeMount, reactive, ref, useSSRContext } from "vue";
import { useRoute } from "vue-router";
import useVuelidate from "@vuelidate/core";
import { email, helpers, minLength, required, maxLength } from "@vuelidate/validators";

import { Service as AdminService } from "@/services/adminService";
import { useAppStore } from "@/stores/app";
import { useAdminStore } from "@/stores/admin";
import { storeToRefs } from "pinia";
import type { EditUser, User } from "@/models";

const isLoadingUser = ref(false);
const formRules = computed(() => ({
    name: {
        required: helpers.withMessage("Navn må fylles ut.", required),
        minLength: helpers.withMessage("Navn må minst ha 2 tegn.", minLength(2)),
        maxLength: helpers.withMessage("Navn må maks ha 50 tegn.", maxLength(50)),
    },
    email: {
        required: helpers.withMessage("E-post må fylles ut.", required),
        email: helpers.withMessage("E-post har ugyldig format.", email),
        maxLength: helpers.withMessage("E-post må maks ha 50 tegn.", maxLength(50)),
    },
}));
const formState = reactive({
    id: "",
    name: "",
    email: "",
    blocked: false,
    blockedAt: "",
    permissions: [] as string[],
    submitting: false,
});

const v$ = useVuelidate(formRules, formState);
const route = useRoute();
const adminService = AdminService.use();
const appStore = useAppStore();
const adminStore = useAdminStore();

const { getUserById, permissions, users } = storeToRefs(adminStore);
const { getPermissions } = adminStore;
const { onProblemNotify } = appStore;

const editUser = async () => {
    try {
        formState.submitting = true;
        const success = await v$.value.$validate();
        if (!success) return;

        const user: EditUser = {
            id: route.params.id as string,
            name: formState.name,
            email: formState.email,
            blocked: formState.blocked,
            permissions: formState.permissions,
        };

        const ok = await adminStore.editUser(user);
        if (ok) v$.value.$reset();
    } finally {
        formState.submitting = false;
    }
};

const getUser = async () => {
    const cachedUser = getUserById.value(route.params.id as string);
    if (cachedUser) {
        mapUserToFormState(cachedUser);
        return;
    }

    const result = await adminService.user(route.params.id as string);
    result.match({
        Ok: (value) => mapUserToFormState(value.data),
        Err: (issue) => onProblemNotify(issue),
    });
};

const mapUserToFormState = (user: User) => {
    formState.name = user.name;
    formState.email = user.email;
    formState.blocked = user.blocked;
    formState.permissions = user.permissions.map(p => p.slug);
};

onBeforeMount(async () => {
    try {
        isLoadingUser.value = true;
        await getUser();

        if (permissions.value.length === 0) {
            await getPermissions();
        }
    } finally {
        isLoadingUser.value = false;
    }
});
</script>

<template>
    <section class="container form-container">
        <nav aria-label="breadcrumb">
            <ul>
                <li><router-link to="/">Hjem</router-link></li>
                <li><router-link to="/admin">Brukere</router-link></li>
                <li>Brukerdetaljer</li>
            </ul>
        </nav>

        <article v-if="isLoadingUser">
            <span aria-busy="true">Laster brukerdetaljer, vennligst vent ...</span>
        </article>

        <article v-else class="card-container">
            <h2>Brukerdetaljer</h2>

            <form @submit.prevent="editUser()">
                <label for="name">
                    Navn
                    <input v-model.trim="formState.name" type="name" name="name" placeholder="Navn" aria-label="Name"
                        :aria-invalid="!v$.name.$dirty ? undefined : (v$.name.$error)" />
                    <small id="invalid-helper">
                        {{ v$.name.$errors.map(e => e.$message).join(" ") }}
                    </small>
                </label>

                <label for="email">
                    E-post
                    <input v-model.trim="formState.email" type="email" name="email" placeholder="E-post" aria-label="Email"
                        autocomplete="email" :aria-invalid="!v$.email.$dirty ? undefined : (v$.email.$error)" />
                    <small id="invalid-helper">
                        {{ v$.email.$errors.map(e => e.$message).join(" ") }}
                    </small>
                </label>

                <fieldset>
                    <label for="permissions" class="dropdown-container">
                        <p>Tilganger</p>
                        <details class="dropdown">
                            <summary aria-haspopup="listbox">Velg ...</summary>
                            <ul>
                                <li v-for="permission in permissions" :key="permission.slug">
                                    <label>
                                        <input type="checkbox" :value="permission.slug" v-model="formState.permissions">
                                        {{ permission.name }}
                                    </label>
                                </li>
                            </ul>
                        </details>
                    </label>
                </fieldset>

                <fieldset>
                    <label for="switch">
                        Blokker bruker
                        <input type="checkbox" id="blocked" name="blocked" role="switch" v-model="formState.blocked">
                    </label>
                </fieldset>

                <input type="submit" value="Lagre" v-if="!formState.submitting">
                <button v-else class="secondary wait-btn" aria-busy="true" disabled>Vennligst vent...</button>
            </form>
        </article>
    </section>
</template>

<style scoped>
.form-container {
    max-width: 768px;
}

.wait-btn {
    width: 100%;
}
</style>
