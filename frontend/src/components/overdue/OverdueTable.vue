<script setup>
import StatusBadge from "@/components/common/StatusBadge.vue";

import {
    AlertTriangle,
    User,
    Building2,
    CalendarDays,
} from "lucide-vue-next";

defineProps({
    accounts: {
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
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Client
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Property
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Sale
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Installment
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                            Due Date
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Days Overdue
                        </th>
                         <th>
                            Aging
                        </th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Priority
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Balance
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">
                            Loading overdue accounts...
                        </td>
                    </tr>

                    <tr
                        v-for="account in accounts"
                        v-else
                        :key="account.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <User class="h-4 w-4 text-slate-400" />

                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ fullName(account.sale?.client) }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ account.sale?.client?.client_code || "—" }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <Building2 class="h-4 w-4 text-slate-400" />

                                <div>
                                    <p class="font-medium text-slate-900">
                                        {{ account.sale?.lot?.project?.project_name || "—" }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        Lot {{ account.sale?.lot?.lot_no || "—" }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-700">
                            {{ account.sale?.sale_no || "—" }}
                        </td>

                        <td class="px-5 py-4 text-center text-sm font-semibold text-slate-900">
                            #{{ account.installment_no }}
                        </td>

                        <td class="px-5 py-4 text-sm text-slate-700">
                            <span class="inline-flex items-center gap-1">
                                <CalendarDays class="h-4 w-4 text-slate-400" />
                                {{ formatDate(account.due_date) }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                <AlertTriangle class="h-3.5 w-3.5" />
                                {{ account.days_overdue }} days
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-semibold"
                                :class="{
                                    'bg-yellow-100 text-yellow-700':
                                        account.aging_bucket === '1-30 Days',

                                    'bg-orange-100 text-orange-700':
                                        account.aging_bucket === '31-60 Days',

                                    'bg-red-100 text-red-700':
                                        account.aging_bucket === '61-90 Days',

                                    'bg-red-200 text-red-900':
                                        account.aging_bucket === '90+ Days',
                                }"
                            >
                                {{ account.aging_bucket }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                            :class="{
                                'bg-slate-100 text-slate-700':
                                    account.collection_priority === 'low',

                                'bg-yellow-100 text-yellow-700':
                                    account.collection_priority === 'medium',

                                'bg-orange-100 text-orange-700':
                                    account.collection_priority === 'high',

                                'bg-red-200 text-red-900':
                                    account.collection_priority === 'critical',
                            }"
                        >
                            {{ account.collection_priority || "low" }}
                        </span>
                    </td>

                        <td class="px-5 py-4 text-right text-sm font-bold text-red-700">
                            {{ formatMoney(account.balance) }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            <StatusBadge :value="account.status" />
                        </td>
                    </tr>

                    <tr v-if="!loading && accounts.length === 0">
                        <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">
                            No overdue accounts found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <TopDelinquentsTable
                :records="topDelinquents"
            />
        </div>
    </div>
</template>