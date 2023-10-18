<script setup lang="ts">
import { RouterView } from "vue-router";
import { storeToRefs } from "pinia";

import { useAuthStore } from "./stores/auth";
import AppRail from "@/components/AppRail.vue";
import ProblemModal from "@/components/ProblemModal.vue";
import Snackbar from "@/components/Snackbar.vue";

const authStore = useAuthStore();
const { checkingSessionState } = storeToRefs(authStore);
</script>

<template>
    <ProblemModal />
    <Snackbar />

    <div class="app-wrapper">
        <aside class="app-rail">
            <AppRail />
        </aside>

        <div v-if="checkingSessionState" class="verify-session-container">
            <span aria-busy="true">Verifiserer innlogging, vennligst vent</span>
        </div>

        <main v-else class="main-container">
            <RouterView />
        </main>
    </div>
</template>

<style scoped>
.app-wrapper {
    display: grid;
    grid-template-columns: 60px auto;
    grid-template-rows: 100vh;
    grid-template-areas:
        "AppRail MainContainer"
        "AppRail MainContainer";
}

.app-rail {
    grid-area: AppRail;
}

.main-container {
    grid-area: MainContainer;
    margin: 80px 10px 0 10px;
    overflow-x: auto;
}

.verify-session-container {
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    min-height: 100vh;
}

@media only screen and (min-width: 480px) {
    .app-wrapper {
        grid-template-columns: 80px auto;
    }

    .main-container {
        margin: 80px 40px 0 40px;
    }
}
</style>
