<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";

import AgingReportSummary from "@/components/reports/AgingReportSummary.vue";
import AgingBucketSummary from "@/components/reports/AgingBucketSummary.vue";
import AgingReportTable from "@/components/reports/AgingReportTable.vue";

import reportService from "@/services/reportService";
import toast from "@/utils/toast";

import { Search } from "lucide-vue-next";

const schedules = ref([]);
const loading = ref(false);

const filters = reactive({
    from_date: "",
    to_date: "",
    status: "",
    bucket: "",
    search: "",
    per_page: 10,
    page: 1,
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
});

const summary = reactive({
    total_accounts: 0,
    total_installments: 0,
    total_balance: 0,
    current_amount: 0,
    overdue_amount: 0,
    critical_amount: 0,
});

const buckets = reactive({
    current: {
        label: "Current",
        count: 0,
        amount: 0,
    },
    "1_30": {
        label: "1-30 Days",
        count: 0,
        amount: 0,
    },
    "31_60": {
        label: "31-60 Days",
        count: 0,
        amount: 0,
    },
    "61_90": {
        label: "61-90 Days",
        count: 0,
        amount: 0,
    },
    "91_180": {
        label: "91-180 Days",
        count: 0,
        amount: 0,
    },
    "181_plus": {
        label: "181+ Days",
        count: 0,
        amount: 0,
    },
});

const exportExcel = async () => {
    loading.value = true;

    try {
        const response = await reportService.exportAgingReportExcel(filters);

        const blob = new Blob([response.data], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `aging-report-${new Date()
            .toISOString()
            .slice(0, 10)}.xlsx`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export aging report Excel.");
    } finally {
        loading.value = false;
    }
};

const exportPdf = async () => {
    loading.value = true;

    try {
        const response = await reportService.exportAgingReportPdf(filters);

        const blob = new Blob([response.data], {
            type: "application/pdf",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `aging-report-${new Date()
            .toISOString()
            .slice(0, 10)}.pdf`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export aging report PDF.");
    } finally {
        loading.value = false;
    }
};

const fetchReport = async () => {
    loading.value = true;

    try {
        const response = await reportService.getAgingReport(filters);

        Object.assign(summary, response.data.summary ?? {});
        Object.assign(buckets, response.data.buckets ?? {});

        const paginated = response.data.schedules ?? {};

        schedules.value = paginated.data ?? [];

        pagination.current_page = paginated.current_page ?? 1;
        pagination.last_page = paginated.last_page ?? 1;
        pagination.total = paginated.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load aging report.");
    } finally {
        loading.value = false;
    }
};

const applyFilters = async () => {
    filters.page = 1;
    await fetchReport();
};

const resetFilters = async () => {
    filters.from_date = "";
    filters.to_date = "";
    filters.status = "";
    filters.bucket = "";
    filters.search = "";
    filters.page = 1;

    await fetchReport();
};

const selectBucket = async (bucket) => {
    filters.bucket = filters.bucket === bucket ? "" : bucket;
    filters.page = 1;

    await fetchReport();
};

const previousPage = async () => {
    if (pagination.current_page > 1) {
        filters.page = pagination.current_page - 1;
        await fetchReport();
    }
};

const nextPage = async () => {
    if (pagination.current_page < pagination.last_page) {
        filters.page = pagination.current_page + 1;
        await fetchReport();
    }
};

const goToPage = async (page) => {
    if (page === pagination.current_page) return;

    filters.page = page;
    await fetchReport();
};

const printReport = () => {
    window.print();
};

onMounted(() => {
    fetchReport();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Aging Report"
                description="Monitor receivables by age, risk level, due date, installment balance, and overdue bucket."
            />

            <AgingReportSummary
                :summary="summary"
            />

            <AgingBucketSummary
                :buckets="buckets"
                :selected-bucket="filters.bucket"
                @select="selectBucket"
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-6">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            From Due Date
                        </label>

                        <input
                            v-model="filters.from_date"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            To Due Date
                        </label>

                        <input
                            v-model="filters.to_date"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Status
                        </label>

                        <select
                            v-model="filters.status"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Bucket
                        </label>

                        <select
                            v-model="filters.bucket"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">All</option>
                            <option value="current">Current</option>
                            <option value="1_30">1-30 Days</option>
                            <option value="31_60">31-60 Days</option>
                            <option value="61_90">61-90 Days</option>
                            <option value="91_180">91-180 Days</option>
                            <option value="181_plus">181+ Days</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Search
                        </label>

                        <div class="relative">
                            <Search class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" />

                            <input
                                v-model="filters.search"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Client, sale no., agent, project, lot..."
                                class="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <select
                        v-model="filters.per_page"
                        @change="applyFilters"
                        class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    >
                        <option :value="10">10 rows</option>
                        <option :value="25">25 rows</option>
                        <option :value="50">50 rows</option>
                    </select>

                    <button
                        @click="exportExcel"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        Export Excel
                    </button>

                    <button
                        @click="exportPdf"
                        class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Export PDF
                    </button>

                    <button
                        @click="printReport"
                        class="rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        Print
                    </button>

                    <button
                        @click="resetFilters"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-50"
                    >
                        Reset
                    </button>

                    <button
                        @click="applyFilters"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>

            <AgingReportTable
                :schedules="schedules"
                :loading="loading"
            />

            <Pagination
                v-if="!loading && pagination.total > 0"
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                :total="pagination.total"
                @previous="previousPage"
                @next="nextPage"
                @go-to-page="goToPage"
            />
        </div>
    </AppLayout>
</template>