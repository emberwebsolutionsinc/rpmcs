<script setup>
import { Search } from "lucide-vue-next";

defineProps({
    search: {
        type: String,
        default: "",
    },

    status: {
        type: String,
        default: "",
    },

    perPage: {
        type: Number,
        default: 10,
    },
});

defineEmits([
    "update:search",
    "update:status",
    "update:perPage",
    "search",
]);
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="relative w-full xl:max-w-lg">
                <Search class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" />

                <input
                    :value="search"
                    @input="$emit('update:search', $event.target.value)"
                    @keyup.enter="$emit('search')"
                    type="text"
                    placeholder="Search sale no, client, or lot..."
                    class="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <select
                    :value="status"
                    @change="$emit('update:status', $event.target.value)"
                    class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="fully_paid">Fully Paid</option>
                </select>

                <select
                    :value="perPage"
                    @change="$emit('update:perPage', Number($event.target.value))"
                    class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                >
                    <option :value="10">10 rows</option>
                    <option :value="25">25 rows</option>
                    <option :value="50">50 rows</option>
                </select>

                <button
                    @click="$emit('search')"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-50"
                >
                    Search
                </button>
            </div>
        </div>
    </div>
</template>