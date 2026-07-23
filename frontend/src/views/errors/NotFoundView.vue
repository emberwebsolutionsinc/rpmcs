<template>
    <div
        class="min-h-screen flex items-center justify-center bg-gray-100 px-6"
    >
        <div
            class="w-full max-w-lg rounded-xl bg-white p-10 text-center shadow-lg"
        >
            <h1
                class="text-6xl font-bold text-gray-900"
            >
                404
            </h1>

            <h2
                class="mt-4 text-2xl font-semibold text-gray-800"
            >
                Page Not Found
            </h2>

            <p class="mt-4 text-gray-600">
                The page you requested does not exist
                or may have been moved.
            </p>

            <button
                type="button"
                class="mt-8 rounded-lg bg-indigo-600 px-5 py-2.5 text-white hover:bg-indigo-700"
                @click="goToDashboard"
            >
                Return to Dashboard
            </button>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from "vue-router";

const router = useRouter();

const goToDashboard = () => {
    router.replace({
        name: "dashboard",
    });
};
</script>