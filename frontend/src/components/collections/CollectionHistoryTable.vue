<script setup>
import StatusBadge from "@/components/common/StatusBadge.vue";

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

const formatMethod = (method) => {
    if (!method) return "—";

    return method.replace("_", " ");
};
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div v-if="loading" class="p-6 text-center text-sm text-slate-500">
            Loading collection history...
        </div>

        <div v-else-if="collections.length === 0" class="p-6 text-center text-sm text-slate-500">
            No payments recorded yet.
        </div>

        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Collection
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            OR No.
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Date
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Method
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="collection in collections"
                        :key="collection.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                            {{ collection.collection_no }}
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ collection.official_receipt_no || "—" }}
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ formatDate(collection.payment_date) }}
                        </td>

                        <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">
                            {{ formatMoney(collection.amount_paid) }}
                        </td>

                        <td class="px-4 py-3 text-sm capitalize text-slate-700">
                            {{ formatMethod(collection.payment_method) }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <StatusBadge :value="collection.status" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>