<script setup lang="ts">
import { reactive, computed } from "vue";
import { useVuelidate } from "@vuelidate/core";
import { helpers, required, minLength, sameAs } from "@vuelidate/validators";
import { Service as AuthService } from "@/services/authService";
import { useAppStore } from "@/stores/app";
import { useRoute, useRouter } from "vue-router";

const formState = reactive({
    password: "",
    confirmPassword: "",
    isSubmitting: false,
});

const pwdFormatRules = helpers.regex(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/);
const formRules = computed(() => ({
    password: {
        required: helpers.withMessage("Passord må fylles ut.", required),
        minLength: helpers.withMessage("Passord må inneholde minst 12 tegn.", minLength(12)),
        pwdFormatRules: helpers.withMessage("Passord krever minst 1 liten bokstav, 1 stor bokstav, 1 tall og 1 symbol.", pwdFormatRules)
    },
    confirmPassword: {
        sameAsPassword: helpers.withMessage("Bekreft passord stemmer ikke overens med passordet.", sameAs(formState.password)),
        required: helpers.withMessage("Bekreft passord må fylles ut.", required),
    }
}));

const v$ = useVuelidate(formRules, formState);
const service = AuthService.use();
const route = useRoute();
const router = useRouter();

const resetPwd = async () => {
    try {
        formState.isSubmitting = true;
        const success = await v$.value.$validate();
        if (!success) return;

        const result = await service.resetPassword(
            formState.password,
            formState.confirmPassword,
            route.query.token?.toString() ?? "",
            route.query.email?.toString() ?? ""
        );

        result.match({
            Ok: () => {
                v$.value.$reset();
                formState.password = "";
                formState.confirmPassword = "";
                router.push("/login");
            },
            Err: (issue) => {
                const appStore = useAppStore();
                appStore.onProblemNotify(issue);
            }
        });
    } finally {
        formState.isSubmitting = false;
    }
};
</script>

<template>
    <div class="container reset-pwd-container">
        <article>
            <div>
                <hgroup>
                    <h1>Tilbakestill passordet ditt</h1>
                    <h2>Opprett nytt ønsket passord</h2>
                </hgroup>
                <form @submit.prevent="resetPwd()">
                    <label for="password">
                        Passord
                        <input v-model="formState.password" type="password" name="password" placeholder="Nytt passord"
                            aria-label="Password" autocomplete="password"
                            :aria-invalid="!v$.password.$dirty ? undefined : (v$.password.$error)" />
                        <small id="invalid-helper">
                            {{ v$.password.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>

                    <label for="confirmPassword">
                        Bekreft passord
                        <input v-model="formState.confirmPassword" type="password" name="confirmPassword"
                            placeholder="Bekreft nytt passord" aria-label="Confirm Password" autocomplete="password"
                            :aria-invalid="!v$.confirmPassword.$dirty ? undefined : (v$.confirmPassword.$error)" />
                        <small id="invalid-helper">
                            {{ v$.confirmPassword.$errors.map(e => e.$message).join(" ") }}
                        </small>
                    </label>

                    <button v-if="!formState.isSubmitting" type="submit">Tilbakestill</button>
                    <button v-else type="button" aria-busy="true">Vennligst vent...</button>
                </form>
                <router-link class="contrast" to="/login">Logg inn</router-link>
            </div>
        </article>
    </div>
</template>

<style scoped>
.reset-pwd-container {
    max-width: 768px;
}
</style>

