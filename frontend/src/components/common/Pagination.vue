<script setup>
import { computed } from "vue";

const props = defineProps({
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

const emit = defineEmits(["previous", "next", "goToPage"]);

const pages = computed(() => {
    const items = [];
    const start = Math.max(1, props.currentPage - 2);
    const end = Math.min(props.lastPage, props.currentPage + 2);

    for (let page = start; page <= end; page++) {
        items.push(page);
    }

    return items;
});
</script>

<template>
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">
            Page
            <span class="font-semibold text-slate-700">{{ currentPage }}</span>
            of
            <span class="font-semibold text-slate-700">{{ lastPage }}</span>
            —
            <span class="font-semibold text-slate-700">{{ total }}</span>
            total records
        </p>

        <div class="flex flex-wrap items-center gap-2">
            <button
                @click="$emit('previous')"
                :disabled="currentPage === 1"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Previous
            </button>

            <button
                v-for="page in pages"
                :key="page"
                @click="$emit('goToPage', page)"
                class="rounded-lg border px-3 py-2 text-sm"
                :class="
                    page === currentPage
                        ? 'border-emerald-600 bg-emerald-600 text-white'
                        : 'border-slate-300 hover:bg-slate-50'
                "
            >
                {{ page }}
            </button>

            <button
                @click="$emit('next')"
                :disabled="currentPage === lastPage"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Next
            </button>
        </div>
    </div>
</template>