<script setup>
import ModuleStatCard from "@/components/common/ModuleStatCard.vue";

import {
    BadgeDollarSign,
    CheckCircle2,
    XCircle,
    Wallet,
    Banknote,
    Coins,
} from "lucide-vue-next";

defineProps({
    totalSales: {
        type: Number,
        default: 0,
    },

    activeSales: {
        type: Number,
        default: 0,
    },

    cancelledSales: {
        type: Number,
        default: 0,
    },

    fullyPaidSales: {
        type: Number,
        default: 0,
    },

    totalContractPrice: {
        type: [Number, String],
        default: 0,
    },

    totalBalance: {
        type: [Number, String],
        default: 0,
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        maximumFractionDigits: 2,
    }).format(Number(amount || 0));
};
</script>

<template>
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        <ModuleStatCard
            title="Total Sales"
            :value="totalSales"
            :icon="BadgeDollarSign"
            helper="All sale records"
        />

        <ModuleStatCard
            title="Active Sales"
            :value="activeSales"
            :icon="CheckCircle2"
            helper="Currently active"
        />

        <ModuleStatCard
            title="Cancelled"
            :value="cancelledSales"
            :icon="XCircle"
            helper="Cancelled sales"
        />

        <ModuleStatCard
            title="Fully Paid"
            :value="fullyPaidSales"
            :icon="Wallet"
            helper="Completed accounts"
        />

        <ModuleStatCard
            title="Contract Value"
            :value="formatCurrency(totalContractPrice)"
            :icon="Banknote"
            helper="Total contract price"
        />

        <ModuleStatCard
            title="Outstanding"
            :value="formatCurrency(totalBalance)"
            :icon="Coins"
            helper="Remaining balance"
        />
    </div>
</template>