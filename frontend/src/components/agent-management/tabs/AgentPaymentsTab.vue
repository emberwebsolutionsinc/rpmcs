<script setup>
defineProps({
    payments: {
        type: Array,
        default: () => [],
    },
    deletedPayments: {
        type: Array,
        default: () => [],
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
    <div class="space-y-6">
        <div class="overflow-x-auto">
            <h3 class="mb-3 font-semibold text-slate-900">
                Active Commission Payments
            </h3>

            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Date
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Method / Reference
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Encoded By
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="payment in payments"
                        :key="payment.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-4 py-4">
                            {{ date(payment.payment_date) }}
                        </td>

                        <td class="px-4 py-4">
                            {{ payment.sale?.sale_no || "—" }}
                        </td>

                        <td class="px-4 py-4 text-right font-bold text-emerald-700">
                            {{ money(payment.amount) }}
                        </td>

                        <td class="px-4 py-4">
                            <p class="capitalize">
                                {{ payment.payment_method?.replace("_", " ") || "—" }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ payment.reference_no || "—" }}
                            </p>
                        </td>

                        <td class="px-4 py-4">
                            {{ payment.created_by?.name || payment.created_by?.email || "—" }}
                        </td>
                    </tr>

                    <tr v-if="payments.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-sm text-slate-500"
                        >
                            No active commission payments found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="overflow-x-auto">
            <h3 class="mb-3 font-semibold text-red-700">
                Deleted / Voided Commission Payments
            </h3>

            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Deleted At
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Reason
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Deleted By
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="payment in deletedPayments"
                        :key="payment.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-4 py-4">
                            {{ date(payment.deleted_at) }}
                        </td>

                        <td class="px-4 py-4">
                            {{ payment.sale?.sale_no || "—" }}
                        </td>

                        <td class="px-4 py-4 text-right font-bold text-red-700">
                            {{ money(payment.amount) }}
                        </td>

                        <td class="px-4 py-4">
                            {{ payment.delete_reason || "—" }}
                        </td>

                        <td class="px-4 py-4">
                            {{ payment.deleted_by?.name || payment.deleted_by?.email || "—" }}
                        </td>
                    </tr>

                    <tr v-if="deletedPayments.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-sm text-slate-500"
                        >
                            No deleted commission payments found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>