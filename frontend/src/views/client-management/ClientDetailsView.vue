<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";

import ClientDocumentsTab from "@/views/client-management/ClientDocumentsTab.vue";
import UploadClientDocumentModal from "@/views/client-management/UploadClientDocumentModal.vue";
import ClientTimelineTab from "@/views/client-management/ClientTimelineTab.vue";


import clientService from "@/services/clientService";
import toast from "@/utils/toast";

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const client = ref(null);
const summary = ref({});
const sales = ref([]);
const collections = ref([]);
const documents = ref([]);
const activities = ref([]);

const showUploadDocumentModal = ref(false);
const uploadingDocument = ref(false);

const activeTab = ref("overview");

const clientId = computed(() => route.params.id);

const tabs = [
    { key: "overview", label: "Overview" },
    { key: "sales", label: "Sales" },
    { key: "collections", label: "Collections" },
    { key: "documents", label: "Documents" },
    { key: "timeline", label: "Timeline" },
];

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

const date = (value) => {
    if (!value) return "—";
    return new Date(value).toLocaleDateString("en-PH");
};

const fullName = (person) => {
    if (!person) return "—";

    return [
        person.first_name,
        person.middle_name,
        person.last_name,
        person.suffix,
    ]
        .filter(Boolean)
        .join(" ");
};

const amountReceived = (collection) => {
    return Number(
        collection?.amount_received ??
            collection?.amount ??
            collection?.paid_amount ??
            collection?.payment_amount ??
            0
    );
};

const saleDownpayment = (sale) => {
    return Number(sale?.downpayment ?? 0);
};

const saleCollections = (sale) => {
    return Number(
        sale?.collections_total ??
            sale?.total_collections ??
            sale?.collection_total ??
            0
    );
};

const saleTotalCollected = (sale) => {
    return Number(
        sale?.total_collection ??
            sale?.total_collected ??
            saleDownpayment(sale) + saleCollections(sale)
    );
};

const saleBalance = (sale) => {
    return Number(
        sale?.balance ??
            Math.max(
                Number(sale?.contract_price ?? 0) -
                    saleTotalCollected(sale),
                0
            )
    );
};

const projectName = (sale) =>
    sale.project?.project_name ??
    sale.lot?.block?.phase?.project?.project_name ??
    "—";

const phaseName = (sale) =>
    sale.phase?.phase_name ??
    sale.lot?.block?.phase?.phase_name ??
    "—";

const blockNumber = (sale) =>
    sale.block?.block_no ??
    sale.lot?.block?.block_no ??
    "—";

const lotNumber = (sale) => sale.lot?.lot_no ?? "—";

const loadClient = async () => {
    loading.value = true;

    try {
        const response = await clientService.getClient(clientId.value);

        client.value = response.data.data ?? null;
        summary.value = response.data.summary ?? {};
        sales.value = response.data.sales ?? [];
        collections.value = response.data.collections ?? [];
        documents.value = response.data.documents ?? [];
        activities.value = response.data.timeline ?? response.data.activities ??[];
    } catch (error) {
        console.error(error);
        toast.error("Failed to load client details.");
    } finally {
        loading.value = false;
    }
};

const openUploadDocumentModal = () => {
    showUploadDocumentModal.value = true;
};

const closeUploadDocumentModal = () => {
    showUploadDocumentModal.value = false;
};

const uploadClientDocument = async (payload) => {
    uploadingDocument.value = true;

    try {
        const formData = new FormData();

        formData.append(
            "document_type",
            payload.document_type || ""
        );

        formData.append(
            "document_name",
            payload.document_name
        );

        formData.append(
            "remarks",
            payload.remarks || ""
        );

        formData.append(
            "file",
            payload.file
        );

        await clientService.uploadClientDocument(
            clientId.value,
            formData
        );

        toast.success(
            "Client document uploaded successfully."
        );

        closeUploadDocumentModal();

        await loadClient();
    } catch (error) {
        console.error(error);

        toast.error(
            error?.response?.data?.message ||
                "Failed to upload client document."
        );
    } finally {
        uploadingDocument.value = false;
    }
};

const goBack = () => {
    router.push("/client-management/clients");
};

