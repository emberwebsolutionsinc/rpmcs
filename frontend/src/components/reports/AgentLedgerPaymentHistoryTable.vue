<script setup>
defineProps({
    payments: {
        type: Array,
        default: () => [],
    },
    deleted: {
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
            <h3
                class="font-semibold"
                :class="deleted ? 'text-red-700' : 'text-slate-900'"
            >
                {{ deleted ? "Deleted Payment History" : "Active Payment History" }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ deleted ? "Deleted At" : "Payment Date" }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Method / Ref.
                        </th>
                        <th
                            v-if="deleted"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500"
                        >
                            Reason
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-for="payment in payments" :key="payment.id">
                        <td class="px-4 py-4 text-sm">
                            {{ date(deleted ? payment.deleted_at : payment.payment_date) }}
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

                        <td
                            class="px-4 py-4 text-right font-bold"
                            :class="deleted ? 'text-red-700' : 'text-emerald-700'"
                        >
                            {{ money(payment.amount) }}
                        </td>

                        <td class="px-4 py-4 text-sm">
                            <p class="capitalize">
                                {{ payment.payment_method?.replace("_", " ") || "—" }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ payment.reference_no || "—" }}
                            </p>
                        </td>

                        <td
                            v-if="deleted"
                            class="px-4 py-4 text-sm"
                        >
                            {{ payment.delete_reason || "—" }}
                        </td>
                    </tr>

                    <tr v-if="payments.length === 0">
                        <td
                            :colspan="deleted ? 5 : 4"
                            class="px-4 py-8 text-center text-sm text-slate-500"
                        >
                            No payment records found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>