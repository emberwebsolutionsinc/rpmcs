<script setup>
defineProps({
    sales: {
        type: Array,
        default: () => [],
    },
});

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

const commissionStatus = (sale) => {
    const earned = Number(sale.commission_earned || 0);
    const paid = Number(sale.commission_paid || 0);

    if (earned <= 0) return "No Commission";
    if (paid <= 0) return "Unpaid";
    if (paid >= earned) return "Fully Paid";

    return "Partially Paid";
};

const commissionStatusClass = (sale) => {
    const status = commissionStatus(sale);

    if (status === "Fully Paid") {
        return "bg-emerald-100 text-emerald-700";
    }

    if (status === "Partially Paid") {
        return "bg-amber-100 text-amber-700";
    }

    if (status === "Unpaid") {
        return "bg-red-100 text-red-700";
    }

    return "bg-slate-100 text-slate-600";
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                        Sale
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                        Rate
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                        Earned
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                        Paid
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                        Deleted
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                        Balance
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                        Status
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                <tr
                    v-for="sale in sales"
                    :key="sale.sale_id || sale.id"
                    class="hover:bg-slate-50"
                >
                    <td class="px-4 py-4 font-semibold text-slate-900">
                        {{ sale.sale_no || "—" }}
                    </td>

                    <td class="px-4 py-4 text-center">
                        {{ sale.commission_rate || 0 }}%
                    </td>

                    <td class="px-4 py-4 text-right font-semibold text-emerald-700">
                        {{ money(sale.commission_earned) }}
                    </td>

                    <td class="px-4 py-4 text-right font-semibold text-blue-700">
                        {{ money(sale.commission_paid) }}
                    </td>

                    <td class="px-4 py-4 text-right font-semibold text-red-700">
                        {{ money(sale.commission_deleted) }}
                    </td>

                    <td class="px-4 py-4 text-right font-bold text-slate-900">
                        {{ money(sale.commission_balance) }}
                    </td>

                    <td class="px-4 py-4 text-center">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="commissionStatusClass(sale)"
                        >
                            {{ commissionStatus(sale) }}
                        </span>
                    </td>
                </tr>

                <tr v-if="sales.length === 0">
                    <td
                        colspan="7"
                        class="px-4 py-8 text-center text-sm text-slate-500"
                    >
                        No commission records found.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>