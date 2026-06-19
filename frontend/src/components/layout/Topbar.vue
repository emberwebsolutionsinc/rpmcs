<script setup>
import { Menu } from "lucide-vue-next";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

defineEmits(["open-sidebar"]);

const authStore = useAuthStore();
const router = useRouter();

const logout = async () => {
    await authStore.logout();
    router.push("/login");
};
</script>

<template>
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 sm:px-6 py-4">
        <div class="flex justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <button
                    @click="$emit('open-sidebar')"
                    class="lg:hidden rounded-lg border border-slate-300 p-2 text-slate-700"
                >
                    <Menu class="h-5 w-5" />
                </button>

                <div>
                    <h2 class="font-semibold text-slate-900 text-sm sm:text-base">
                        Real Property Management and Collection System
                    </h2>

                  
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-sm text-slate-600">
                    {{ authStore.user?.name }}
                </span>

                <button
                    @click="logout"
                    class="rounded-lg bg-red-600 px-3 sm:px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    Logout
                </button>
            </div>
        </div>
    </header>
</template>