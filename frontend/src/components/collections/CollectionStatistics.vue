<script setup>
import {
    Wallet,
    CalendarDays,
    Banknote,
    AlertCircle,
} from "lucide-vue-next";

defineProps({
    totalCollected: {
        type: [Number, String],
        default: 0,
    },

    todayCollected: {
        type: [Number, String],
        default: 0,
    },

    monthlyCollected: {
        type: [Number, String],
        default: 0,
    },

    outstandingBalance: {
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
        style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));"
    >
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                        Total Collected
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-emerald-950">
                        {{ formatMoney(totalCollected) }}
                    </h2>

                    <p class="mt-1 text-xs text-emerald-700">
                        All posted collections
                    </p>
                </div>

                <div class="rounded-xl bg-emerald-100 p-3 text-emerald-700">
                    <Wallet class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">
                        Today
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-blue-950">
                        {{ formatMoney(todayCollected) }}
                    </h2>

                    <p class="mt-1 text-xs text-blue-700">
                        Collection for today
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
                        This Month
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-purple-950">
                        {{ formatMoney(monthlyCollected) }}
                    </h2>

                    <p class="mt-1 text-xs text-purple-700">
                        Current month collection
                    </p>
                </div>

                <div class="rounded-xl bg-purple-100 p-3 text-purple-700">
                    <Banknote class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                        Outstanding
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-amber-950">
                        {{ formatMoney(outstandingBalance) }}
                    </h2>

                    <p class="mt-1 text-xs text-amber-700">
                        Remaining sales balance
                    </p>
                </div>

                <div class="rounded-xl bg-amber-100 p-3 text-amber-700">
                    <AlertCircle class="h-6 w-6" />
                </div>
            </div>
        </div>
    </div>
</template>