<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";

import CommissionPaymentReportSummary from "@/components/reports/CommissionPaymentReportSummary.vue";
import CommissionPaymentBreakdownTable from "@/components/reports/CommissionPaymentBreakdownTable.vue";
import CommissionPaymentReportTable from "@/components/reports/CommissionPaymentReportTable.vue";
import DeletedCommissionPaymentTable from "@/components/reports/DeletedCommissionPaymentTable.vue";

import reportService from "@/services/reportService";
import toast from "@/utils/toast";

const loading = ref(false);

const summary = ref({
    total_active_payments: 0,
    total_active_amount: 0,
    total_deleted_payments: 0,
    total_deleted_amount: 0,
    net_payment_count: 0,
    net_payment_amount: 0,
});

const byAgent = ref([]);
const byMethod = ref([]);
const deletedPayments = ref([]);

const payments = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    total: 0,
});

const filters = reactive({
    from_date: "",
    to_date: "",
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

const fetchReport = async () => {
    loading.value = true;

    try {
        const response = await reportService.getCommissionPaymentReport(filters);

        summary.value = response.data.summary ?? {};
        byAgent.value = response.data.by_agent ?? [];
        byMethod.value = response.data.by_method ?? [];
        deletedPayments.value = response.data.deleted_payments ?? [];

        payments.value = response.data.payments ?? {
            data: [],
            current_page: 1,
            last_page: 1,
            total: 0,
        };

        pagination.current_page = payments.value.current_page ?? 1;
        pagination.last_page = payments.value.last_page ?? 1;
        pagination.total = payments.value.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load commission payment report.");
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


const exportExcel = async () => {
    loading.value = true;

    try {
        const response =
            await reportService.exportCommissionPaymentReportExcel(filters);

        const blob = new Blob([response.data], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `commission-payment-report-${new Date()
            .toISOString()
            .slice(0, 10)}.xlsx`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export commission payment Excel.");
    } finally {
        loading.value = false;
    }
};

const exportPdf = async () => {
    loading.value = true;

    try {
        const response =
            await reportService.exportCommissionPaymentReportPdf(filters);

        const blob = new Blob([response.data], {
            type: "application/pdf",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `commission-payment-report-${new Date()
            .toISOString()
            .slice(0, 10)}.pdf`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export commission payment PDF.");
    } finally {
        loading.value = false;
    }
};

const printReport = () => {
    window.print();
};

</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Commission Payment Report"
                description="Review active commission payments, deleted payments, payment methods, and agent payment totals."
            />

            <CommissionPaymentReportSummary
                :summary="summary"
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-5">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            From Payment Date
                        </label>

                        <input
                            v-model="filters.from_date"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            To Payment Date
                        </label>

                        <input
                            v-model="filters.to_date"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
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
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="gcash">GCash</option>
                            <option value="check">Check</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Search
                        </label>

                        <input
                            v-model="filters.search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search agent, sale no., client, reference..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
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
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <CommissionPaymentBreakdownTable
                    title="Payments by Agent"
                    :rows="byAgent"
                    type="agent"
                />

                <CommissionPaymentBreakdownTable
                    title="Payments by Method"
                    :rows="byMethod"
                    type="method"
                />
            </div>

            <CommissionPaymentReportTable
                :payments="payments"
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

            <DeletedCommissionPaymentTable
                :payments="deletedPayments"
            />
        </div>
    </AppLayout>
</template>