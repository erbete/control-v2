<script setup lang="ts">
import { ref } from "vue";
import { AdminTableTab } from "@/components/AdminTable/adminTableTab";
import AdminTableHeader from "./AdminTableHeader.vue";
import AdminTableUserRows from "./AdminTableUserRows.vue";
import AdminTablePermissionRows from "./AdminTablePermissionRows.vue";

// Cache active tab value for when routing back to the table view
const activeTab = ref(Number(sessionStorage.getItem("activeTab")) ?? 0);
const setActiveTab = (value: number) => {
    sessionStorage.setItem("activeTab", value.toString());
    activeTab.value = value;
};
</script>

<template>
    <figure>
        <AdminTableHeader @onChangeTab="setActiveTab($event)" />

        <Suspense v-if="activeTab === AdminTableTab.Users">
            <AdminTableUserRows />

            <template #fallback>
                <div class="fallback-container">
                    <span aria-busy="true">Laster brukere, vennligst vent...</span>
                </div>
            </template>
        </Suspense>

        <Suspense v-else>
            <AdminTablePermissionRows />

            <template #fallback>
                <div class="fallback-container">
                    <span aria-busy="true">Laster tilganger, vennligst vent...</span>
                </div>
            </template>
        </Suspense>
    </figure>
</template>

<style scoped>
.fallback-container {
    margin-top: 80px;
    text-align: center;
}
</style>
