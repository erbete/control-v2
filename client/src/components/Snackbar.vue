<script setup lang="ts">
import { storeToRefs } from "pinia";
import { useAppStore } from "@/stores/app";

const store = useAppStore();
const { snackbar } = storeToRefs(store);
</script>

<template>
    <div v-if="snackbar.show" class="snackbar">
        <div class="snackbar-content">
            <span class="contrast">{{ snackbar.text }}</span>
            <button :style="{ backgroundColor: snackbar.color }" class="close-btn" @click="snackbar.show = false"
                type="button">OK</button>
        </div>
    </div>
</template>

<style scoped>
.snackbar {
    background-color: var(--pico-card-background-color);
    width: 100%;
    max-width: 640px;
    border-radius: 8px;
    position: fixed;
    bottom: 50px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1;
    font-size: 16px;
}

.snackbar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px;
}

.close-btn {
    max-width: 100px;
    width: 100%;
    font-size: 16px;
    padding: 4px;
    margin: 0;
    cursor: pointer;
}
</style>
