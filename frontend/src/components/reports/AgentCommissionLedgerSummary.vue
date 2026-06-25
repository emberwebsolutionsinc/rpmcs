<script setup>
defineProps({
    summary: {
        type: Object,
        default: () => ({}),
    },
});

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });
</script>

<template>
    <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Agent</p>
            <p class="font-bold">
                {{ summary.agent_name || "—" }}
            </p>
            <p class="text-xs text-slate-500">
                {{ summary.agent_code || "—" }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Sales</p>
            <p class="text-2xl font-bold">
                {{ summary.total_sales || 0 }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Contract Price</p>
            <p class="font-bold">
                {{ money(summary.total_contract_price) }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Commission Earned</p>
            <p class="font-bold text-emerald-700">
                {{ money(summary.total_commission_earned) }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Commission Paid</p>
            <p class="font-bold text-blue-700">
                {{ money(summary.total_commission_paid) }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Commission Balance</p>
            <p class="font-bold text-red-700">
                {{ money(summary.total_commission_balance) }}
            </p>
        </div>
    </div>
</template>