<script setup>
import {
    AlertTriangle,
    CalendarClock,
    CalendarDays,
    Wallet,
} from "lucide-vue-next";

defineProps({
    overdueAccounts: {
        type: [Number, String],
        default: 0,
    },

    overdueInstallments: {
        type: [Number, String],
        default: 0,
    },

    totalOverdueAmount: {
        type: [Number, String],
        default: 0,
    },

    dueThisWeek: {
        type: [Number, String],
        default: 0,
    },

    dueThisMonth: {
        type: [Number, String],
        default: 0,
    },
    criticalAccounts: {
        type: [Number, String],
        default: 0,
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
    <div
        class="grid gap-4"
        style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));"
    >
        <div class="rounded-2xl border border-red-100 bg-red-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-700">
                        Overdue Accounts
                    </p>

                    <h2 class="mt-2 text-2xl font-bold text-red-950">
                        {{ overdueAccounts }}
                    </h2>

                    <p class="mt-1 text-xs text-red-700">
                        Unique sales with overdue payments
                    </p>
                </div>

                <div class="rounded-xl bg-red-100 p-3 text-red-700">
                    <AlertTriangle class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-orange-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-700">
                        Overdue Installments
                    </p>

                    <h2 class="mt-2 text-2xl font-bold text-orange-950">
                        {{ overdueInstallments }}
                    </h2>

                    <p class="mt-1 text-xs text-orange-700">
                        Total unpaid overdue schedules
                    </p>
                </div>

                <div class="rounded-xl bg-orange-100 p-3 text-orange-700">
                    <CalendarClock class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                        Overdue Amount
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-amber-950">
                        {{ formatMoney(totalOverdueAmount) }}
                    </h2>

                    <p class="mt-1 text-xs text-amber-700">
                        Total overdue balance
                    </p>
                </div>

                <div class="rounded-xl bg-amber-100 p-3 text-amber-700">
                    <Wallet class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-red-200 bg-red-100 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-800">
                        Critical Accounts
                    </p>

                    <h2 class="mt-2 text-2xl font-bold text-red-950">
                        {{ criticalAccounts }}
                    </h2>

                    <p class="mt-1 text-xs text-red-800">
                        More than 90 days overdue
                    </p>
                </div>

                <div class="rounded-xl bg-red-200 p-3 text-red-800">
                    <AlertTriangle class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                        Due This Week
                    </p>

                    <h2 class="mt-2 text-2xl font-bold text-blue-950">
                        {{ dueThisWeek }}
                    </h2>

                    <p class="mt-1 text-xs text-blue-700">
                        Current week schedules
                    </p>
                </div>

                <div class="rounded-xl bg-blue-100 p-3 text-blue-700">
                    <CalendarDays class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-purple-100 bg-purple-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-purple-700">
                        Due This Month
                    </p>

                    <h2 class="mt-2 text-2xl font-bold text-purple-950">
                        {{ dueThisMonth }}
                    </h2>

                    <p class="mt-1 text-xs text-purple-700">
                        Current month schedules
                    </p>
                </div>

                <div class="rounded-xl bg-purple-100 p-3 text-purple-700">
                    <CalendarDays class="h-6 w-6" />
                </div>
            </div>
        </div>
    </div>
</template>