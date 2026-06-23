<script setup>
import StatusBadge from "@/components/common/StatusBadge.vue";

defineProps({
    sales: {
        type: Array,
        default: () => [],
    },

    loading: {
        type: Boolean,
        default: false,
    },
});

const fullName = (person) => {
    if (!person) return "—";

    return (
        person.full_name ||
        [person.first_name, person.middle_name, person.last_name]
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
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Client
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Agent
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Property
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Contract Price
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Downpayment
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Balance
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale Date
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="9" class="px-5 py-10 text-center text-sm text-slate-500">
                            Loading sales report...
                        </td>
                    </tr>

                    <tr
                        v-for="sale in sales"
                        v-else
                        :key="sale.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-900">
                                {{ sale.sale_no }}
                            </p>

                            <p class="text-xs text-slate-500">
                                Reservation: {{ sale.reservation?.reservation_no || "—" }}
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">
                                {{ fullName(sale.client) }}
                            </p>

                            <p class="text-xs text-slate-500">
                                {{ sale.client?.client_code || "—" }}
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">
                                {{ fullName(sale.agent) }}
                            </p>

                            <p class="text-xs text-slate-500">
                                {{ sale.agent?.agent_code || "No Agent" }}
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900">
                                {{ sale.lot?.project?.project_name || "—" }}
                            </p>

                            <p class="text-xs text-slate-500">
                                {{ sale.lot?.lot_no || "—" }}
                            </p>
                        </td>

                        <td class="px-5 py-4 text-right text-sm font-bold text-slate-900">
                            {{ formatMoney(sale.contract_price) }}
                        </td>

                        <td class="px-5 py-4 text-right text-sm font-bold text-emerald-700">
                            {{ formatMoney(sale.downpayment) }}
                        </td>

                        <td class="px-5 py-4 text-right text-sm font-bold text-red-700">
                            {{ formatMoney(sale.balance) }}
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-700">
                            {{ formatDate(sale.sale_date) }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            <StatusBadge :value="sale.status" />
                        </td>
                    </tr>

                    <tr v-if="!loading && sales.length === 0">
                        <td colspan="9" class="px-5 py-10 text-center text-sm text-slate-500">
                            No sales records found for this report.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>