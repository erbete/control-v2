<script setup lang="ts">
import { computed, onBeforeMount, reactive, ref } from "vue";
import { useRoute } from "vue-router";
import useVuelidate from "@vuelidate/core";
import { helpers, minLength, required, maxLength } from "@vuelidate/validators";
import { Service as AdminService } from "@/services/adminService";
import { useAppStore } from "@/stores/app";
import { useAdminStore } from "@/stores/admin";
import { storeToRefs } from "pinia";
import type { EditPermission, Permission } from "@/models";

const isLoadingPermission = ref(false);
const formState = reactive({
    name: "",
    slug: "",
    description: "",
    submitting: false,
});
const formRules = computed(() => ({
    name: {
        required: helpers.withMessage("Navn må fylles ut.", required),
        minLength: helpers.withMessage("Navn må minst ha 2 tegn.", minLength(2)),
        maxLength: helpers.withMessage("Navn må maks ha 50 tegn.", maxLength(50)),
    },
    slug: {
        required: helpers.withMessage("Slug må fylles ut.", required),
        minLength: helpers.withMessage("Slug må minst ha 2 tegn.", minLength(2)),
    },
    description: {
        required: helpers.withMessage("Beskrivelse må fylles ut.", required),
        maxLength: helpers.withMessage("Beskrivelse må maks ha 500 tegn.", maxLength(500)),
    },
}));

const v$ = useVuelidate(formRules, formState);
const route = useRoute();
const adminService = AdminService.use();
const appStore = useAppStore();
const adminStore = useAdminStore();
const { getPermissionById } = storeToRefs(adminStore);
const { onProblemNotify } = appStore;

const editPermission = async () => {
    try {
        formState.submitting = true;

        const success = await v$.value.$validate();
        if (!success) return;

        const permission: EditPermission = {
            id: route.params.id as string,
            name: formState.name,
            slug: formState.slug,
            description: formState.description,
        };

        const ok = await adminStore.editPermission(permission);
        if (ok) v$.value.$reset();
    } finally {
        formState.submitting = false;
    }
};

const getPermission = async () => {
    const cachedPermission = getPermissionById.value(route.params.id as string);
    if (cachedPermission) {
        mapPermissionToFormState(cachedPermission);
        return;
    }

    const result = await adminService.permission(route.params.id as string);
    result.match({
        Ok: (value) => mapPermissionToFormState(value.data),
        Err: (issue) => onProblemNotify(issue),
    });
};

const mapPermissionToFormState = (permission: Permission) => {
    formState.name = permission.name;
    formState.slug = permission.slug;
    formState.description = permission.description;
};

onBeforeMount(async () => {
    try {
        isLoadingPermission.value = true;
        await getPermission();
    } finally {
        isLoadingPermission.value = false;
    }
});
</script>

<template>
    <section class="container form-container">
        <nav aria-label="breadcrumb">
            <ul>
                <li><router-link to="/">Hjem</router-link></li>
                <li><router-link to="/admin">Tilganger</router-link></li>
                <li>Detaljer</li>
            </ul>
        </nav>

        <article v-if="isLoadingPermission">
            <span aria-busy="true">Laster detaljer, vennligst vent ...</span>
        </article>

        <article v-else class="card-container">
            <h2>Tilgang</h2>

            <form @submit.prevent="editPermission()">
                <fieldset>
                    <label for="name">
                        Navn
                        <input v-model.trim="formState.name" type="name" name="name" placeholder="Navn" aria-label="Name"
                            :aria-invalid="!v$.name.$dirty ? undefined : (v$.name.$error)" />
                        <small id="invalid-helper">
                            {{ v$.name.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>

                    <label for="slug">
                        Slug
                        <input v-model.trim="formState.slug" type="text" name="slug" placeholder="Slug" aria-label="Slug"
                            :aria-invalid="!v$.slug.$dirty ? undefined : (v$.slug.$error)" />
                        <small id="invalid-helper">
                            {{ v$.slug.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>

                    <label for="description">
                        Beskrivelse
                        <textarea rows="4" v-model.trim="formState.description" placeholder="Beskrivelse"
                            aria-label="Beskrivelse"
                            :aria-invalid="!v$.description.$dirty ? undefined : (v$.description.$error)" />
                        <small id="invalid-helper">
                            {{ v$.description.$errors.map(e => e.$message).join(" ") }}
                        </small>
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
