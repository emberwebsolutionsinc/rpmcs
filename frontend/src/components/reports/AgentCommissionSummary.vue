<script setup>
defineProps({
    summary: {
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
    <div class="grid gap-4 md:grid-cols-4 xl:grid-cols-7">
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Agents</p>
            <p class="text-2xl font-bold">{{ summary.total_agents }}</p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Sales</p>
            <p class="text-2xl font-bold">{{ summary.total_sales }}</p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Contract Price</p>
            <p class="font-bold">{{ money(summary.total_contract_price) }}</p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Downpayment</p>
            <p class="font-bold">{{ money(summary.total_downpayment) }}</p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Balance</p>
            <p class="font-bold">{{ money(summary.total_balance) }}</p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Gross Commission</p>
            <p class="font-bold text-green-700">
                {{ money(summary.gross_commission) }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Unpaid Commission</p>
            <p class="font-bold text-red-700">
                {{ money(summary.unpaid_commission) }}
            </p>
        </div>
    </div>
</template>