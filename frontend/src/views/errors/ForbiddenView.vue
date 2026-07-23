<template>
    <div
        class="min-h-screen flex items-center justify-center bg-gray-50 px-6"
    >
        <div class="max-w-lg text-center">
            <div class="text-7xl font-bold text-gray-800">
                403
            </div>

            <h1 class="mt-4 text-2xl font-semibold text-gray-900">
                Access Denied
            </h1>

            <p class="mt-3 text-gray-600">
                You do not have permission to access this page.
            </p>

            <div class="mt-8 flex justify-center gap-3">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-gray-700 hover:bg-gray-100"
                    @click="goBack"
                >
                    Go Back
                </button>

                <button
                    type="button"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-white hover:bg-indigo-700"
                    @click="goToDashboard"
                >
                    Return to Dashboard
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from "vue-router";

const router = useRouter();

const goBack = () => {
    if (window.history.length > 1) {
        router.back();
        return;
    }

    router.replace({
        name: "dashboard",
    });
};

const goToDashboard = () => {
    router.replace({
        name: "dashboard",
    });
};
</script>