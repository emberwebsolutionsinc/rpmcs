<script setup>
import {
    Receipt,
    RotateCcw,
    Wallet,
    BadgeCheck,
} from "lucide-vue-next";

defineProps({
    postedCount: {
        type: [Number, String],
        default: 0,
    },
    voidedCount: {
        type: [Number, String],
        default: 0,
    },
    grossCollections: {
        type: [Number, String],
        default: 0,
    },
    voidedAmount: {
        type: [Number, String],
        default: 0,
    },
    netCollections: {
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
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
            <Receipt class="h-6 w-6 text-emerald-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-emerald-700">
                Posted ORs
            </p>
            <p class="mt-1 text-2xl font-bold text-emerald-950">
                {{ postedCount }}
            </p>
        </div>

        <div class="rounded-2xl border border-red-100 bg-red-50 p-5">
            <RotateCcw class="h-6 w-6 text-red-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-red-700">
                Voided ORs
            </p>
            <p class="mt-1 text-2xl font-bold text-red-950">
                {{ voidedCount }}
            </p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
            <Wallet class="h-6 w-6 text-blue-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-blue-700">
                Gross Collections
            </p>
            <p class="mt-1 text-xl font-bold text-blue-950">
                {{ formatMoney(grossCollections) }}
            </p>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-orange-50 p-5">
            <RotateCcw class="h-6 w-6 text-orange-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-orange-700">
                Voided Amount
            </p>
            <p class="mt-1 text-xl font-bold text-orange-950">
                {{ formatMoney(voidedAmount) }}
            </p>
        </div>

        <div class="rounded-2xl border border-purple-100 bg-purple-50 p-5">
            <BadgeCheck class="h-6 w-6 text-purple-700" />
            <p class="mt-3 text-xs font-semibold uppercase text-purple-700">
                Net Collections
            </p>
            <p class="mt-1 text-xl font-bold text-purple-950">
                {{ formatMoney(netCollections) }}
            </p>
        </div>
    </div>
</template>