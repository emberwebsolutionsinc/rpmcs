<script setup>
defineProps({
    agents: {
        type: Array,
        default: () => [],
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
            <h3 class="font-semibold text-slate-900">
                Agent Commission Summary
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                            Agent
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase">
                            Sales
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
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="agent in agents"
                        :key="agent.agent_id"
                    >
                        <td class="px-4 py-4">
                            <div class="font-semibold">
                                {{ agent.agent_name }}
                            </div>

                            <div class="text-xs text-slate-500">
                                {{ agent.agent_code }}
                            </div>
                        </td>

                        <td class="px-4 py-4 text-center">
                            {{ agent.sales_count }}
                        </td>

                        <td class="px-4 py-4 text-right">
                            {{ money(agent.total_contract_price) }}
                        </td>

                        <td class="px-4 py-4 text-center">
                            {{ agent.commission_rate }}%
                        </td>

                        <td class="px-4 py-4 text-right text-green-700 font-semibold">
                            {{ money(agent.commission_earned) }}
                        </td>

                        <td class="px-4 py-4 text-right">
                            {{ money(agent.commission_paid) }}
                        </td>

                        <td class="px-4 py-4 text-right text-red-700 font-semibold">
                            {{ money(agent.commission_balance) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>