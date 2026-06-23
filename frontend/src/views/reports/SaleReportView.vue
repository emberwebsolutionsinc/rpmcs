<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";

import SaleReportSummary from "@/components/reports/SaleReportSummary.vue";
import SaleReportBreakdown from "@/components/reports/SaleReportBreakdown.vue";
import SaleReportTable from "@/components/reports/SaleReportTable.vue";

import reportService from "@/services/reportService";
import toast from "@/utils/toast";

import { Search } from "lucide-vue-next";

const sales = ref([]);
const byProject = ref([]);
const byAgent = ref([]);
const loading = ref(false);

const filters = reactive({
    from_date: "",
    to_date: "",
    status: "",
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
    total_sales: 0,
    active_sales: 0,
    fully_paid_sales: 0,
    cancelled_sales: 0,
    total_contract_price: 0,
    total_down_payment: 0,
    total_balance: 0,
});

const fetchReport = async () => {
    loading.value = true;

    try {
        const response = await reportService.getSalesReport(filters);

        Object.assign(summary, response.data.summary ?? {});

        const paginated = response.data.sales ?? {};

        sales.value = paginated.data ?? [];
        byProject.value = response.data.by_project ?? [];
        byAgent.value = response.data.by_agent ?? [];

        pagination.current_page = paginated.current_page ?? 1;
        pagination.last_page = paginated.last_page ?? 1;
        pagination.total = paginated.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load sales report.");
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
    filters.search = "";
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
                title="Sales Report"
                description="Analyze sales performance, contract prices, downpayments, balances, projects, and agents."
            />

            <SaleReportSummary
                :total-sales="summary.total_sales"
                :active-sales="summary.active_sales"
                :fully-paid-sales="summary.fully_paid_sales"
                :cancelled-sales="summary.cancelled_sales"
                :total-contract-price="summary.total_contract_price"
                :total-down-payment="summary.total_down_payment"
                :total-balance="summary.total_balance"
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-5">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            From
                        </label>

                        <input
                            v-model="filters.from_date"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            To
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
                            <option value="active">Active</option>
                            <option value="fully_paid">Fully Paid</option>
                            <option value="cancelled">Cancelled</option>
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
                                placeholder="Sale no., client, agent, lot, project..."
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

            <div class="grid gap-6 xl:grid-cols-2">
                <SaleReportBreakdown
                    title="Sales by Project"
                    description="Contract price and balance grouped by project."
                    :records="byProject"
                    label-key="project_name"
                />

                <SaleReportBreakdown
                    title="Sales by Agent"
                    description="Sales performance grouped by agent."
                    :records="byAgent"
                    label-key="agent_name"
                />
            </div>

            <SaleReportTable
                :sales="sales"
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