<script setup>
const props = defineProps({
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

const cards = [
    {
        title: "Total Sales",
        value: () => props.summary.total_sales || 0,
        color: "text-slate-900",
        bg: "bg-slate-50",
        icon: "📑",
        description: "Reservations converted into sales",
    },

    {
        title: "Gross Sales",
        value: () => money(props.summary.total_contract_price),
        color: "text-blue-700",
        bg: "bg-blue-50",
        icon: "🏠",
        description: "Total contract price",
    },

    {
        title: "Commission Earned",
        value: () => money(props.summary.total_commission_earned),
        color: "text-emerald-700",
        bg: "bg-emerald-50",
        icon: "💰",
        description: "Lifetime earnings",
    },

    {
        title: "Commission Paid",
        value: () => money(props.summary.total_commission_paid),
        color: "text-indigo-700",
        bg: "bg-indigo-50",
        icon: "💵",
        description: "Already released",
    },

    {
        title: "Outstanding",
        value: () => money(props.summary.total_commission_balance),
        color: "text-red-700",
        bg: "bg-red-50",
        icon: "⚠️",
        description: "Remaining payable",
    },

    {
        title: "Clients",
        value: () => props.summary.total_clients || 0,
        color: "text-amber-700",
        bg: "bg-amber-50",
        icon: "👥",
        description: "Handled buyers",
    },

    {
        title: "Sub Agents",
        value: () => props.summary.sub_agents_count || 0,
        color: "text-cyan-700",
        bg: "bg-cyan-50",
        icon: "🤝",
        description: "Assigned sub-agents",
    },

    {
        title: "Projects",
        value: () => props.summary.total_projects || 0,
        color: "text-purple-700",
        bg: "bg-purple-50",
        icon: "🏘️",
        description: "Projects sold",
    },
];
</script>

<template>
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div
            v-for="card in cards"
            :key="card.title"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
        >
            <div class="flex items-center justify-between">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl text-2xl"
                    :class="card.bg"
                >
                    {{ card.icon }}
                </div>

                <p
                    class="text-2xl font-bold"
                    :class="card.color"
                >
                    {{ card.value() }}
                </p>
            </div>

            <div class="mt-5">
                <p class="font-semibold text-slate-900">
                    {{ card.title }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ card.description }}
                </p>
            </div>
        </div>
    </div>
</template>