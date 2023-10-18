<script setup lang="ts">
import AdminTable from "@/components/AdminTable/AdminTable.vue";
</script>

<template>
    <section class="container">
        <AdminTable v-if="$route.name === 'Admin'" />
        <router-view v-else />
    </section>
</template>
