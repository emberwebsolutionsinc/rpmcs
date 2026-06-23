<script setup>
import {
    Users,
    CalendarClock,
    Wallet,
    CheckCircle,
    AlertTriangle,
    Siren,
} from "lucide-vue-next";

defineProps({
    summary: {
        type: Object,
        default: () => ({}),
    },
});

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
        <!-- Total Accounts -->
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                        Accounts
                    </p>

                    <h2 class="mt-2 text-2xl font-bold text-blue-950">
                        {{ summary.total_accounts || 0 }}
                    </h2>
                </div>

                <Users class="h-8 w-8 text-blue-600" />
            </div>
        </div>

        <!-- Installments -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Installments
                    </p>

                    <h2 class="mt-2 text-2xl font-bold text-slate-900">
                        {{ summary.total_installments || 0 }}
                    </h2>
                </div>

                <CalendarClock class="h-8 w-8 text-slate-600" />
            </div>
        </div>

        <!-- Total Balance -->
        <div class="rounded-2xl border border-purple-100 bg-purple-50 p-5 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-purple-700">
                        Total Outstanding Balance
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-purple-950">
                        {{ formatMoney(summary.total_balance) }}
                    </h2>
                </div>

                <Wallet class="h-8 w-8 text-purple-600" />
            </div>
        </div>

        <!-- Current -->
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                        Current
                    </p>

                    <h2 class="mt-2 text-lg font-bold text-emerald-950">
                        {{ formatMoney(summary.current_amount) }}
                    </h2>
                </div>

                <CheckCircle class="h-8 w-8 text-emerald-600" />
            </div>
        </div>

        <!-- Overdue -->
        <div class="rounded-2xl border border-red-100 bg-red-50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-700">
                        Overdue
                    </p>

                    <h2 class="mt-2 text-lg font-bold text-red-950">
                        {{ formatMoney(summary.overdue_amount) }}
                    </h2>
                </div>

                <AlertTriangle class="h-8 w-8 text-red-600" />
            </div>
        </div>

        <!-- Critical -->
        <div class="rounded-2xl border border-red-300 bg-red-100 p-5 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-800">
                        Critical Exposure (181+ Days)
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-red-950">
                        {{ formatMoney(summary.critical_amount) }}
                    </h2>
                </div>

                <Siren class="h-8 w-8 text-red-800" />
            </div>
        </div>
    </div>
</template>