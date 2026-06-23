<script setup>
import {
    BadgeDollarSign,
    CheckCircle,
    CircleDollarSign,
    ReceiptText,
    Wallet,
    XCircle,
} from "lucide-vue-next";

defineProps({
    totalSales: {
        type: [Number, String],
        default: 0,
    },
    activeSales: {
        type: [Number, String],
        default: 0,
    },
    fullyPaidSales: {
        type: [Number, String],
        default: 0,
    },
    cancelledSales: {
        type: [Number, String],
        default: 0,
    },
    totalContractPrice: {
        type: [Number, String],
        default: 0,
    },
    totalDownPayment: {
        type: [Number, String],
        default: 0,
    },
    totalBalance: {
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
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <ReceiptText class="h-6 w-6 text-blue-700" />

            <p class="mt-3 text-xs font-semibold uppercase text-blue-700">
                Total Sales
            </p>

            <h2 class="mt-1 text-2xl font-bold text-blue-950">
                {{ totalSales }}
            </h2>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <CheckCircle class="h-6 w-6 text-emerald-700" />

            <p class="mt-3 text-xs font-semibold uppercase text-emerald-700">
                Active Sales
            </p>

            <h2 class="mt-1 text-2xl font-bold text-emerald-950">
                {{ activeSales }}
            </h2>
        </div>

        <div class="rounded-2xl border border-green-100 bg-green-50 p-5 shadow-sm">
            <CircleDollarSign class="h-6 w-6 text-green-700" />

            <p class="mt-3 text-xs font-semibold uppercase text-green-700">
                Fully Paid
            </p>

            <h2 class="mt-1 text-2xl font-bold text-green-950">
                {{ fullyPaidSales }}
            </h2>
        </div>

        <div class="rounded-2xl border border-red-100 bg-red-50 p-5 shadow-sm">
            <XCircle class="h-6 w-6 text-red-700" />

            <p class="mt-3 text-xs font-semibold uppercase text-red-700">
                Cancelled Sales
            </p>

            <h2 class="mt-1 text-2xl font-bold text-red-950">
                {{ cancelledSales }}
            </h2>
        </div>

        <div class="rounded-2xl border border-purple-100 bg-purple-50 p-5 shadow-sm xl:col-span-2">
            <BadgeDollarSign class="h-6 w-6 text-purple-700" />

            <p class="mt-3 text-xs font-semibold uppercase text-purple-700">
                Total Contract Price
            </p>

            <h2 class="mt-1 text-2xl font-bold text-purple-950">
                {{ formatMoney(totalContractPrice) }}
            </h2>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-orange-50 p-5 shadow-sm">
            <Wallet class="h-6 w-6 text-orange-700" />

            <p class="mt-3 text-xs font-semibold uppercase text-orange-700">
                Total Downpayment
            </p>

            <h2 class="mt-1 text-xl font-bold text-orange-950">
                {{ formatMoney(totalDownPayment) }}
            </h2>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <Wallet class="h-6 w-6 text-slate-700" />

            <p class="mt-3 text-xs font-semibold uppercase text-slate-500">
                Total Balance
            </p>

            <h2 class="mt-1 text-xl font-bold text-slate-950">
                {{ formatMoney(totalBalance) }}
            </h2>
        </div>
    </div>
</template>