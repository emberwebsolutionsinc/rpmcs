<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },

    description: {
        type: String,
        default: "",
    },

    records: {
        type: Array,
        default: () => [],
    },

    labelKey: {
        type: String,
        required: true,
    },
});

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
        maximumFractionDigits: 0,
    });
};
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="font-semibold text-slate-900">
                {{ title }}
            </h3>

            <p v-if="description" class="mt-1 text-sm text-slate-500">
                {{ description }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Name
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Sales
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Contract Price
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Balance
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="record in records"
                        :key="record.project_id || record.agent_id || record[labelKey]"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-4 py-3 font-medium text-slate-900">
                            {{ record[labelKey] || "Unassigned" }}
                        </td>

                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700">
                            {{ record.total_sales || 0 }}
                        </td>

                        <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">
                            {{ formatMoney(record.total_contract_price) }}
                        </td>

                        <td class="px-4 py-3 text-right text-sm font-bold text-red-700">
                            {{ formatMoney(record.total_balance) }}
                        </td>
                    </tr>

                    <tr v-if="records.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">
                            No breakdown data available.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>