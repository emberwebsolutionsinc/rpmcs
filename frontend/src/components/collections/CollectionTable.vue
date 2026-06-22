<script setup>
import StatusBadge from "@/components/common/StatusBadge.vue";

import {
    Receipt,
    User,
    CalendarDays,
    Wallet,
    RotateCcw,
} from "lucide-vue-next";

defineProps({
    collections: {
        type: Array,
        default: () => [],
    },

    loading: {
        type: Boolean,
        default: false,
    },
});

defineEmits([
    "view",
    "void",
]);

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });
};

const formatDate = (date) => {
    if (!date) return "—";

    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
};

const clientName = (collection) => {
    const client = collection.sale?.client;

    if (!client) return "—";

    return (
        client.full_name ||
        [client.first_name, client.middle_name, client.last_name]
            .filter(Boolean)
            .join(" ") ||
        "—"
    );
};
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Collection
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Client
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Sale
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Amount
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Method
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr v-if="loading">
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">
                            Loading collections...
                        </td>
                    </tr>

                    <tr
                        v-for="collection in collections"
                        v-else
                        :key="collection.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                    <Receipt class="h-5 w-5" />
                                </div>

                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ collection.collection_no }}
                                    </p>

                                    <p class="flex items-center gap-1 text-xs text-slate-500">
                                        <CalendarDays class="h-3.5 w-3.5" />
                                        {{ formatDate(collection.payment_date) }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-sm text-slate-700">
                                <User class="h-4 w-4 text-slate-400" />
                                {{ clientName(collection) }}
                            </div>
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-700">
                            <p class="font-medium text-slate-900">
                                {{ collection.sale?.sale_no || "—" }}
                            </p>

                            <p class="text-xs text-slate-500">
                                OR: {{ collection.official_receipt_no || "—" }}
                            </p>
                        </td>

                        <td class="px-6 py-4 text-right text-sm font-bold text-slate-900">
                            {{ formatMoney(collection.amount_paid) }}
                        </td>

                        <td class="px-6 py-4 text-sm capitalize text-slate-700">
                            <span class="inline-flex items-center gap-1">
                                <Wallet class="h-4 w-4 text-slate-400" />
                                {{ collection.payment_method?.replace("_", " ") || "—" }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <StatusBadge :value="collection.status" />
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <button
                                    @click="$emit('view', collection)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                >
                                    View
                                </button>
                                <button
                                    v-if="collection.status === 'posted'"
                                    @click="$emit('void', collection)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100"
                                >
                                    <RotateCcw class="h-4 w-4" />
                                    Void
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!loading && collections.length === 0">
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">
                            No collections found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>