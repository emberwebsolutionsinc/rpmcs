<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";

import ReportDashboardCards from "@/components/reports/ReportDashboardCards.vue";

import reportService from "@/services/reportService";
import toast from "@/utils/toast";

const loading = ref(false);

const dashboard = reactive({
    today_collections: 0,
    monthly_collections: 0,
    total_collections: 0,
    voided_collections: 0,
    voided_amount: 0,
    outstanding_receivables: 0,
    active_sales: 0,
    fully_paid_sales: 0,
    active_reservations: 0,
    cancelled_reservations: 0,
    overdue_accounts: 0,
    overdue_installments: 0,
    total_overdue_amount: 0,
    critical_accounts: 0,
});

const commissionSummary = ref({
    total_commission_earned: 0,
    total_commission_paid: 0,
    total_commission_balance: 0,
    total_commission_deleted: 0,
    top_agent: null,
    recent_payments: [],
});

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

const shortDate = (value) => {
    if (!value) return "—";

    return new Date(value).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
};

const fetchDashboard = async () => {
    loading.value = true;

    try {
        const response = await reportService.getDashboard();

        Object.assign(dashboard, response.data.data ?? {});

        commissionSummary.value =
            response.data.commission_summary ?? {
                total_commission_earned: 0,
                total_commission_paid: 0,
                total_commission_balance: 0,
                total_commission_deleted: 0,
                top_agent: null,
                recent_payments: [],
            };
    } catch (error) {
        console.error(error);
        toast.error("Failed to load reports dashboard.");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDashboard();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Reports Dashboard"
                description="Executive overview of collections, sales, reservations, receivables, overdue accounts, and agent commissions."
            />

            <ReportDashboardCards :data="dashboard" />

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Commission Earned
                    </p>

                    <p class="mt-2 text-xl font-bold text-emerald-700">
                        {{ money(commissionSummary.total_commission_earned) }}
                    </p>
                </div>

                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Commission Paid
                    </p>

                    <p class="mt-2 text-xl font-bold text-blue-700">
                        {{ money(commissionSummary.total_commission_paid) }}
                    </p>
                </div>

                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Commission Balance
                    </p>

                    <p class="mt-2 text-xl font-bold text-red-700">
                        {{ money(commissionSummary.total_commission_balance) }}
                    </p>
                </div>

                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Deleted / Voided Commission
                    </p>

                    <p class="mt-2 text-xl font-bold text-orange-700">
                        {{ money(commissionSummary.total_commission_deleted) }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Report Shortcuts
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Access detailed operational reports.
                    </p>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <RouterLink
                            to="/reports/collections"
                            class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 hover:bg-emerald-100"
                        >
                            <p class="font-semibold text-emerald-900">
                                Collections Report
                            </p>

                            <p class="mt-1 text-sm text-emerald-700">
                                ORs, collections, voids, and payment methods.
                            </p>
                        </RouterLink>

                        <RouterLink
                            to="/reports/agent-commissions"
                            class="rounded-xl border border-blue-200 bg-blue-50 p-4 hover:bg-blue-100"
                        >
                            <p class="font-semibold text-blue-900">
                                Agent Commission Report
                            </p>

                            <p class="mt-1 text-sm text-blue-700">
                                Earned, paid, balance, and deleted commissions.
                            </p>
                        </RouterLink>

                        <RouterLink
                            to="/reports/commission-payments"
                            class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 hover:bg-indigo-100"
                        >
                            <p class="font-semibold text-indigo-900">
                                Commission Payment Report
                            </p>

                            <p class="mt-1 text-sm text-indigo-700">
                                Payment history, methods, and voided payments.
                            </p>
                        </RouterLink>

                        <RouterLink
                            to="/reports/agent-commission-ledger"
                            class="rounded-xl border border-purple-200 bg-purple-50 p-4 hover:bg-purple-100"
                        >
                            <p class="font-semibold text-purple-900">
                                Agent Commission Ledger
                            </p>

                            <p class="mt-1 text-sm text-purple-700">
                                Per-agent statement of earned and paid commissions.
                            </p>
                        </RouterLink>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Financial Health Summary
                    </h3>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-sm text-slate-500">
                                Outstanding Receivables
                            </span>

                            <span class="font-bold text-slate-900">
                                {{ money(dashboard.outstanding_receivables) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-sm text-slate-500">
                                Total Overdue Amount
                            </span>

                            <span class="font-bold text-red-700">
                                {{ money(dashboard.total_overdue_amount) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-sm text-slate-500">
                                Overdue Accounts
                            </span>

                            <span class="font-bold text-red-700">
                                {{ dashboard.overdue_accounts || 0 }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500">
                                Critical Accounts
                            </span>

                            <span class="font-bold text-red-800">
                                {{ dashboard.critical_accounts || 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Top Agent by Commission
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Agent with the highest computed commission earned.
                    </p>

                    <div class="mt-5">
                        <template v-if="commissionSummary.top_agent">
                            <p class="text-lg font-bold text-slate-900">
                                {{ commissionSummary.top_agent.agent_name }}
                            </p>

                            <p class="text-sm text-slate-500">
                                {{ commissionSummary.top_agent.agent_code || "—" }}
                            </p>

                            <p class="mt-2 font-semibold text-emerald-700">
                                {{ money(commissionSummary.top_agent.commission_earned) }}
                            </p>
                        </template>

                        <p
                            v-else
                            class="text-sm text-slate-500"
                        >
                            No commissionable agent data yet.
                        </p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b px-6 py-4">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Recent Commission Payments
                        </h3>

                        <p class="text-sm text-slate-500">
                            Latest recorded commission releases.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                        Date
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                        Agent
                                    </th>

                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                        Sale / Client
                                    </th>

                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                        Amount
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="payment in commissionSummary.recent_payments"
                                    :key="payment.id"
                                >
                                    <td class="px-4 py-4 text-sm">
                                        {{ shortDate(payment.payment_date) }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-slate-900">
                                            {{ payment.agent?.first_name }}
                                            {{ payment.agent?.last_name }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ payment.agent?.agent_code || "—" }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-slate-900">
                                            {{ payment.sale?.sale_no || "—" }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ payment.sale?.client?.first_name }}
                                            {{ payment.sale?.client?.last_name }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-4 text-right font-bold text-emerald-700">
                                        {{ money(payment.amount) }}
                                    </td>
                                </tr>

                                <tr v-if="commissionSummary.recent_payments.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-8 text-center text-sm text-slate-500"
                                    >
                                        No recent commission payments.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <LoadingOverlay
            :show="loading"
            title="Loading Reports"
            description="Please wait while RPMCS loads the reports dashboard."
        />
    </AppLayout>
</template>