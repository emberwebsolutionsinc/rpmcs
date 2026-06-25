<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    rows: {
        type: Array,
        default: () => [],
    },
    type: {
        type: String,
        default: "agent",
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
                {{ title }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            {{ type === "method" ? "Payment Method" : "Agent" }}
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Count
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="row in rows"
                        :key="type === 'method' ? row.payment_method : row.agent_id"
                    >
                        <td class="px-4 py-4">
                            <template v-if="type === 'method'">
                                <span class="capitalize">
                                    {{ row.payment_method?.replace("_", " ") || "—" }}
                                </span>
                            </template>

                            <template v-else>
                                <p class="font-semibold text-slate-900">
                                    {{ row.agent_name }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ row.agent_code }}
                                </p>
                            </template>
                        </td>

                        <td class="px-4 py-4 text-center">
                            {{ row.payment_count }}
                        </td>

                        <td class="px-4 py-4 text-right font-semibold text-emerald-700">
                            {{ money(row.total_amount) }}
                        </td>
                    </tr>

                    <tr v-if="rows.length === 0">
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">
                            No data found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>