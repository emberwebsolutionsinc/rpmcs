<script setup>
import StatusBadge from "@/components/common/StatusBadge.vue";

defineProps({
    schedules: {
        type: Array,
        default: () => [],
    },

    loading: {
        type: Boolean,
        default: false,
    },
});

const fullName = (person) => {
    if (!person) return "—";

    return (
        person.full_name ||
        [person.first_name, person.middle_name, person.last_name]
            .filter(Boolean)
            .join(" ") ||
        "—"
    );
};

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return "—";

    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
};

const riskClass = (risk) => {
    return {
        current: "bg-emerald-100 text-emerald-700",
        low: "bg-yellow-100 text-yellow-700",
        medium: "bg-orange-100 text-orange-700",
        high: "bg-red-100 text-red-700",
        critical: "bg-red-200 text-red-900",
    }[risk] || "bg-slate-100 text-slate-700";
};
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="font-semibold text-slate-900">
                Aging Details
            </h3>

            <p class="text-sm text-slate-500">
                Detailed unpaid installment balances by due date and aging bucket.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Client
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale / Property
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Installment
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Due Date
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Days Late
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount Due
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Paid
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Balance
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Aging
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Risk
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="11" class="px-5 py-10 text-center text-sm text-slate-500">
                            Loading aging report...
                        </td>
                    </tr>

                    <tr
                        v-for="schedule in schedules"
                        v-else
                        :key="schedule.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-900">
                                {{ fullName(schedule.sale?.client) }}
                            </p>

                            <p class="text-xs text-slate-500">
                                {{ schedule.sale?.client?.client_code || "—" }}
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-900">
                                {{ schedule.sale?.sale_no || "—" }}
                            </p>

                            <p class="text-xs text-slate-500">
                                {{ schedule.sale?.lot?.project?.project_name || "—" }}
                            </p>

                            <p class="text-xs text-slate-500">
                                {{ schedule.sale?.lot?.lot_no || "—" }}
                                <span v-if="schedule.sale?.lot?.lot_code">
                                    — {{ schedule.sale.lot.lot_code }}
                                </span>
                            </p>
                        </td>

                        <td class="px-5 py-4 text-center">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                #{{ schedule.installment_no }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-700">
                            {{ formatDate(schedule.due_date) }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="riskClass(schedule.risk_level)"
                            >
                                {{ schedule.days_late || 0 }} days
                            </span>
                        </td>

                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-900">
                            {{ formatMoney(schedule.amount_due) }}
                        </td>

                        <td class="px-5 py-4 text-right text-sm font-semibold text-emerald-700">
                            {{ formatMoney(schedule.amount_paid) }}
                        </td>

                        <td class="px-5 py-4 text-right text-sm font-bold text-red-700">
                            {{ formatMoney(schedule.balance) }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="riskClass(schedule.risk_level)"
                            >
                                {{ schedule.aging_bucket || "Current" }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-center">
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                :class="riskClass(schedule.risk_level)"
                            >
                                {{ schedule.risk_level || "current" }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-center">
                            <StatusBadge :value="schedule.status" />
                        </td>
                    </tr>

                    <tr v-if="!loading && schedules.length === 0">
                        <td colspan="11" class="px-5 py-10 text-center text-sm text-slate-500">
                            No aging records found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>