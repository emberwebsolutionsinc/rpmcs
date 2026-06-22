<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import EmptyState from "@/components/common/EmptyState.vue";
import Pagination from "@/components/common/Pagination.vue";
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";

import CollectionStatistics from "@/components/collections/CollectionStatistics.vue";
import CollectionTable from "@/components/collections/CollectionTable.vue";
import CollectionDetailsDrawer from "@/components/collections/CollectionDetailsDrawer.vue";
import VoidCollectionModal from "@/components/collections/VoidCollectionModal.vue";

import collectionService from "@/services/collectionService";
import toast from "@/utils/toast";

import {
    Receipt,
    Search,
} from "lucide-vue-next";

const collections = ref([]);
const loading = ref(false);
const processing = ref(false);

const showDetailsDrawer = ref(false);
const selectedCollection = ref(null);

const showVoidModal = ref(false);
const selectedVoidCollection = ref(null);

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

const fetchSummary = async () => {
    try {
        const response = await collectionService.getSummary();

        Object.assign(statistics, response.data.data);
    } catch (error) {
        console.error(error);
        toast.error("Failed to load collection summary.");
    }
};

const fetchCollections = async () => {
    loading.value = true;

    try {
        const response = await collectionService.getCollections(filters);

        collections.value = response.data.data ?? [];

        pagination.current_page = response.data.current_page ?? 1;
        pagination.last_page = response.data.last_page ?? 1;
        pagination.total = response.data.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load collections.");
    } finally {
        loading.value = false;
    }
};

const refreshPage = async () => {
    await Promise.all([
        fetchSummary(),
        fetchCollections(),
    ]);
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

const viewCollection = (collection) => {
    selectedCollection.value = collection;
    showDetailsDrawer.value = true;
};

const closeDetailsDrawer = () => {
    selectedCollection.value = null;
    showDetailsDrawer.value = false;
};

const openVoidModal = (collection) => {
    selectedVoidCollection.value = collection;
    showVoidModal.value = true;
};

const closeVoidModal = () => {
    selectedVoidCollection.value = null;
    showVoidModal.value = false;
};

const submitVoidCollection = async (form) => {
    if (!selectedVoidCollection.value) return;

    processing.value = true;

    try {
        await collectionService.voidCollection(
            selectedVoidCollection.value.id,
            form
        );

        toast.success("Collection voided successfully.");

        closeVoidModal();
        closeDetailsDrawer();

        await refreshPage();
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

const printOR = async (collection) => {
    processing.value = true;

    try {
        const response = await collectionService.printOR(collection.id);

        const blob = new Blob([response.data], {
            type: "application/pdf",
        });

        const url = window.URL.createObjectURL(blob);

        window.open(url, "_blank");

        setTimeout(() => {
            window.URL.revokeObjectURL(url);
        }, 10000);
    } catch (error) {
        console.error(error);
        toast.error("Failed to generate official receipt.");
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
                @view="viewCollection"
                @void="openVoidModal"
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

        <CollectionDetailsDrawer
            :show="showDetailsDrawer"
            :collection="selectedCollection"
            @close="closeDetailsDrawer"
            @print-or="printOR"
            @void="openVoidModal"
        />

        <VoidCollectionModal
            :show="showVoidModal"
            :collection="selectedVoidCollection"
            @close="closeVoidModal"
            @submit="submitVoidCollection"
        />

        <LoadingOverlay
            :show="processing"
            title="Processing Collection"
            description="Please wait while RPMCS updates the collection, sale, and payment schedule records."
        />
    </AppLayout>
</template>