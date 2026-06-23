<script setup>
defineProps({
    sales: {
        type: Object,
        required: true,
    },
});

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });
</script>

<template>
    <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h3 class="font-semibold">
                Commission Details Per Sale
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                            Sale No.
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                            Client
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                            Agent
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                            Contract Price
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase">
                            Rate
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                            Commission
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="sale in sales.data"
                        :key="sale.id"
                    >
                        <td class="px-4 py-4">
                            {{ sale.sale_no }}
                        </td>

                        <td class="px-4 py-4">
                            {{ sale.client?.first_name }}
                            {{ sale.client?.last_name }}
                        </td>

                        <td class="px-4 py-4">
                            {{ sale.agent?.first_name }}
                            {{ sale.agent?.last_name }}
                        </td>

                        <td class="px-4 py-4 text-right">
                            {{ money(sale.contract_price) }}
                        </td>

                        <td class="px-4 py-4 text-center">
                            {{ sale.commission_rate }}%
                        </td>

                        <td class="px-4 py-4 text-right font-semibold text-green-700">
                            {{ money(sale.commission_earned) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>