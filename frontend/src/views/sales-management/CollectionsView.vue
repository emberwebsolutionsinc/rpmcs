<script setup>
import { computed, onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import EmptyState from "@/components/common/EmptyState.vue";
import Pagination from "@/components/common/Pagination.vue";
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";

import CollectionStatistics from "@/components/collections/CollectionStatistics.vue";
import CollectionTable from "@/components/collections/CollectionTable.vue";

import collectionService from "@/services/collectionService";
import toast from "@/utils/toast";
import { confirmDelete } from "@/utils/swal";

import {
    Receipt,
    Search,
} from "lucide-vue-next";

const collections = ref([]);
const loading = ref(false);
const processing = ref(false);

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
    total_collected: 0,
    today_collected: 0,
    monthly_collected: 0,
    outstanding_balance: 0,
});

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
        maximumFractionDigits: 0,
    });
};

const computeStatisticsFromCurrentPage = () => {
    const today = new Date().toISOString().slice(0, 10);
    const currentMonth = new Date().toISOString().slice(0, 7);

    const posted = collections.value.filter((item) => {
        return item.status === "posted";
    });

    statistics.total_collected = posted.reduce((sum, item) => {
        return sum + Number(item.amount_paid || 0);
    }, 0);

    statistics.today_collected = posted
        .filter((item) => String(item.payment_date).slice(0, 10) === today)
        .reduce((sum, item) => sum + Number(item.amount_paid || 0), 0);

    statistics.monthly_collected = posted
        .filter((item) => String(item.payment_date).slice(0, 7) === currentMonth)
        .reduce((sum, item) => sum + Number(item.amount_paid || 0), 0);

    statistics.outstanding_balance = posted.reduce((sum, item) => {
        return sum + Number(item.sale?.balance || 0);
    }, 0);
};

const fetchCollections = async () => {
    loading.value = true;

    try {
        const response = await collectionService.getCollections(filters);

        collections.value = response.data.data ?? [];

        pagination.current_page = response.data.current_page ?? 1;
        pagination.last_page = response.data.last_page ?? 1;
        pagination.total = response.data.total ?? 0;

        computeStatisticsFromCurrentPage();
    } catch (error) {
        console.error(error);
        toast.error("Failed to load collections.");
    } finally {
        loading.value = false;
    }
};

const searchCollections = async () => {
    filters.page = 1;
    await fetchCollections();
};

const previousPage = async () => {
    if (pagination.current_page > 1) {
        filters.page = pagination.current_page - 1;
        await fetchCollections();
    }
};

const nextPage = async () => {
    if (pagination.current_page < pagination.last_page) {
        filters.page = pagination.current_page + 1;
        await fetchCollections();
    }
};

const goToPage = async (page) => {
    if (page === pagination.current_page) return;

    filters.page = page;
    await fetchCollections();
};

const voidCollection = async (collection) => {
    const confirmed = await confirmDelete(
        `Void ${collection.collection_no}? This will restore the sale and installment balance.`
    );

    if (!confirmed) return;

    processing.value = true;

    try {
        await collectionService.voidCollection(collection.id);

        toast.success("Collection voided successfully.");

        await fetchCollections();
    } catch (error) {
        console.error(error);

        if (error.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0][0];
            toast.error(firstError);
            return;
        }

        toast.error(
            error.response?.data?.message ||
            "Failed to void collection."
        );
    } finally {
        processing.value = false;
    }
};

onMounted(() => {
    fetchCollections();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Collections"
                description="Monitor posted payments, official receipts, collection methods, and voided transactions."
            />

            <CollectionStatistics
                :total-collected="statistics.total_collected"
                :today-collected="statistics.today_collected"
                :monthly-collected="statistics.monthly_collected"
                :outstanding-balance="statistics.outstanding_balance"
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="relative w-full lg:max-w-lg">
                        <Search class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" />

                        <input
                            v-model="filters.search"
                            @keyup.enter="searchCollections"
                            type="text"
                            placeholder="Search collection no., OR no., client, sale no..."
                            class="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select
                            v-model="filters.status"
                            @change="searchCollections"
                            class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">All Status</option>
                            <option value="posted">Posted</option>
                            <option value="voided">Voided</option>
                        </select>

                        <select
                            v-model="filters.per_page"
                            @change="searchCollections"
                            class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option :value="10">10 rows</option>
                            <option :value="25">25 rows</option>
                            <option :value="50">50 rows</option>
                        </select>

                        <button
                            @click="searchCollections"
                            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-50"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <CollectionTable
                v-if="collections.length > 0 || loading"
                :collections="collections"
                :loading="loading"
                @void="voidCollection"
            />

            <EmptyState
                v-else
                title="No Collections Found"
                description="Collections will appear here after payments are posted from the Sale drawer."
                :icon="Receipt"
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

        <LoadingOverlay
            :show="processing"
            title="Processing Collection"
            description="Please wait while RPMCS updates the collection, sale, and payment schedule records."
        />
    </AppLayout>
</template>