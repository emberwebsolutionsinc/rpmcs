<script setup>
defineProps({
    payments: {
        type: Object,
        default: () => ({
            data: [],
        }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

const date = (value) => {
    if (!value) return "—";

    return new Date(value).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
};
</script>

<template>
    <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h3 class="font-semibold text-slate-900">
                Commission Payment History
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Date
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Agent
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale / Client
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Method
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Reference
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Encoded By
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                            Loading payments...
                        </td>
                    </tr>

                    <tr
                        v-for="payment in payments.data"
                        v-else
                        :key="payment.id"
                    >
                        <td class="px-4 py-4 text-sm">
                            {{ date(payment.payment_date) }}
                        </td>

                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">
                                {{ payment.agent?.first_name }}
                                {{ payment.agent?.last_name }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ payment.agent?.agent_code || "—" }}
                            </p>
                        </td>

                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">
                                {{ payment.sale?.sale_no || "—" }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ payment.sale?.client?.first_name }}
                                {{ payment.sale?.client?.last_name }}
                            </p>
                        </td>

                        <td class="px-4 py-4 text-right font-bold text-emerald-700">
                            {{ money(payment.amount) }}
                        </td>

                        <td class="px-4 py-4 text-sm capitalize">
                            {{ payment.payment_method?.replace("_", " ") || "—" }}
                        </td>

                        <td class="px-4 py-4 text-sm">
                            {{ payment.reference_no || "—" }}
                        </td>

                        <td class="px-4 py-4 text-sm">
                            {{ payment.created_by?.name || payment.created_by?.email || "—" }}
                        </td>
                    </tr>

                    <tr v-if="!loading && payments.data.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                            No commission payments found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>