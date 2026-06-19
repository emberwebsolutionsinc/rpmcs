<script setup>
import { computed } from "vue";

const props = defineProps({
    value: {
        type: String,
        required: true,
    },
});

const label = computed(() => {
    return props.value
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) => char.toUpperCase());
});

const badgeClass = computed(() => {
    const classes = {
        active: "bg-emerald-100 text-emerald-700",
        inactive: "bg-slate-100 text-slate-600",

        available: "bg-emerald-100 text-emerald-700",
        reserved: "bg-blue-100 text-blue-700",
        sold: "bg-purple-100 text-purple-700",
        fully_paid: "bg-green-100 text-green-700",
        cancelled: "bg-red-100 text-red-700",

        pending: "bg-yellow-100 text-yellow-700",
        partial: "bg-orange-100 text-orange-700",
        paid: "bg-emerald-100 text-emerald-700",
        defaulted: "bg-red-100 text-red-700",

        main_agent: "bg-indigo-100 text-indigo-700",
        sub_agent: "bg-cyan-100 text-cyan-700",
        independent_agent: "bg-slate-100 text-slate-700",
    };

    return classes[props.value] || "bg-slate-100 text-slate-600";
});
</script>

<template>
    <span
        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
        :class="badgeClass"
    >
        {{ label }}
    </span>
</template>