<script setup lang="ts">
import { reactive, computed } from "vue";
import { useVuelidate } from "@vuelidate/core";
import { email, helpers, required } from "@vuelidate/validators";
import { useAuthStore } from "@/stores/auth";

const formState = reactive({
    email: "",
    submitting: false,
});

const formRules = computed(() => ({
    email: {
        required: helpers.withMessage("E-post må fylles ut.", required),
        email: helpers.withMessage("E-post har et ugyldig format.", email),
    },
}));

const authStore = useAuthStore();
const { forgotPassword } = authStore;
const v$ = useVuelidate(formRules, formState);

const forgotPwd = async () => {
    try {
        formState.submitting = true;
        const success = await v$.value.$validate();
        if (!success) return;

        await forgotPassword(formState.email);
        v$.value.$reset();
        formState.email = "";
    } finally {
        formState.submitting = false;
    }
};
</script>

<template>
    <div class="container forgot-pwd-container">
        <article>
            <div>
                <hgroup>
                    <h1>Glemt passord?</h1>
                    <h2>Send inn e-postadressen til din Control-bruker for å tilbakestille</h2>
                </hgroup>
                <form @submit.prevent="forgotPwd()">
                    <label for="email">
                        E-post
                        <input v-model="formState.email" type="email" name="email" placeholder="Din e-postadresse"
                            aria-label="Email" autocomplete="email"
                            :aria-invalid="!v$.email.$dirty ? undefined : (v$.email.$error)" />
                        <small id="invalid-helper">
                            {{ v$.email.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>

                    <button v-if="!formState.submitting" type="submit">Send inn</button>
                    <button v-else type="button" aria-busy="true">Vennligst vent...</button>
                </form>
                <router-link class="contrast" to="/login">Logg inn</router-link>
            </div>
        </article>
    </div>
</template>

<style scoped>
.forgot-pwd-container {
    max-width: 768px;
}
</style>
