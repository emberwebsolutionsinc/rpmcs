<script setup>
defineProps({
    currentPage: {
        type: Number,
        required: true,
    },

    lastPage: {
        type: Number,
        required: true,
    },

    total: {
        type: Number,
        required: true,
    },
});

defineEmits([
    "previous",
    "next",
]);
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-sm text-slate-500">
            Showing page
            <span class="font-semibold text-slate-700">
                {{ currentPage }}
            </span>
            of
            <span class="font-semibold text-slate-700">
                {{ lastPage }}
            </span>
            —
            <span class="font-semibold text-slate-700">
                {{ total }}
            </span>
            total records
        </p>

        <div class="flex gap-2">
            <button
                @click="$emit('previous')"
                :disabled="currentPage === 1"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Previous
            </button>

            <button
                @click="$emit('next')"
                :disabled="currentPage === lastPage"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Next
            </button>
        </div>
    </div>
</template>