<script lang="ts" setup>
import { useAuthStore } from "@/stores/auth";
import { storeToRefs } from "pinia";
import { ref } from "vue";

const authStore = useAuthStore();
const { isLoggedIn, hasPermission } = storeToRefs(authStore);
const isLoggingOut = ref<boolean>(false);

const isProduction = import.meta.env.MODE === "production";

const logout = async () => {
    try {
        isLoggingOut.value = true;
        await authStore.logout();
    } finally {
        isLoggingOut.value = false;
    }
};
</script>

<template>
    <div class="container home-container">
        <article>
            <h1>Control</h1>

            <ul>
                <template v-if="isLoggedIn">
                    <template v-if="hasPermission('rebinding')">
                        <li><router-link to="/rebinding" class="contrast">Rebinding</router-link></li>
                    </template>
                </template>

                <li><router-link to="/tools" class="contrast">Verktøy</router-link></li>

                <li><a href="https://nortel.no" target="_blank" class="contrast">Nortel.no</a></li>

                <li v-if="!isProduction">
                    <a href="http://127.0.0.1:1080/" target="_blank" class="contrast">MailCatcher (dev)</a>
                </li>

                <li v-if="!isProduction">
                    <a href="http://127.0.0.1:9000/" target="_blank" class="contrast">Graylog (dev)</a>
                </li>
            </ul>

            <footer>
                <router-link v-if="!isLoggedIn" class="contrast" to="/login">Logg inn</router-link>
                <a v-else :aria-busy="isLoggingOut" @click="logout()">
                    <span v-if="isLoggingOut">Vennligst vent...</span>
                    <span v-else>Logg ut</span>
                </a>
            </footer>
        </article>
    </div>
</template>

<style scoped>
.logo {
    width: 12rem;
    margin-bottom: 12px;
}

footer span:hover {
    cursor: pointer;
}
</style>
