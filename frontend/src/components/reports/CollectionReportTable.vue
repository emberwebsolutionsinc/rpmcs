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

const fullName = (client) => {
    if (!client) return "—";

    return (
        client.full_name ||
        [client.first_name, client.middle_name, client.last_name]
            .filter(Boolean)
            .join(" ") ||
        "—"
    );
};

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
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Collection
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            OR No.
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Client
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Property
                        </th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Method
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Date
                        </th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">
                            Loading collection report...
                        </td>
                    </tr>

                    <tr
                        v-for="collection in collections"
                        v-else
                        :key="collection.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-5 py-4 text-sm font-semibold text-slate-900">
                            {{ collection.collection_no }}
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-700">
                            {{ collection.official_receipt_no || "—" }}
                        </td>

                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">
                                {{ fullName(collection.sale?.client) }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ collection.sale?.client?.client_code || "—" }}
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">
                                {{ collection.sale?.lot?.project?.project_name || "—" }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Lot {{ collection.sale?.lot?.lot_no || "—" }}
                            </p>
                        </td>

                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-900">
                            {{ formatMoney(collection.amount_paid) }}
                        </td>

                        <td class="px-5 py-4 text-sm capitalize text-slate-700">
                            {{ formatMethod(collection.payment_method) }}
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-700">
                            {{ formatDate(collection.payment_date) }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            <StatusBadge :value="collection.status" />
                        </td>
                    </tr>

                    <tr v-if="!loading && collections.length === 0">
                        <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">
                            No collection records found for this report.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>