const viewSale = (sale) => {
    const saleId = sale.sale_id || sale.id;

    if (!saleId) {
        toast.error("Sale ID not found.");
        return;
    }

    router.push(`/agent-management/sales/${saleId}`);
};

onMounted(loadClient);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <PageHeader
                    title="Client Details"
                    description="View client profile, sales, collections, documents, and timeline."
                />

                <button
                    type="button"
                    @click="goBack"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Back
                </button>
            </div>

            <TableSkeleton v-if="loading" />

            <template v-else-if="client">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">
                                {{ fullName(client) }}
                            </h1>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ client.client_code || "No client code" }}
                            </p>
                        </div>

                        <span class="w-fit rounded-full bg-emerald-100 px-4 py-1 text-xs font-semibold capitalize text-emerald-700">
                            {{ client.status || "active" }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Total Sales
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-900">
                            {{ summary.total_sales || sales.length || 0 }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Contract Price
                        </p>

                        <p class="mt-2 font-bold text-emerald-700">
                            {{ money(summary.total_contract_price) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Downpayment
                        </p>

                        <p class="mt-2 font-bold text-purple-700">
                            {{ money(summary.total_downpayment) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Total Collected
                        </p>

                        <p class="mt-2 font-bold text-blue-700">
                            {{ money(summary.total_collected) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Balance
                        </p>

                        <p class="mt-2 font-bold text-red-700">
                            {{ money(summary.total_balance) }}
                        </p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <nav class="flex flex-wrap gap-2">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                type="button"
                                @click="activeTab = tab.key"
                                class="rounded-lg px-4 py-2 text-sm font-semibold"
                                :class="
                                    activeTab === tab.key
                                        ? 'bg-emerald-600 text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                "
                            >
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <div
                            v-if="activeTab === 'overview'"
                            class="grid gap-6 md:grid-cols-2"
                        >
                            <div class="rounded-xl border border-slate-200 p-5">
                                <h3 class="font-semibold text-slate-900">
                                    Personal Information
                                </h3>

                                <div class="mt-4 space-y-3 text-sm">
                                    <div>
                                        <p class="text-slate-500">Full Name</p>
                                        <p class="font-semibold text-slate-900">
                                            {{ fullName(client) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500">Civil Status</p>
                                        <p class="font-semibold">
                                            {{ client.civil_status || "—" }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500">Birthdate</p>
                                        <p class="font-semibold">
                                            {{ date(client.birthdate) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500">TIN</p>
                                        <p class="font-semibold">
                                            {{ client.tin || "—" }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-5">
                                <h3 class="font-semibold text-slate-900">
                                    Contact Information
                                </h3>

                                <div class="mt-4 space-y-3 text-sm">
                                    <div>
                                        <p class="text-slate-500">
                                            Contact Number
                                        </p>
                                        <p class="font-semibold">
                                            {{ client.contact_number || "—" }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500">Email</p>
                                        <p class="font-semibold">
                                            {{ client.email || "—" }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500">Address</p>
                                        <p class="font-semibold">
                                            {{ client.address || "—" }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-slate-500">Occupation</p>
                                        <p class="font-semibold">
                                            {{ client.occupation || "—" }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'sales'">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                                Sale No.
                                            </th>

                                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                                Property
                                            </th>

                                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                                Agent
                                            </th>

                                            <th class="px-4 py-3 text-right font-semibold text-slate-500">
                                                TCP
                                            </th>

                                            <th class="px-4 py-3 text-right font-semibold text-slate-500">
                                                Downpayment
                                            </th>

                                            <th class="px-4 py-3 text-right font-semibold text-slate-500">
                                                Collections
                                            </th>

                                            <th class="px-4 py-3 text-right font-semibold text-slate-500">
                                                Total Collected
                                            </th>

                                            <th class="px-4 py-3 text-right font-semibold text-slate-500">
                                                Balance
                                            </th>

                                            <th class="px-4 py-3 text-center font-semibold text-slate-500">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="sale in sales"
                                            :key="sale.id"
                                            class="hover:bg-slate-50"
                                        >
                                            <td class="px-4 py-4 font-semibold text-slate-900">
                                                {{ sale.sale_no || "—" }}
                                            </td>

                                            <td class="px-4 py-4">
                                                <p class="font-semibold text-slate-900">
                                                    {{ projectName(sale) }}
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    Phase {{ phaseName(sale) }}
                                                    • Block {{ blockNumber(sale) }}
                                                    • Lot {{ lotNumber(sale) }}
                                                </p>
                                            </td>

                                            <td class="px-4 py-4">
                                                {{ fullName(sale.agent) }}
                                            </td>

                                            <td class="px-4 py-4 text-right">
                                                {{ money(sale.contract_price) }}
                                            </td>

                                            <td class="px-4 py-4 text-right text-purple-700">
                                                {{ money(saleDownpayment(sale)) }}
                                            </td>

                                            <td class="px-4 py-4 text-right text-blue-700">
                                                {{ money(saleCollections(sale)) }}
                                            </td>

                                            <td class="px-4 py-4 text-right font-semibold text-emerald-700">
                                                {{ money(saleTotalCollected(sale)) }}
                                            </td>

                                            <td class="px-4 py-4 text-right font-semibold text-red-700">
                                                {{ money(saleBalance(sale)) }}
                                            </td>

                                            <td class="px-4 py-4 text-center">
                                                <button
                                                    type="button"
                                                    @click="viewSale(sale)"
                                                    class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                                >
                                                    View
                                                </button>
                                            </td>
                                        </tr>

                                        <tr v-if="sales.length === 0">
                                            <td
                                                colspan="9"
                                                class="px-4 py-10 text-center text-slate-500"
                                            >
                                                No sales found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'collections'">
                            <div class="mb-4 grid gap-4 md:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase text-slate-500">
                                        Downpayment
                                    </p>
                                    <p class="mt-2 font-bold text-purple-700">
                                        {{ money(summary.total_downpayment) }}
                                    </p>
                                </div>


                                <div class="rounded-xl border border-slate-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase text-slate-500">
                                        Total Collected
                                    </p>
                                    <p class="mt-2 font-bold text-emerald-700">
                                        {{ money(summary.total_collected) }}
                                    </p>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                                Date
                                            </th>

                                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                                Sale No.
                                            </th>

                                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                                Reference
                                            </th>

                                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                                Remarks
                                            </th>

                                            <th class="px-4 py-3 text-right font-semibold text-slate-500">
                                                Amount Received
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="collection in collections"
                                            :key="collection.id"
                                            class="hover:bg-slate-50"
                                        >
                                            <td class="px-4 py-4">
                                                {{
                                                    date(
                                                        collection.payment_date ||
                                                            collection.collection_date ||
                                                            collection.created_at
                                                    )
                                                }}
                                            </td>

                                            <td class="px-4 py-4 font-semibold text-slate-900">
                                                {{
                                                    collection.sale?.sale_no ||
                                                    collection.sale_no ||
                                                    "—"
                                                }}
                                            </td>

                                            <td class="px-4 py-4">
                                                {{
                                                    collection.reference_no ||
                                                    collection.or_no ||
                                                    "—"
                                                }}
                                            </td>

                                            <td class="px-4 py-4 text-slate-600">
                                                {{ collection.remarks || "—" }}
                                            </td>

                                            <td class="px-4 py-4 text-right font-semibold text-emerald-700">
                                                {{ money(amountReceived(collection)) }}
                                            </td>
                                        </tr>

                                        <tr v-if="collections.length === 0">
                                            <td
                                                colspan="5"
                                                class="px-4 py-10 text-center text-slate-500"
                                            >
                                                No collection records found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <ClientDocumentsTab
                            v-else-if="activeTab === 'documents'"
                            :client="client"
                            :documents="documents"
                            @upload="openUploadDocumentModal"
                            @refresh="loadClient"
                        />

                        <ClientTimelineTab
                            v-else-if="activeTab === 'timeline'"
                            :activities="activities"
                        />
                    </div>
                </div>
            </template>

            <div
                v-else
                class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500 shadow-sm"
            >
                Client not found.
            </div>
        </div>

        <UploadClientDocumentModal
            :show="showUploadDocumentModal"
            :client="client"
            :saving="uploadingDocument"
            @close="closeUploadDocumentModal"
            @submit="uploadClientDocument"
        />
    </AppLayout>
</template>