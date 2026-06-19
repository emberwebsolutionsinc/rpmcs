<script setup>
import { ref } from "vue";
import Sidebar from "@/components/layout/Sidebar.vue";
import Topbar from "@/components/layout/Topbar.vue";

const sidebarOpen = ref(false);

const openSidebar = () => {
    sidebarOpen.value = true;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <Sidebar
            :open="sidebarOpen"
            @close="closeSidebar"
        />

        <div class="lg:pl-72">
            <Topbar @open-sidebar="openSidebar" />

            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>