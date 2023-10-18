<script setup lang="ts">
import { reactive, computed, onBeforeMount } from "vue";
import { useRoute } from "vue-router";
import useVuelidate from "@vuelidate/core";
import { helpers, minLength, maxLength, required } from "@vuelidate/validators";
import { useAdminStore } from "@/stores/admin";
import { storeToRefs } from "pinia";

const formState = reactive({
    name: "",
    slug: "",
    description: "",
    roles: [],
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
const adminStore = useAdminStore();
const route = useRoute();
const { getPermissions } = adminStore;
const { permissions } = storeToRefs(adminStore);

const createPermission = async () => {
    try {
        formState.submitting = true;

        const success = await v$.value.$validate();
        if (!success) return;

        const {
            name,
            slug,
            description,
        } = formState;
        const ok = await adminStore.createPermission({
            id: route.params.id as string,
            name,
            slug,
            description,
        });

        if (ok) resetModalState();
    } finally {
        formState.submitting = false;
    }
};

const resetModalState = () => {
    formState.name = "";
    formState.slug = "";
    formState.description = "";
    formState.roles = [];
    v$.value.$reset();
};

onBeforeMount(async () => {
    if (permissions.value.length === 0) {
        await getPermissions();
    }
});
</script>

<template>
    <section class="container form-container">
        <nav aria-label="breadcrumb">
            <ul>
                <li><router-link to="/">Hjem</router-link></li>
                <li><router-link to="/admin">Tilganger</router-link></li>
                <li>Opprett</li>
            </ul>
        </nav>

        <article>
            <h2>
                Opprett ny tilgang
            </h2>

            <form>
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

                <input type="submit" value="Opprett" v-if="!formState.submitting" @click="createPermission()">
                <button v-else class="secondary wait-btn" aria-busy="true" disabled>Vennligst vent...</button>
            </form>
        </article>
    </section>
</template>

<style scoped>
.form-container {
    max-width: 768px;
}

.add-btn-icon {
    width: 48px;
}

.dropdown-container p {
    margin: 0;
}

.wait-btn {
    width: 100%;
}
</style>
