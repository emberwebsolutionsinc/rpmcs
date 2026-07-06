<script setup>
import { computed, ref } from "vue";

import AgentDocumentPreviewModal from "@/components/agent-management/AgentDocumentPreviewModal.vue";
import DeleteConfirmationModal from "@/components/common/DeleteConfirmationModal.vue";
import AgentDocumentUploadModal from "@/components/agent-management/AgentDocumentUploadModal.vue";
import agentService from "@/services/agentService";
import toast from "@/utils/toast";

const props = defineProps({
    agent: {
        type: Object,
        required: true,
    },

    documents: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["refresh"]);

const search = ref("");
const category = ref("");
const status = ref("");
const view = ref("table");

const showUploadModal = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const showPreviewModal = ref(false);
const previewDocumentData = ref(null);
const previewUrl = ref("");

const showDeleteModal = ref(false);
const deleting = ref(false);
const documentToDelete = ref(null);


const categories = [
    "PRC License",
    "DHSUD Accreditation",
    "Government ID",
    "Contract",
    "Tax Document",
    "Other",
];

const statuses = [
    "pending",
    "verified",
    "expired",
];

const filteredDocuments = computed(() => {
    return props.documents.filter((doc) => {
        const keyword = search.value.toLowerCase();

        const matchesSearch =
            !keyword ||
            doc.document_title?.toLowerCase().includes(keyword) ||
            doc.file_name?.toLowerCase().includes(keyword);

        const matchesCategory =
            !category.value ||
            doc.document_type === category.value;

        const matchesStatus =
            !status.value ||
            doc.verification_status === status.value;

        return matchesSearch && matchesCategory && matchesStatus;
    });
});

const formatDate = (value) => {
    if (!value) return "—";

    return new Date(value).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
};

const fileSize = (bytes) => {
    const size = Number(bytes || 0);

    if (size >= 1024 * 1024) {
        return `${(size / 1024 / 1024).toFixed(2)} MB`;
    }

    if (size >= 1024) {
        return `${(size / 1024).toFixed(2)} KB`;
    }

    return `${size} B`;
};

const statusClass = (status) => {
    switch (status) {
        case "verified":
            return "bg-emerald-100 text-emerald-700";

        case "pending":
            return "bg-amber-100 text-amber-700";

        case "expired":
            return "bg-red-100 text-red-700";

        default:
            return "bg-slate-100 text-slate-700";
    }
};

const openUploadModal = () => {
    uploadProgress.value = 0;
    showUploadModal.value = true;
};

const closeUploadModal = () => {
    if (uploading.value) return;

    showUploadModal.value = false;
    uploadProgress.value = 0;
};

const uploadDocument = async (formData) => {
    uploading.value = true;
    uploadProgress.value = 0;

    try {
        await agentService.uploadAgentDocument(
            props.agent.id,
            formData,
            (progressEvent) => {
                if (!progressEvent.total) return;

                uploadProgress.value = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
            }
        );

        toast.success("Document uploaded successfully.");

        showUploadModal.value = false;
        uploadProgress.value = 0;

        emit("refresh");
    } catch (error) {
        console.error(error);

        const message =
            error.response?.data?.message ||
            error.response?.data?.errors?.file?.[0] ||
            "Failed to upload document.";

        toast.error(message);
    } finally {
        uploading.value = false;
    }
};

const previewDocument = async (doc) => {

    try {

        previewDocumentData.value = doc;

        const response =
            await agentService.previewAgentDocument(
                props.agent.id,
                doc.id
            );

        previewUrl.value =
            URL.createObjectURL(
                response.data
            );

        showPreviewModal.value = true;

    } catch (error) {

        console.error(error);

        toast.error(
            "Unable to preview document."
        );

    }

};

const downloadDocument = async (doc) => {
    try {

        const response =
            await agentService.downloadAgentDocument(
                props.agent.id,
                doc.id
            );

        const blob = new Blob(
            [response.data],
            {
                type:
                    response.headers["content-type"] ||
                    doc.mime_type,
            }
        );

        const disposition =
            response.headers["content-disposition"];

        let filename =
            doc.file_name;

        if (disposition) {

            const match =
                disposition.match(
                    /filename="?([^"]+)"?/i
                );

            if (match) {
                filename = match[1];
            }

        }

        const url =
            window.URL.createObjectURL(blob);

        const link =
            document.createElement("a");

        link.href = url;
        link.download = filename;

        document.body.appendChild(link);

        link.click();

        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);

    } catch (error) {

        console.error(error);

        toast.error(
            "Unable to download document."
        );

    }
};

