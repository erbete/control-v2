<script setup lang="ts">
import { reactive, computed, onBeforeMount } from "vue";
import useVuelidate from "@vuelidate/core";
import { storeToRefs } from "pinia";
import { email, helpers, minLength, maxLength, required, sameAs } from "@vuelidate/validators";
import { useAdminStore } from "../../stores/admin";

const formState = reactive({
    name: "",
    email: "",
    password: "",
    passwordConfirmation: "",
    roles: [],
    permissions: [],
    submitting: false,
});
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
    password: {
        required: helpers.withMessage("Passord må fylles ut.", required),
        minLength: helpers.withMessage("Passord må inneholde minst 12 tegn.", minLength(12)),
        format: helpers.withMessage("Passord krever minst 1 liten bokstav, 1 stor bokstav, 1 tall og 1 symbol.", helpers.regex(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/))
    },
    passwordConfirmation: {
        sameAsPassword: helpers.withMessage("Bekreft passord stemmer ikke overens med passordet.", sameAs(formState.password)),
        required: helpers.withMessage("Bekreft passord må fylles ut.", required),
    },
}));

const v$ = useVuelidate(formRules, formState);
const adminStore = useAdminStore();
const { permissions } = storeToRefs(adminStore);
const { getPermissions, registerUser } = adminStore;

const createUser = async () => {
    try {
        formState.submitting = true;

        const success = await v$.value.$validate();
        if (!success) return;

        const {
            name,
            email,
            password,
            passwordConfirmation,
            permissions,
        } = formState;

        const ok = await registerUser({
            name,
            email,
            password,
            password_confirmation: passwordConfirmation,
            permissions,
        });

        if (ok) resetModalState();
    } finally {
        formState.submitting = false;
    }
};

const resetModalState = () => {
    formState.name = "";
    formState.email = "";
    formState.password = "";
    formState.passwordConfirmation = "";
    formState.permissions = [];
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
                <li><router-link to="/admin">Brukere</router-link></li>
                <li>Opprett</li>
            </ul>
        </nav>

        <article>
            <h2>
                Opprett ny bruker
            </h2>

            <form>
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

                <div class="grid">
                    <label for="password">
                        Passord
                        <input v-model="formState.password" type="password" name="password" placeholder="Passord"
                            aria-label="Password" autocomplete="current-password"
                            :aria-invalid="!v$.password.$dirty ? undefined : (v$.password.$error)" />
                        <small id="invalid-helper">
                            {{ v$.password.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>

                    <label for="passwordConfirmation">
                        Bekreft passord
                        <input v-model="formState.passwordConfirmation" type="password" name="passwordConfirmation"
                            placeholder="Bekreft nytt passord" aria-label="Confirm Password" autocomplete="password"
                            :aria-invalid="!v$.passwordConfirmation.$dirty ? undefined : (v$.passwordConfirmation.$error)" />
                        <small id="invalid-helper">
                            {{ v$.passwordConfirmation.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>
                </div>

                <fieldset>
                    <label for="permissions" class="dropdown-container">
                        <p>Tilganger <small>(valgfritt... )</small></p>
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

                <input type="submit" value="Opprett" v-if="!formState.submitting" @click="createUser()">
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
