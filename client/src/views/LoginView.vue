<script lang="ts" setup>
import { reactive, computed } from "vue";
import { useVuelidate } from "@vuelidate/core";
import { email, helpers, required } from "@vuelidate/validators";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const formState = reactive({
    email: "",
    password: "",
    isSubmitting: false,
});

const formRules = computed(() => ({
    email: {
        required: helpers.withMessage("E-post må fylles ut.", required),
        email: helpers.withMessage("E-post har et ugyldig format.", email),
    },
    password: {
        required: helpers.withMessage("Passord må fylles ut.", required),
    }
}));

const v$ = useVuelidate(formRules, formState);
const router = useRouter();
const authStore = useAuthStore();
const { login, logout, getUser } = authStore;

const signIn = async () => {
    try {
        formState.isSubmitting = true;
        const success = await v$.value.$validate();
        if (!success) return;

        await login(formState.email, formState.password);
        if (authStore.isLoggedIn) await getUser();
        if (authStore.user) {
            router.push({ name: "Home" });
            return;
        }

        // Make sure to reset session state if something went wrong during login
        await logout();
    } finally {
        formState.isSubmitting = false;
    }
};
</script>

<template>
    <section class="container login-container">
        <article>
            <div>
                <hgroup>
                    <h1>Logg inn</h1>
                    <h2>Logg inn med din Control-bruker</h2>
                </hgroup>
                <form @submit.prevent="signIn">
                    <label for="email">
                        E-post
                        <input v-model="formState.email" type="email" name="email" placeholder="E-post" aria-label="Email"
                            autocomplete="email" :aria-invalid="!v$.email.$dirty ? undefined : (v$.email.$error)" />
                        <small id="invalid-helper">
                            {{ v$.email.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>

                    <label for="password">
                        Passord
                        <input v-model="formState.password" type="password" name="password" placeholder="Passord"
                            aria-label="Password" autocomplete="current-password"
                            :aria-invalid="!v$.password.$dirty ? undefined : (v$.password.$error)" />
                        <small id="invalid-helper">
                            {{ v$.password.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>

                    <button v-if="!formState.isSubmitting" type="submit">Logg inn</button>
                    <button v-else class="secondary" type="button" aria-busy="true" disabled>Vennligst vent...</button>
                </form>
                <router-link class="contrast" to="/forgot-password">Glemt passord?</router-link>
            </div>
        </article>
    </section>
</template>

<style scoped>
.login-container {
    max-width: 768px;
}
</style>
