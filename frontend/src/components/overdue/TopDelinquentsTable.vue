<script setup>
defineProps({
    records: {
        type: Array,
        default: () => [],
    },
});

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });
};

const fullName = (client) => {
    if (!client) return "—";

    return [
        client.first_name,
        client.middle_name,
        client.last_name,
    ]
        .filter(Boolean)
        .join(" ");
};
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="font-semibold text-slate-900">
                Top Delinquent Accounts
            </h3>

            <p class="text-sm text-slate-500">
                Highest overdue balances.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase">
                            Client
                        </th>

                        <th class="px-4 py-3 text-left text-xs uppercase">
                            Property
                        </th>

                        <th class="px-4 py-3 text-center text-xs uppercase">
                            Installments
                        </th>

                        <th class="px-4 py-3 text-right text-xs uppercase">
                            Overdue Balance
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="record in records"
                        :key="record.sale_id"
                        class="border-t"
                    >
                        <td class="px-4 py-3">
                            {{ fullName(record.client) }}
                        </td>

                        <td class="px-4 py-3">
                            {{ record.project }}
                            —
                            Lot {{ record.lot_no }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ record.overdue_installments }}
                        </td>

                        <td
                            class="px-4 py-3 text-right font-bold text-red-700"
                        >
                            {{ formatMoney(record.total_overdue_balance) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>