<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";
import EmptyState from "@/components/common/EmptyState.vue";
import Pagination from "@/components/common/Pagination.vue";
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";

import SaleStatistics from "@/components/sales/SaleStatistics.vue";
import SaleFilters from "@/components/sales/SaleFilters.vue";
import SaleTable from "@/components/sales/SaleTable.vue";
import SaleDetailsDrawer from "@/components/sales/SaleDetailsDrawer.vue";
import GenerateScheduleModal from "@/components/sales/GenerateScheduleModal.vue";

import saleService from "@/services/saleService.js";
import paymentScheduleService from "@/services/paymentScheduleService.js";
import toast from "@/utils/toast";
import { confirmDelete } from "@/utils/swal";

import { BadgeDollarSign } from "lucide-vue-next";

const sales = ref([]);
const loading = ref(false);
const processing = ref(false);

const showSaleDrawer = ref(false);
const selectedSale = ref(null);

const showGenerateScheduleModal = ref(false);

const filters = reactive({
    search: "",
    status: "",
    per_page: 10,
    page: 1,
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
});

const statistics = reactive({
    total_sales: 0,
    active_sales: 0,
    cancelled_sales: 0,
    fully_paid_sales: 0,
    total_contract_price: 0,
    total_balance: 0,
});

const fetchSummary = async () => {
    try {
        const response = await saleService.getSummary();

        Object.assign(statistics, response.data.data);
    } catch (error) {
        console.error(error);
        toast.error("Failed to load sales summary.");
    }
};

const fetchSales = async () => {
    loading.value = true;

    try {
        const response = await saleService.getSales(filters);

        sales.value = response.data.data ?? [];

        pagination.current_page = response.data.current_page ?? 1;
        pagination.last_page = response.data.last_page ?? 1;
        pagination.total = response.data.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load sales.");
    } finally {
        loading.value = false;
    }
};

const refreshPage = async () => {
    await Promise.all([
        fetchSummary(),
        fetchSales(),
    ]);
};

const searchSales = async () => {
    filters.page = 1;
    await fetchSales();
};

const previousPage = async () => {
    if (pagination.current_page > 1) {
        filters.page = pagination.current_page - 1;
        await fetchSales();
    }
};

const nextPage = async () => {
    if (pagination.current_page < pagination.last_page) {
        filters.page = pagination.current_page + 1;
        await fetchSales();
    }
};

const goToPage = async (page) => {
    if (page === pagination.current_page) return;

    filters.page = page;
    await fetchSales();
};

const viewSale = (sale) => {
    selectedSale.value = sale;
    showSaleDrawer.value = true;
};

const closeSaleDrawer = () => {
    selectedSale.value = null;
    showSaleDrawer.value = false;
};

const openGenerateScheduleModal = (sale) => {
    selectedSale.value = sale;
    showGenerateScheduleModal.value = true;
};

const closeGenerateScheduleModal = () => {
    showGenerateScheduleModal.value = false;
};

const submitGenerateSchedule = async (form) => {
    processing.value = true;

    try {
        await paymentScheduleService.generateSchedule(form);

        toast.success("Payment schedule generated successfully.");

        closeGenerateScheduleModal();

        if (selectedSale.value) {
            selectedSale.value = {
                ...selectedSale.value,
                schedule_refresh_key: Date.now(),
            };
        }

        await fetchSummary();
    } catch (error) {
        console.error(error);

        if (error.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0][0];
            toast.error(firstError);
            return;
        }

        toast.error(
            error.response?.data?.message ||
            "Failed to generate payment schedule."
        );
    } finally {
        processing.value = false;
    }
};

const cancelSale = async (sale) => {
    const confirmed = await confirmDelete(
        `Cancel ${sale.sale_no}?`
    );

    if (!confirmed) return;

    processing.value = true;

    try {
        await saleService.cancelSale(sale.id);

        toast.success("Sale cancelled successfully.");

        await refreshPage();

        if (selectedSale.value?.id === sale.id) {
            closeSaleDrawer();
        }
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || "Failed to cancel sale.");
    } finally {
        processing.value = false;
    }
};

onMounted(() => {
    refreshPage();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Sales"
                description="Manage completed lot sales, contract values, balances, and sale status."
            />

            <SaleStatistics
                :total-sales="statistics.total_sales"
                :active-sales="statistics.active_sales"
                :cancelled-sales="statistics.cancelled_sales"
                :fully-paid-sales="statistics.fully_paid_sales"
                :total-contract-price="statistics.total_contract_price"
                :total-balance="statistics.total_balance"
            />

            <SaleFilters
                v-model:search="filters.search"
                v-model:status="filters.status"
                v-model:per-page="filters.per_page"
                @search="searchSales"
            />

            <TableSkeleton v-if="loading" />

            <SaleTable
                v-else-if="sales.length > 0"
                :sales="sales"
                @view="viewSale"
                @cancel="cancelSale"
            />

            <EmptyState
                v-else
                title="No Sales Found"
                description="Sales will appear here after reservations are converted to sales."
                :icon="BadgeDollarSign"
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

        <SaleDetailsDrawer
            :show="showSaleDrawer"
            :sale="selectedSale"
            @close="closeSaleDrawer"
            @generate-schedule="openGenerateScheduleModal"
        />

        <GenerateScheduleModal
            :show="showGenerateScheduleModal"
            :sale="selectedSale"
            @close="closeGenerateScheduleModal"
            @submit="submitGenerateSchedule"
        />

        <LoadingOverlay
            :show="processing"
            title="Processing Sale"
            description="Please wait while BPMCS updates the sale record."
        />
    </AppLayout>
</template>