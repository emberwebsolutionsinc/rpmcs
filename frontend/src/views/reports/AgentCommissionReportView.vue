<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";

import AgentCommissionSummary from "@/components/reports/AgentCommissionSummary.vue";
import AgentCommissionTable from "@/components/reports/AgentCommissionTable.vue";
import AgentCommissionSalesTable from "@/components/reports/AgentCommissionSalesTable.vue";

import RecordCommissionPaymentModal from "@/components/reports/RecordCommissionPaymentModal.vue";
import CommissionPaymentHistoryTable from "@/components/reports/CommissionPaymentHistoryTable.vue";
import agentCommissionPaymentService from "@/services/agentCommissionPaymentService";

import reportService from "@/services/reportService";
import toast from "@/utils/toast";

const loading = ref(false);

const summary = ref({});
const agents = ref([]);
const sales = ref({
    data: [],
});

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

const payments = ref([]);
const paymentsLoading = ref(false);

const selectedSale = ref(null);
const showPaymentModal = ref(false);
const paymentProcessing = ref(false);

const fetchPayments = async () => {
    paymentsLoading.value = true;

    try {
        const response =
            await agentCommissionPaymentService.getPayments({
                per_page: 10,
            });

        payments.value = response.data.data?.data ?? [];
    } catch (error) {
        console.error(error);
        toast.error("Failed to load commission payment history.");
    } finally {
        paymentsLoading.value = false;
    }
};

const openPaymentModal = (sale) => {
    selectedSale.value = sale;
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    selectedSale.value = null;
    showPaymentModal.value = false;
};

const submitPayment = async (form) => {
    paymentProcessing.value = true;

    try {
        await agentCommissionPaymentService.recordPayment(form);

        toast.success("Commission payment recorded successfully.");

        closePaymentModal();

        await Promise.all([
            fetchReport(),
            fetchPayments(),
        ]);
    } catch (error) {
        console.error(error);

        if (error.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0][0];
            toast.error(firstError);
            return;
        }

        toast.error(
            error.response?.data?.message ||
            "Failed to record commission payment."
        );
    } finally {
        paymentProcessing.value = false;
    }
};

const deletePayment = async (payment) => {
    if (!confirm("Delete this commission payment?")) {
        return;
    }

    try {
        await agentCommissionPaymentService.deletePayment(payment.id);

        toast.success("Commission payment deleted successfully.");

        await Promise.all([
            fetchReport(),
            fetchPayments(),
        ]);
    } catch (error) {
        console.error(error);
        toast.error("Failed to delete commission payment.");
    }
};


const fetchReport = async () => {
    loading.value = true;

    try {
        const response =
            await reportService.getAgentCommissionReport(filters);

        summary.value = response.data.summary ?? {};
        agents.value = response.data.agents ?? [];
        sales.value = response.data.sales ?? { data: [] };

        pagination.current_page = sales.value.current_page ?? 1;
        pagination.last_page = sales.value.last_page ?? 1;
        pagination.total = sales.value.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load agent commission report.");
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

const exportExcel = async () => {
    loading.value = true;

    try {
        const response =
            await reportService.exportAgentCommissionReportExcel(filters);

        const blob = new Blob([response.data], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `agent-commission-report-${new Date()
            .toISOString()
            .slice(0, 10)}.xlsx`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export agent commission Excel.");
    } finally {
        loading.value = false;
    }
};

const exportPdf = async () => {
    loading.value = true;

    try {
        const response =
            await reportService.exportAgentCommissionReportPdf(filters);

        const blob = new Blob([response.data], {
            type: "application/pdf",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `agent-commission-report-${new Date()
            .toISOString()
            .slice(0, 10)}.pdf`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export agent commission PDF.");
    } finally {
        loading.value = false;
    }
};

const printReport = () => {
    window.print();
};

onMounted(fetchReport, fetchPayments);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Agent Commission Report"
                description="Monitor agent sales performance, commission rates, earned commissions, and unpaid commission exposure."
            />

            <AgentCommissionSummary
                :summary="summary"
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-5">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            From Sale Date
                        </label>

                        <input
                            v-model="filters.from_date"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            To Sale Date
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
                            placeholder="Search agent, client, sale no., lot, project..."
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

            <AgentCommissionTable
                :agents="agents"
            />

            <AgentCommissionSalesTable
                :sales="sales"
                @record-payment="openPaymentModal"
            />

            <CommissionPaymentHistoryTable
                :payments="payments"
                :loading="paymentsLoading"
                @delete="deletePayment"
            />

            <RecordCommissionPaymentModal
                :show="showPaymentModal"
                :sale="selectedSale"
                :processing="paymentProcessing"
                @close="closePaymentModal"
                @submit="submitPayment"
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