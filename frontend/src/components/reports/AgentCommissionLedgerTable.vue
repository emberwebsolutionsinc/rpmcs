<script setup>
defineProps({
    rows: {
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
    <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h3 class="font-semibold text-slate-900">
                Sales Commission Ledger
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Client / Property
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Contract
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
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-for="row in rows" :key="row.sale_id">
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">
                                {{ row.sale_no }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ date(row.sale_date) }}
                            </p>
                        </td>

                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">
                                {{ row.client?.first_name }}
                                {{ row.client?.last_name }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ row.project?.project_name || "—" }}
                                /
                                {{ row.lot?.lot_no || "—" }}
                            </p>
                        </td>

                        <td class="px-4 py-4 text-right">
                            {{ money(row.contract_price) }}
                        </td>

                        <td class="px-4 py-4 text-center">
                            {{ row.commission_rate }}%
                        </td>

                        <td class="px-4 py-4 text-right font-semibold text-emerald-700">
                            {{ money(row.commission_earned) }}
                        </td>

                        <td class="px-4 py-4 text-right font-semibold text-blue-700">
                            {{ money(row.commission_paid) }}
                        </td>

                        <td class="px-4 py-4 text-right font-semibold text-red-700">
                            {{ money(row.commission_deleted) }}
                        </td>

                        <td class="px-4 py-4 text-right font-bold text-slate-900">
                            {{ money(row.commission_balance) }}
                        </td>
                    </tr>

                    <tr v-if="rows.length === 0">
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">
                            No sales ledger found for this agent.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>