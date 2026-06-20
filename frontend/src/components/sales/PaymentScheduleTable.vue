<script setup>
import StatusBadge from "@/components/common/StatusBadge.vue";

const props = defineProps({
    schedules: {
        type: Array,
        default: () => [],
    },

    loading: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["record-payment"]);

const canPay = (schedule, index) => {
    if (
        schedule.status === "paid" ||
        schedule.status === "cancelled"
    ) {
        return false;
    }

    if (index === 0) {
        return true;
    }

    const previousSchedule = props.schedules[index - 1];

    return previousSchedule?.status === "paid";
};

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
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
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div v-if="loading" class="p-6 text-center text-sm text-slate-500">
            Loading payment schedule...
        </div>

        <div v-else-if="schedules.length === 0" class="p-6 text-center text-sm text-slate-500">
            No payment schedule generated yet.
        </div>

        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            #
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Due Date
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Amount Due
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Balance
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="(schedule, index) in schedules"
                        :key="schedule.id"
                    >
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                            {{ schedule.installment_no }}
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ formatDate(schedule.due_date) }}
                        </td>

                        <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">
                            {{ formatMoney(schedule.amount_due) }}
                        </td>

                        <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">
                            {{ formatMoney(schedule.balance) }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <StatusBadge :value="schedule.status" />
                        </td>

                        <td class="px-4 py-3 text-right">
                           <template v-if="schedule.status === 'paid'">
                            <span class="text-green-600 font-semibold">
                                ✓ Paid
                            </span>
                        </template>

                        <template v-else-if="canPay(schedule, index)">
                            <button
                                @click="$emit('record-payment', schedule)"
                                class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                            >
                                Pay
                            </button>
                        </template>

                        <template v-else>
                            <span
                                class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-medium text-slate-500"
                            >
                                🔒 Waiting
                            </span>
                        </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>