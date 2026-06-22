<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";

import CollectionReportSummary from "@/components/reports/CollectionReportSummary.vue";
import CollectionReportTable from "@/components/reports/CollectionReportTable.vue";

import reportService from "@/services/reportService";
import toast from "@/utils/toast";

import { Search } from "lucide-vue-next";

const collections = ref([]);
const loading = ref(false);

const printReport = () => {
    window.print();
};

const filters = reactive({
    from_date: "",
    to_date: "",
    status: "",
    payment_method: "",
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
    posted_count: 0,
    voided_count: 0,
    gross_collections: 0,
    voided_amount: 0,
    net_collections: 0,
});

const exportPdf = async () => {
    loading.value = true;

    try {
        const response = await reportService.exportCollectionReportPdf(filters);

        const blob = new Blob([response.data], {
            type: "application/pdf",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `collections-report-${new Date()
            .toISOString()
            .slice(0, 10)}.pdf`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export collection report PDF.");
    } finally {
        loading.value = false;
    }
};


const fetchReport = async () => {
    loading.value = true;

    try {
        const response = await reportService.getCollectionReport(filters);

        Object.assign(summary, response.data.summary ?? {});

        const paginated = response.data.collections ?? {};

        collections.value = paginated.data ?? [];

        pagination.current_page = paginated.current_page ?? 1;
        pagination.last_page = paginated.last_page ?? 1;
        pagination.total = paginated.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load collection report.");
    } finally {
        loading.value = false;
    }
};

const exportExcel = async () => {
    loading.value = true;

    try {
        const response =
            await reportService.exportCollectionReportExcel(filters);

        const blob = new Blob([response.data], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const url = window.URL.createObjectURL(blob);

        const link = document.createElement("a");

        link.href = url;
        link.download = `collections-report-${new Date()
            .toISOString()
            .slice(0, 10)}.xlsx`;

        document.body.appendChild(link);
        link.click();

        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export collection report.");
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
    filters.payment_method = "";
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

onMounted(() => {
    fetchReport();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Collections Report"
                description="Analyze posted and voided collections, official receipts, payment methods, and collection totals."
            />

            <CollectionReportSummary
                :posted-count="summary.posted_count"
                :voided-count="summary.voided_count"
                :gross-collections="summary.gross_collections"
                :voided-amount="summary.voided_amount"
                :net-collections="summary.net_collections"
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-6">
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
                            <option value="posted">Posted</option>
                            <option value="voided">Voided</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Method
                        </label>
                        <select
                            v-model="filters.payment_method"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">All</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="gcash">GCash</option>
                            <option value="maya">Maya</option>
                            <option value="other">Other</option>
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
                                placeholder="OR no., collection no., client, sale no..."
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

            <CollectionReportTable
                :collections="collections"
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