const deleteDocument = (doc) => {
    documentToDelete.value = doc;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    if (deleting.value) return;

    showDeleteModal.value = false;
    documentToDelete.value = null;
};

const confirmDeleteDocument = async () => {
    if (!documentToDelete.value) return;

    deleting.value = true;

    try {
        await agentService.deleteAgentDocument(
            props.agent.id,
            documentToDelete.value.id
        );

        toast.success("Document deleted successfully.");

        closePreviewModal();
        closeDeleteModal();

        emit("refresh");
    } catch (error) {
        console.error(error);
        toast.error("Failed to delete document.");
    } finally {
        deleting.value = false;
    }
};

const closePreviewModal = () => {

    if (previewUrl.value) {

        URL.revokeObjectURL(
            previewUrl.value
        );

    }

    previewUrl.value = "";

    previewDocumentData.value = null;

    showPreviewModal.value = false;

};

</script>
<template>
    <div class="space-y-6">

        <!-- ===================================================== -->
        <!-- Header -->
        <!-- ===================================================== -->

        <div
            class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between"
        >

            <div>

                <h2 class="text-xl font-bold text-slate-900">
                    Agent Documents
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Store, organize and manage all agent documents.
                </p>

            </div>

            <button
                @click="openUploadModal"
                class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
            >
                + Upload Document
            </button>

            <AgentDocumentUploadModal
                :show="showUploadModal"
                :uploading="uploading"
                :progress="uploadProgress"
                @close="closeUploadModal"
                @submit="uploadDocument"
            />

        </div>

        <!-- ===================================================== -->
        <!-- Toolbar -->
        <!-- ===================================================== -->

        <div
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >

            <div class="grid gap-4 lg:grid-cols-5">

                <!-- Search -->

                <div class="lg:col-span-2">

                    <label
                        class="mb-2 block text-xs font-semibold uppercase text-slate-500"
                    >
                        Search
                    </label>

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search title or filename..."
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    />

                </div>

                <!-- Category -->

                <div>

                    <label
                        class="mb-2 block text-xs font-semibold uppercase text-slate-500"
                    >
                        Category
                    </label>

                    <select
                        v-model="category"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm"
                    >

                        <option value="">
                            All Categories
                        </option>

                        <option
                            v-for="item in categories"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>

                    </select>

                </div>

                <!-- Status -->

                <div>

                    <label
                        class="mb-2 block text-xs font-semibold uppercase text-slate-500"
                    >
                        Verification
                    </label>

                    <select
                        v-model="status"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm"
                    >

                        <option value="">
                            All Status
                        </option>

                        <option
                            v-for="item in statuses"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>

                    </select>

                </div>

                <!-- View -->

                <div>

                    <label
                        class="mb-2 block text-xs font-semibold uppercase text-slate-500"
                    >
                        View
                    </label>

                    <div
                        class="flex overflow-hidden rounded-lg border border-slate-300"
                    >

                        <button
                            @click="view='table'"
                            class="flex-1 py-2 text-sm"
                            :class="view==='table'
                                ? 'bg-emerald-600 text-white'
                                : 'bg-white'"
                        >
                            Table
                        </button>

                        <button
                            @click="view='grid'"
                            class="flex-1 py-2 text-sm"
                            :class="view==='grid'
                                ? 'bg-emerald-600 text-white'
                                : 'bg-white'"
                        >
                            Grid
                        </button>

                    </div>

                </div>

            </div>

        </div>

        <!-- ===================================================== -->
        <!-- KPI -->
        <!-- ===================================================== -->

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <p class="text-xs uppercase text-slate-500">
                    Documents
                </p>

                <h2 class="mt-2 text-3xl font-bold">
                    {{ filteredDocuments.length }}
                </h2>

            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <p class="text-xs uppercase text-slate-500">
                    Verified
                </p>

                <h2 class="mt-2 text-3xl font-bold text-emerald-700">
                    {{
                        filteredDocuments.filter(
                            d=>d.verification_status==="verified"
                        ).length
                    }}
                </h2>

            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <p class="text-xs uppercase text-slate-500">
                    Pending
                </p>

                <h2 class="mt-2 text-3xl font-bold text-amber-700">
                    {{
                        filteredDocuments.filter(
                            d=>d.verification_status==="pending"
                        ).length
                    }}
                </h2>

            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >

                <p class="text-xs uppercase text-slate-500">
                    Expired
                </p>

                <h2 class="mt-2 text-3xl font-bold text-red-700">
                    {{
                        filteredDocuments.filter(
                            d=>d.verification_status==="expired"
                        ).length
                    }}
                </h2>

            </div>

        </div>
                <!-- ===================================================== -->
        <!-- TABLE VIEW -->
        <!-- ===================================================== -->

        <div
            v-if="view === 'table'"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                                Document
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                                Category
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                                Uploaded
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                                Expiration
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500">
                                Verification
                            </th>

                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase text-slate-500">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <tr
                            v-for="doc in filteredDocuments"
                            :key="doc.id"
                            class="transition hover:bg-slate-50"
                        >

                            <!-- Document -->

                            <td class="px-5 py-5">

                                <div class="flex items-center gap-4">

                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-2xl"
                                    >

                                        <template
                                            v-if="doc.mime_type?.includes('pdf')"
                                        >
                                            📕
                                        </template>

                                        <template
                                            v-else-if="doc.mime_type?.includes('image')"
                                        >
                                            🖼️
                                        </template>

                                        <template v-else>
                                            📄
                                        </template>

                                    </div>

                                    <div>

                                        <p class="font-semibold text-slate-900">
                                            {{ doc.document_title }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ doc.file_name }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{
                                                (
                                                    doc.file_size /
                                                    1024 /
                                                    1024
                                                ).toFixed(2)
                                            }}
                                            MB
                                        </p>

                                    </div>

                                </div>

                            </td>

                            <!-- Category -->

                            <td class="px-5 py-5">

                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                                >
                                    {{ doc.document_type }}
                                </span>

                            </td>

                            <!-- Uploaded -->

                            <td class="px-5 py-5">

                                <div class="space-y-1">

                                    <p class="text-sm font-medium">
                                        {{ formatDate(doc.created_at) }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{
                                            doc.uploaded_by?.name ||
                                            "System"
                                        }}
                                    </p>

                                </div>

                            </td>

                            <!-- Expiration -->

                            <td class="px-5 py-5">

                                <span
                                    :class="[
                                        'font-medium',
                                        doc.verification_status === 'expired'
                                            ? 'text-red-600'
                                            : 'text-slate-700'
                                    ]"
                                >
                                    {{ formatDate(doc.expires_at) }}
                                </span>

                            </td>

                            <!-- Verification -->

                            <td class="px-5 py-5 text-center">

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="statusClass(doc.verification_status)"
                                >
                                    {{ doc.verification_status }}
                                </span>

                            </td>

                            <!-- Actions -->

                            <td class="px-5 py-5">

                                <div
                                    class="flex justify-end gap-2"
                                >

                                    <button
                                        @click="previewDocument(doc)"
                                        class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold hover:bg-slate-200"
                                    >
                                        Preview
                                    </button>

                                    <button
                                        @click="downloadDocument(doc)"
                                        class="rounded-lg bg-blue-100 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-200"
                                    >
                                        Download
                                    </button>

                                    <button
                                        @click="deleteDocument(doc)"
                                        class="rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-200"
                                    >
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>

                        <!-- Empty -->

                        <tr
                            v-if="filteredDocuments.length === 0"
                        >

                            <td
                                colspan="6"
                                class="px-10 py-20 text-center"
                            >

                                <div
                                    class="mx-auto max-w-md"
                                >

                                    <div class="text-6xl">
                                        📂
                                    </div>

                                    <h3
                                        class="mt-5 text-xl font-semibold"
                                    >
                                        No Documents Found
                                    </h3>

                                    <p
                                        class="mt-3 text-sm text-slate-500"
                                    >
                                        Upload the first document for this
                                        agent or adjust your filters.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>
                <!-- ===================================================== -->
        <!-- GRID VIEW -->
        <!-- ===================================================== -->

        <div
            v-if="view === 'grid'"
            class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
        >

            <div
                v-for="doc in filteredDocuments"
                :key="doc.id"
                class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >

                <div
                    class="flex h-40 items-center justify-center bg-slate-50 text-6xl"
                >

                    <template
                        v-if="doc.mime_type?.includes('pdf')"
                    >
                        📕
                    </template>

                    <template
                        v-else-if="doc.mime_type?.includes('image')"
                    >
                        🖼️
                    </template>

                    <template v-else>
                        📄
                    </template>

                </div>

                <div class="p-5">

                    <div class="flex items-start justify-between gap-3">

                        <div>

                            <h3 class="font-semibold text-slate-900">
                                {{ doc.document_title }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ doc.file_name }}
                            </p>

                        </div>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="statusClass(doc.verification_status)"
                        >
                            {{ doc.verification_status }}
                        </span>

                    </div>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="flex justify-between">

                            <span class="text-slate-500">
                                Category
                            </span>

                            <span class="font-medium">
                                {{ doc.document_type }}
                            </span>

                        </div>

                        <div class="flex justify-between">

                            <span class="text-slate-500">
                                Uploaded
                            </span>

                            <span class="font-medium">
                                {{ formatDate(doc.created_at) }}
                            </span>

                        </div>

                        <div class="flex justify-between">

                            <span class="text-slate-500">
                                Expiry
                            </span>

                            <span class="font-medium">
                                {{ formatDate(doc.expires_at) }}
                            </span>

                        </div>

                    </div>

                    <div class="mt-5 flex gap-2">

                        <button
                            @click="previewDocument(doc)"
                            class="flex-1 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold hover:bg-slate-200"
                        >
                            Preview
                        </button>

                        <button
                            @click="downloadDocument(doc)"
                            class="flex-1 rounded-lg bg-blue-100 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-200"
                        >
                            Download
                        </button>

                        <button
                            @click="deleteDocument(doc)"
                            class="flex-1 rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-200"
                        >
                            Delete
                        </button>

                    </div>

                </div>

            </div>

            <div
                v-if="filteredDocuments.length === 0"
                class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-16 text-center"
            >

                <div class="text-6xl">
                    📂
                </div>

                <h3 class="mt-5 text-xl font-semibold text-slate-900">
                    No Documents Found
                </h3>

                <p class="mt-3 text-sm text-slate-500">
                    Upload documents or adjust your filters.
                </p>

            </div>

        </div>

    </div>

    <DeleteConfirmationModal
        :show="showDeleteModal"
        title="Delete Agent Document"
        :message="`Are you sure you want to delete '${documentToDelete?.document_title || 'this document'}'? This action cannot be undone.`"
        :loading="deleting"
        @close="closeDeleteModal"
        @confirm="confirmDeleteDocument"
    />

    <AgentDocumentPreviewModal
        :show="showPreviewModal"
        :document="previewDocumentData"
        :preview-url="previewUrl"
        @close="closePreviewModal"
        @download="downloadDocument"
        @delete="deleteDocument"
    />
</template>