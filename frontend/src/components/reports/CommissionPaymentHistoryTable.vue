<script setup>
defineProps({
    payments: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["delete","print-voucher"]);

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

            <p class="text-sm text-slate-500">
                Recorded commission payments to agents.
            </p>
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
                            Sale
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

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                            Loading payment history...
                        </td>
                    </tr>

                    <tr
                        v-for="payment in payments"
                        v-else
                        :key="payment.id"
                        class="hover:bg-slate-50"
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

                        <td class="px-4 py-4 text-right">
                           <td class="px-4 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    @click="emit('print-voucher', payment)"
                                    class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100"
                                >
                                    Print Voucher
                                </button>

                                <button
                                    type="button"
                                    @click="emit('delete', payment)"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                        </td>
                    </tr>

                    <tr v-if="!loading && payments.length === 0">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                            No commission payments recorded yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>