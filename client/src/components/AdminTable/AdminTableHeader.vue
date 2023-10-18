<script setup lang="ts">
import { ref } from "vue";
import { AdminTableTab } from "@/components/AdminTable/adminTableTab";
import { useRouter } from "vue-router";

// Cache active tab value to remember the position if the user is routed elsewhere
const activeTabValue = sessionStorage.getItem("activeTab");
if (!activeTabValue) sessionStorage.setItem("activeTab", "0");
const activeTab = ref(Number(activeTabValue));
const router = useRouter();

const emits = defineEmits<{ (e: "onChangeTab", value: number): void }>();

const routeTo = (routeName: string) => {
    router.push({ name: routeName });
};

const handleClick = (value: number) => {
    activeTab.value = value;
    emits("onChangeTab", value);
};
</script>

<template>
    <nav class="tabs">
        <div class="tabs-container">
            <a :class="{ 'active-tab': activeTab === AdminTableTab.Users }" @click="handleClick(AdminTableTab.Users)">
                <div class="tabs-content">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span>Brukere</span>
                </div>
            </a>

            <a :class="{ 'active-tab': activeTab === AdminTableTab.Permissions }"
                @click="handleClick(AdminTableTab.Permissions)">
                <div class="tabs-content">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <span>Tilganger</span>
                </div>
            </a>
        </div>

        <div class="btn-container">
            <button @click="routeTo('CreateUser')" class="add-btn" v-if="activeTab === AdminTableTab.Users">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="add-btn-icon">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
                <span>Opprett ny bruker</span>
            </button>

            <button @click="routeTo('CreatePermission')" v-else class="add-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="add-btn-icon">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Opprett ny tilgang</span>
            </button>
        </div>
    </nav>
</template>

<style scoped>
.tabs {
    border-top-left-radius: 2px;
    border-top-right-radius: 2px;
    border-bottom: 1px solid var(--pico-secondary-focus);
    background-color: var(--pico-card-background-color);
    width: 480px;
    height: 100px;
    padding: 0 20px;
}

.tabs-container {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    width: 100%;
}

.btn-container {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    width: 100%;
}

.tabs-container a {
    color: var(--pico-secondary);
    text-decoration: none;
    margin-right: 30px;
}

.tabs-container a:hover {
    cursor: pointer;
}

.tabs-container a:hover {
    color: var(--pico-contrast);
}

.active-tab {
    color: var(--pico-contrast) !important;
}

.tabs-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-weight: bold;
    font-size: 16px;
}

.tabs-content svg {
    width: 24px;
}

.add-btn {
    margin: 0;
    font-size: 16px;
}

.add-btn-icon {
    margin-right: 6px;
    width: 24px;
}
</style>
