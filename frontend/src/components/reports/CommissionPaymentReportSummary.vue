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
    <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-5">
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Active Payments</p>
            <p class="text-2xl font-bold">
                {{ summary.total_active_payments || 0 }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Active Amount</p>
            <p class="font-bold text-emerald-700">
                {{ money(summary.total_active_amount) }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Deleted Payments</p>
            <p class="text-2xl font-bold text-red-700">
                {{ summary.total_deleted_payments || 0 }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Deleted Amount</p>
            <p class="font-bold text-red-700">
                {{ money(summary.total_deleted_amount) }}
            </p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Net Paid</p>
            <p class="font-bold text-blue-700">
                {{ money(summary.net_payment_amount) }}
            </p>
        </div>
    </div>
</template>