<script setup>
import { computed } from "vue";

const props = defineProps({
    value: {
        type: String,
        default: "",
    },
});

const label = computed(() => {
    const labels = {
        paid: "Paid",
        voided: "Void",
        partial: "Partial",
        active: "Active",
        cancelled: "Cancelled",
        expired: "Expired",
        reserved: "Reserved",
        available: "Available",
        sold: "Sold",
        converted_to_sale: "Converted",
        fully_paid: "Fully Paid",
        posted: "Posted",
    };

    return labels[props.value] ?? props.value;
});

const badgeClass = computed(() => {
    const classes = {
        voided: "bg-red-100 text-red-800",
        posted: "bg-green-100 text-green-800 border border-green-200",
        paid: "bg-green-100 text-green-800 border border-green-200",
        partial: "bg-yellow-100 text-yellow-800 border border-yellow-200",
        active: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
        expired: "bg-yellow-100 text-yellow-800",
        reserved: "bg-blue-100 text-blue-800",
        available: "bg-emerald-100 text-emerald-800",
        sold: "bg-purple-100 text-purple-800",
        converted_to_sale: "bg-indigo-100 text-indigo-800",
        fully_paid: "bg-green-100 text-green-800",
    };

    return classes[props.value] ??
        "bg-slate-100 text-slate-700";
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="badgeClass"
    >
        {{ label }}
    </span>
</template>