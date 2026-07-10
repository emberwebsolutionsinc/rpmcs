<script setup>
import { computed, ref } from "vue";

import clientService from "@/services/clientService";
import toast from "@/utils/toast";

const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
    documents: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    "upload",
    "refresh",
]);

const previewingId = ref(null);
const downloadingId = ref(null);
const deletingId = ref(null);

const hasDocuments = computed(() => {
    return props.documents.length > 0;
});

const fullName = (person) => {
    if (!person) return "—";

    return [
        person.first_name,
        person.middle_name,
        person.last_name,
    ]
        .filter(Boolean)
        .join(" ");
};

const formatDate = (value) => {
    if (!value) return "—";

    return new Date(value).toLocaleString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatFileSize = (bytes) => {
    const size = Number(bytes || 0);

    if (size <= 0) return "0 KB";

    const units = ["B", "KB", "MB", "GB"];

    let index = 0;
    let value = size;

    while (value >= 1024 && index < units.length - 1) {
        value /= 1024;
        index++;
    }

    return `${value.toFixed(index === 0 ? 0 : 2)} ${units[index]}`;
};

const documentTypeLabel = (document) => {
    return (
        document.document_type ||
        "Other Document"
    );
};

const uploaderName = (document) => {
    return (
        document.uploaded_by?.name ||
        fullName(document.uploaded_by) ||
        "System"
    );
};

const openUploadModal = () => {
    emit("upload");
};

const previewDocument = async (document) => {
    if (!document?.id) {
        toast.error("Document ID not found.");
        return;
    }

    previewingId.value = document.id;

    try {
        const response =
            await clientService.previewClientDocument(
                props.client.id,
                document.id
            );

        const blob = new Blob(
            [response.data],
            {
                type:
                    response.headers["content-type"] ||
                    document.mime_type ||
                    "application/octet-stream",
            }
        );

        const url = window.URL.createObjectURL(blob);

        window.open(url, "_blank");

        setTimeout(() => {
            window.URL.revokeObjectURL(url);
        }, 60000);
    } catch (error) {
        console.error(error);

        toast.error(
            error?.response?.data?.message ||
                "Failed to preview document."
        );
    } finally {
        previewingId.value = null;
    }
};

const downloadDocument = async (document) => {
    if (!document?.id) {
        toast.error("Document ID not found.");
        return;
    }

    downloadingId.value = document.id;

    try {
        const response =
            await clientService.downloadClientDocument(
                props.client.id,
                document.id
            );

        const blob = new Blob(
            [response.data],
            {
                type:
                    response.headers["content-type"] ||
                    document.mime_type ||
                    "application/octet-stream",
            }
        );

        const url = window.URL.createObjectURL(blob);

        const link = window.document.createElement("a");

        link.href = url;

        link.download =
            document.file_name ||
            document.document_name ||
            "client-document";

        window.document.body.appendChild(link);

        link.click();

        window.document.body.removeChild(link);

        window.URL.revokeObjectURL(url);

        toast.success("Document downloaded.");
    } catch (error) {
        console.error(error);

        toast.error(
            error?.response?.data?.message ||
                "Failed to download document."
        );
    } finally {
        downloadingId.value = null;
    }
};

const deleteDocument = async (document) => {
    if (!document?.id) {
        toast.error("Document ID not found.");
        return;
    }

    const confirmed = window.confirm(
        `Delete "${document.document_name || document.file_name}"?`
    );

    if (!confirmed) return;

    deletingId.value = document.id;

    try {
        await clientService.deleteClientDocument(
            props.client.id,
            document.id
        );

        toast.success("Document deleted successfully.");

        emit("refresh");
    } catch (error) {
        console.error(error);

        toast.error(
            error?.response?.data?.message ||
                "Failed to delete document."
        );
    } finally {
        deletingId.value = null;
    }
};
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="font-semibold text-slate-900">
                    Client Documents
                </h3>

                <p class="text-sm text-slate-500">
                    Upload, preview, download, and manage client documents.
                </p>
            </div>

            <button
                type="button"
                @click="openUploadModal"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
            >
                Upload Document
            </button>
        </div>

        <div
            v-if="hasDocuments"
            class="overflow-hidden rounded-xl border border-slate-200"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                Document
                            </th>

                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                Type
                            </th>

                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                File
                            </th>

                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                Uploaded By
                            </th>

                            <th class="px-4 py-3 text-left font-semibold text-slate-500">
                                Uploaded
                            </th>

                            <th class="px-4 py-3 text-right font-semibold text-slate-500">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr
                            v-for="document in documents"
                            :key="document.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-900">
                                    {{
                                        document.document_name ||
                                        "Untitled Document"
                                    }}
                                </p>

                                <p
                                    v-if="document.remarks"
                                    class="mt-1 max-w-md text-xs text-slate-500"
                                >
                                    {{ document.remarks }}
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ documentTypeLabel(document) }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <p class="font-medium text-slate-900">
                                    {{ document.file_name || "—" }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ formatFileSize(document.file_size) }}
                                    <span v-if="document.mime_type">
                                        · {{ document.mime_type }}
                                    </span>
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                {{ uploaderName(document) }}
                            </td>

                            <td class="px-4 py-4 text-slate-600">
                                {{ formatDate(document.created_at) }}
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        @click="previewDocument(document)"
                                        :disabled="previewingId === document.id"
                                        class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {{
                                            previewingId === document.id
                                                ? "Opening..."
                                                : "Preview"
                                        }}
                                    </button>

                                    <button
                                        type="button"
                                        @click="downloadDocument(document)"
                                        :disabled="downloadingId === document.id"
                                        class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {{
                                            downloadingId === document.id
                                                ? "Downloading..."
                                                : "Download"
                                        }}
                                    </button>

                                    <button
                                        type="button"
                                        @click="deleteDocument(document)"
                                        :disabled="deletingId === document.id"
                                        class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {{
                                            deletingId === document.id
                                                ? "Deleting..."
                                                : "Delete"
                                        }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-else
            class="rounded-xl border border-dashed border-slate-300 px-6 py-12 text-center"
        >
            <div class="text-4xl">
                📄
            </div>

            <h4 class="mt-4 font-semibold text-slate-900">
                No client documents
            </h4>

            <p class="mt-2 text-sm text-slate-500">
                Upload identification, contracts, proof of billing, and other client records.
            </p>

            <button
                type="button"
                @click="openUploadModal"
                class="mt-5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
            >
                Upload First Document
            </button>
        </div>
    </div>
</template>