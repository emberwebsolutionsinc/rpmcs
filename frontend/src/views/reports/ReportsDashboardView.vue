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

const fetchDashboard = async () => {
    loading.value = true;

    try {
        const response = await reportService.getDashboard();

        Object.assign(dashboard, response.data.data ?? {});
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
                description="Executive overview of collections, sales, reservations, receivables, and overdue accounts."
            />

            <ReportDashboardCards
                :data="dashboard"
            />

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

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 opacity-70">
                            <p class="font-semibold text-slate-700">
                                Sales Report
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Coming soon.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 opacity-70">
                            <p class="font-semibold text-slate-700">
                                Aging Report
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Coming soon.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 opacity-70">
                            <p class="font-semibold text-slate-700">
                                Delinquency Report
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Coming soon.
                            </p>
                        </div>
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
                                ₱{{ Number(dashboard.outstanding_receivables || 0).toLocaleString("en-PH") }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-sm text-slate-500">
                                Total Overdue Amount
                            </span>
                            <span class="font-bold text-red-700">
                                ₱{{ Number(dashboard.total_overdue_amount || 0).toLocaleString("en-PH") }}
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
        </div>

        <LoadingOverlay
            :show="loading"
            title="Loading Reports"
            description="Please wait while RPMCS loads the reports dashboard."
        />
    </AppLayout>
</template>