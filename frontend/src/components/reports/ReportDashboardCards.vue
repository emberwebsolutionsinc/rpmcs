<script setup>
import {
    Wallet,
    CalendarDays,
    Receipt,
    RotateCcw,
    AlertTriangle,
    BadgeDollarSign,
    FileSignature,
    CheckCircle,
} from "lucide-vue-next";

defineProps({
    data: {
        type: Object,
        default: () => ({}),
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
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <Wallet class="h-6 w-6 text-emerald-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-emerald-700">
                Today's Collections
            </p>
            <h2 class="mt-1 text-xl font-bold text-emerald-950">
                {{ formatMoney(data.today_collections) }}
            </h2>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <CalendarDays class="h-6 w-6 text-blue-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-blue-700">
                Monthly Collections
            </p>
            <h2 class="mt-1 text-xl font-bold text-blue-950">
                {{ formatMoney(data.monthly_collections) }}
            </h2>
        </div>

        <div class="rounded-2xl border border-purple-100 bg-purple-50 p-5 shadow-sm">
            <Receipt class="h-6 w-6 text-purple-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-purple-700">
                Total Collections
            </p>
            <h2 class="mt-1 text-xl font-bold text-purple-950">
                {{ formatMoney(data.total_collections) }}
            </h2>
        </div>

        <div class="rounded-2xl border border-red-100 bg-red-50 p-5 shadow-sm">
            <AlertTriangle class="h-6 w-6 text-red-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-red-700">
                Overdue Amount
            </p>
            <h2 class="mt-1 text-xl font-bold text-red-950">
                {{ formatMoney(data.total_overdue_amount) }}
            </h2>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-orange-50 p-5 shadow-sm">
            <RotateCcw class="h-6 w-6 text-orange-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-orange-700">
                Voided Collections
            </p>
            <h2 class="mt-1 text-2xl font-bold text-orange-950">
                {{ data.voided_collections || 0 }}
            </h2>
            <p class="mt-1 text-xs text-orange-700">
                {{ formatMoney(data.voided_amount) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <BadgeDollarSign class="h-6 w-6 text-slate-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-slate-500">
                Active Sales
            </p>
            <h2 class="mt-1 text-2xl font-bold text-slate-950">
                {{ data.active_sales || 0 }}
            </h2>
        </div>

        <div class="rounded-2xl border border-green-100 bg-green-50 p-5 shadow-sm">
            <CheckCircle class="h-6 w-6 text-green-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-green-700">
                Fully Paid Sales
            </p>
            <h2 class="mt-1 text-2xl font-bold text-green-950">
                {{ data.fully_paid_sales || 0 }}
            </h2>
        </div>

        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5 shadow-sm">
            <FileSignature class="h-6 w-6 text-indigo-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-indigo-700">
                Active Reservations
            </p>
            <h2 class="mt-1 text-2xl font-bold text-indigo-950">
                {{ data.active_reservations || 0 }}
            </h2>
        </div>

        <div class="rounded-2xl border border-red-200 bg-red-100 p-5 shadow-sm">
            <AlertTriangle class="h-6 w-6 text-red-800" />
            <p class="mt-3 text-xs font-semibold uppercase text-red-800">
                Critical Accounts
            </p>
            <h2 class="mt-1 text-2xl font-bold text-red-950">
                {{ data.critical_accounts || 0 }}
            </h2>
            <p class="mt-1 text-xs text-red-800">
                90+ days overdue
            </p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <AlertTriangle class="h-6 w-6 text-amber-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-amber-700">
                Overdue Accounts
            </p>
            <h2 class="mt-1 text-2xl font-bold text-amber-950">
                {{ data.overdue_accounts || 0 }}
            </h2>
            <p class="mt-1 text-xs text-amber-700">
                {{ data.overdue_installments || 0 }} installments
            </p>
        </div>
    </div>
</template>