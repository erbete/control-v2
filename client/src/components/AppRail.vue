<script lang="ts" setup>
import { storeToRefs } from "pinia";
import { ref, watch } from "vue";
import { RouterLink } from "vue-router";

import { useAuthStore } from "@/stores/auth";

const currentTheme = window.localStorage.getItem("controlAppTheme") ?? "dark";
document.documentElement.dataset.theme = currentTheme;
const theme = ref<string>(currentTheme);

watch(theme, () => {
    document.documentElement.dataset.theme = theme.value;
    window.localStorage.setItem("controlAppTheme", theme.value);
});

const lightswitch = () => {
    theme.value = theme.value === "dark" ? "light" : "dark";
};

const authStore = useAuthStore();
const { isLoggedIn, hasPermission } = storeToRefs(authStore);
</script>

<template>
    <nav class="app-rail-container">
        <router-link to="/" class="app-rail-item">
            <img src="@/assets/favicon.ico" alt="Home Link Image" />
        </router-link>

        <template v-if="isLoggedIn">
            <router-link v-if="hasPermission('admin')" to="/admin" class="app-rail-item">
                <div class="app-rail-item-content">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Admin</span>
                </div>
            </router-link>

            <router-link v-if="hasPermission('rebinding')" to="/rebinding" class="app-rail-item">
                <div class="app-rail-item-content">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M2 4.5A2.5 2.5 0 014.5 2h11a2.5 2.5 0 010 5h-11A2.5 2.5 0 012 4.5zM2.75 9.083a.75.75 0 000 1.5h14.5a.75.75 0 000-1.5H2.75zM2.75 12.663a.75.75 0 000 1.5h14.5a.75.75 0 000-1.5H2.75zM2.75 16.25a.75.75 0 000 1.5h14.5a.75.75 0 100-1.5H2.75z" />
                    </svg>
                    <span>Rebinding</span>
                </div>
            </router-link>
        </template>

        <router-link to="/tools" class="app-rail-item">
            <div class="app-rail-item-content">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M14.5 10a4.5 4.5 0 004.284-5.882c-.105-.324-.51-.391-.752-.15L15.34 6.66a.454.454 0 01-.493.11 3.01 3.01 0 01-1.618-1.616.455.455 0 01.11-.494l2.694-2.692c.24-.241.174-.647-.15-.752a4.5 4.5 0 00-5.873 4.575c.055.873-.128 1.808-.8 2.368l-7.23 6.024a2.724 2.724 0 103.837 3.837l6.024-7.23c.56-.672 1.495-.855 2.368-.8.096.007.193.01.291.01zM5 16a1 1 0 11-2 0 1 1 0 012 0z"
                        clip-rule="evenodd" />
                    <path
                        d="M14.5 11.5c.173 0 .345-.007.514-.022l3.754 3.754a2.5 2.5 0 01-3.536 3.536l-4.41-4.41 2.172-2.607c.052-.063.147-.138.342-.196.202-.06.469-.087.777-.067.128.008.257.012.387.012zM6 4.586l2.33 2.33a.452.452 0 01-.08.09L6.8 8.214 4.586 6H3.309a.5.5 0 01-.447-.276l-1.7-3.402a.5.5 0 01.093-.577l.49-.49a.5.5 0 01.577-.094l3.402 1.7A.5.5 0 016 3.31v1.277z" />
                </svg>
                <span>Verktøy</span>
            </div>
        </router-link>

        <router-link to="/theme" class="app-rail-item">
            <div class="app-rail-item-content">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M14.5 10a4.5 4.5 0 004.284-5.882c-.105-.324-.51-.391-.752-.15L15.34 6.66a.454.454 0 01-.493.11 3.01 3.01 0 01-1.618-1.616.455.455 0 01.11-.494l2.694-2.692c.24-.241.174-.647-.15-.752a4.5 4.5 0 00-5.873 4.575c.055.873-.128 1.808-.8 2.368l-7.23 6.024a2.724 2.724 0 103.837 3.837l6.024-7.23c.56-.672 1.495-.855 2.368-.8.096.007.193.01.291.01zM5 16a1 1 0 11-2 0 1 1 0 012 0z"
                        clip-rule="evenodd" />
                    <path
                        d="M14.5 11.5c.173 0 .345-.007.514-.022l3.754 3.754a2.5 2.5 0 01-3.536 3.536l-4.41-4.41 2.172-2.607c.052-.063.147-.138.342-.196.202-.06.469-.087.777-.067.128.008.257.012.387.012zM6 4.586l2.33 2.33a.452.452 0 01-.08.09L6.8 8.214 4.586 6H3.309a.5.5 0 01-.447-.276l-1.7-3.402a.5.5 0 01.093-.577l.49-.49a.5.5 0 01.577-.094l3.402 1.7A.5.5 0 016 3.31v1.277z" />
                </svg>
                <span>Theme</span>
            </div>
        </router-link>

        <div class="app-rail-item lightswitch">
            <div class="app-rail-item-content" @click="lightswitch()">
                <svg v-if="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#FDB813">
                    <path
                        d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM10 7a3 3 0 100 6 3 3 0 000-6zM15.657 5.404a.75.75 0 10-1.06-1.06l-1.061 1.06a.75.75 0 001.06 1.06l1.06-1.06zM6.464 14.596a.75.75 0 10-1.06-1.06l-1.06 1.06a.75.75 0 001.06 1.06l1.06-1.06zM18 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0118 10zM5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 015 10zM14.596 15.657a.75.75 0 001.06-1.06l-1.06-1.061a.75.75 0 10-1.06 1.06l1.06 1.06zM5.404 6.464a.75.75 0 001.06-1.06l-1.06-1.06a.75.75 0 10-1.061 1.06l1.06 1.06z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.455 2.004a.75.75 0 01.26.77 7 7 0 009.958 7.967.75.75 0 011.067.853A8.5 8.5 0 116.647 1.921a.75.75 0 01.808.083z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.app-rail-container {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    position: fixed;
    height: 100vh;
    width: 65px;
    border-right: 1px solid var(--pico-secondary-focus);
}

.app-rail-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 80px;
    width: 100%;
    margin: 0;
    padding: 0;
    border-radius: 0px;
    color: var(--pico-contrast);
    background-color: transparent;
    text-decoration: none;
}

.app-rail-item:not(.router-link-active):not(.app-rail-item:first-child):not(.lightswitch):hover {
    cursor: pointer;
    background-color: var(--pico-primary);
    color: var(--pico-primary-inverse);
}

.app-rail-item img {
    width: 50px;
    height: auto;
}

.app-rail-item-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-weight: bold;
    margin-top: 5px;
}

.app-rail-item-content svg {
    width: 26px;
}

.app-rail-item-content span {
    font-size: 10.5px;
    padding: 5px;
}

.router-link-active:not(.app-rail-item:first-child) {
    background-color: var(--pico-primary);
    color: var(--pico-primary-inverse);
}

.lightswitch {
    margin-top: auto;
}

.lightswitch svg:hover {
    cursor: pointer;
    width: 1.7rem;
    transition: all .4s ease !important;
}

@media only screen and (min-width: 480px) {
    .app-rail-container {
        width: 80px;
    }
}
</style>
