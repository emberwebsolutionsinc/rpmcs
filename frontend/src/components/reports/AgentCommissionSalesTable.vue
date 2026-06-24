<script setup>
defineProps({
    sales: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["record-payment"]);

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
                            Earned
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                            Paid
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                            Balance
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                            Action
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

                        <td class="px-4 py-4 text-right font-semibold text-blue-700">
                            {{ money(sale.commission_paid) }}
                        </td>

                        <td class="px-4 py-4 text-right font-semibold text-red-700">
                            {{ money(sale.commission_balance) }}
                        </td>

                        <td class="px-4 py-4 text-right">
                            <span
                                v-if="Number(sale.commission_balance || 0) <= 0"
                                class="rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700"
                            >
                                Paid
                            </span>

                            <button
                                v-else
                                type="button"
                                @click="emit('record-payment', sale)"
                                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700"
                            >
                                Record Payment
                            </button>
                        </td>
                    </tr>

                    <tr v-if="sales.data.length === 0">
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-slate-500">
                            No commissionable sales found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>