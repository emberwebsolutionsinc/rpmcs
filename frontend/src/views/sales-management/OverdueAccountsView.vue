<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import EmptyState from "@/components/common/EmptyState.vue";
import Pagination from "@/components/common/Pagination.vue";

import OverdueStatistics from "@/components/overdue/OverdueStatistics.vue";
import OverdueTable from "@/components/overdue/OverdueTable.vue";
import TopDelinquentsTable from "@/components/overdue/TopDelinquentsTable.vue";

import overdueService from "@/services/overdueService";
import toast from "@/utils/toast";

import {
    AlertTriangle,
    Search,
} from "lucide-vue-next";

const accounts = ref([]);
const topDelinquents = ref([]);
const loading = ref(false);

const filters = reactive({
    search: "",
    per_page: 10,
    page: 1,
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
});

const statistics = reactive({
    overdue_accounts: 0,
    overdue_installments: 0,
    total_overdue_amount: 0,
    critical_accounts: 0,
    due_this_week: 0,
    due_this_month: 0,
});

const fetchSummary = async () => {
    try {
        const response = await overdueService.getSummary();

        Object.assign(statistics, response.data.data);
    } catch (error) {
        console.error(error);
        toast.error("Failed to load overdue summary.");
    }
};

const fetchTopDelinquents = async () => {
    try {
        const response = await overdueService.getTopDelinquents();

        topDelinquents.value = response.data.data ?? [];
    } catch (error) {
        console.error(error);
        toast.error("Failed to load top delinquent accounts.");
    }
};

const fetchOverdueAccounts = async () => {
    loading.value = true;

    try {
        const response = await overdueService.getOverdueAccounts(filters);

        accounts.value = response.data.data ?? [];

        pagination.current_page = response.data.current_page ?? 1;
        pagination.last_page = response.data.last_page ?? 1;
        pagination.total = response.data.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load overdue accounts.");
    } finally {
        loading.value = false;
    }
};

const refreshPage = async () => {
    await Promise.all([
        fetchSummary(),
        fetchOverdueAccounts(),
        fetchTopDelinquents(),
    ]);
};

const searchAccounts = async () => {
    filters.page = 1;

    await Promise.all([
        fetchOverdueAccounts(),
        fetchTopDelinquents(),
    ]);
};

const previousPage = async () => {
    if (pagination.current_page > 1) {
        filters.page = pagination.current_page - 1;
        await fetchOverdueAccounts();
    }
};

const nextPage = async () => {
    if (pagination.current_page < pagination.last_page) {
        filters.page = pagination.current_page + 1;
        await fetchOverdueAccounts();
    }
};

const goToPage = async (page) => {
    if (page === pagination.current_page) return;

    filters.page = page;
    await fetchOverdueAccounts();
};

onMounted(() => {
    refreshPage();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Overdue Accounts"
                description="Monitor buyers with unpaid overdue installments and prioritize collection follow-ups."
            />

            <OverdueStatistics
                :overdue-accounts="statistics.overdue_accounts"
                :overdue-installments="statistics.overdue_installments"
                :total-overdue-amount="statistics.total_overdue_amount"
                :critical-accounts="statistics.critical_accounts"
                :due-this-week="statistics.due_this_week"
                :due-this-month="statistics.due_this_month"
            />

            <TopDelinquentsTable
                :records="topDelinquents"
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:max-w-lg">
                        <Search class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" />

                        <input
                            v-model="filters.search"
                            @keyup.enter="searchAccounts"
                            type="text"
                            placeholder="Search client, sale no., lot no..."
                            class="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                        />
                    </div>

                    <div class="flex gap-2">
                        <select
                            v-model="filters.per_page"
                            @change="searchAccounts"
                            class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option :value="10">10 rows</option>
                            <option :value="25">25 rows</option>
                            <option :value="50">50 rows</option>
                        </select>

                        <button
                            @click="searchAccounts"
                            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-50"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <OverdueTable
                v-if="accounts.length > 0 || loading"
                :accounts="accounts"
                :loading="loading"
            />

            <EmptyState
                v-else
                title="No Overdue Accounts"
                description="There are currently no overdue payment schedules."
                :icon="AlertTriangle"
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