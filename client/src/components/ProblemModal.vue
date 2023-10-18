<script lang="ts" setup>
import { storeToRefs } from "pinia";
import { useAppStore } from "@/stores/app";

const appStore = useAppStore();
const { problemModal } = storeToRefs(appStore);

const resetModal = () => {
    appStore.$patch({
        problemModal: {
            open: false,
            problem: {
                type: "https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418",
                title: "Det har oppstått feil",
                detail: "Noe gikk galt, vennligst prøv igjen.",
                errors: [],
            }
        }
    });
};
</script>

<template>
    <dialog :open="problemModal.open">
        <article>
            <header>
                <a @click="resetModal()" aria-label="Close" class="close"></a>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="error-logo">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>

                    <b class="title">{{ problemModal.problem.title }}</b>
                </span>
            </header>

            <p>{{ problemModal.problem.detail }}</p>

            <template v-if="problemModal.problem.errors.length > 0">
                <p v-for="error in problemModal.problem.errors" :key="error">{{ error }}</p>
            </template>

            <footer>
                <a href="#" @click="resetModal()" role="button" class="secondary">Forstått</a>
            </footer>
        </article>
    </dialog>
</template>

<style scoped>
article {
    width: 100%;
}

.title,
.error-logo {
    color: var(--del-color);
}

.close:hover {
    cursor: pointer;
}

.error-logo {
    width: 30px;
    margin-right: 4px;
}
</style>
