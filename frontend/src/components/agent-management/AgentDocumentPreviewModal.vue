<script setup>
import { computed } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    document: {
        type: Object,
        default: null,
    },

    previewUrl: {
        type: String,
        default: "",
    },
});

const emit = defineEmits([
    "close",
    "download",
    "delete",
]);

const imageExtensions = [
    "jpg",
    "jpeg",
    "png",
    "gif",
    "bmp",
    "tif",
    "tiff",
    "webp",
    "heic",
    "heif",
    "avif",
];

const isImage = computed(() => {

    if (!props.document) return false;

    const extension =
        props.document.file_name
            ?.split(".")
            .pop()
            ?.toLowerCase();

    return imageExtensions.includes(extension);

});

const isHeic = computed(() => {

    if (!props.document) return false;

    const extension =
        props.document.file_name
            ?.split(".")
            .pop()
            ?.toLowerCase();

    return [
        "heic",
        "heif",
    ].includes(extension);

});

const isPdf = computed(() => {
    if (!props.document) return false;

    return props.document.mime_type === "application/pdf";
});

const formatDate = (value) => {
    if (!value) return "—";

    return new Date(value).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric",
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

    return `${size} Bytes`;
};

const badgeClass = (status) => {
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
</script>
<template>
    <div
        v-if="show && document"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
    >
        <div class="flex max-h-[90vh] w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex flex-1 items-center justify-center bg-slate-100 p-6">
                <img
                    v-if="isImage"
                    :src="previewUrl"
                    class="max-h-[78vh] max-w-full rounded-xl object-contain"
                    alt="Document preview"
                />

                <iframe
                    v-else-if="isPdf"
                    :src="previewUrl"
                    class="h-[78vh] w-full rounded-xl bg-white"
                ></iframe>

                <div
                    v-else
                    class="text-center text-slate-500"
                >
                    <div class="text-6xl">📄</div>
                    <p class="mt-4 font-semibold">
                        Preview not available for this file type.
                    </p>
                    <p class="mt-1 text-sm">
                        Please download the document to view it.
                    </p>
                </div>
            </div>

            <aside class="w-full max-w-sm border-l border-slate-200 bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ document.document_title }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ document.file_name }}
                        </p>
                    </div>

                    <button
                        @click="emit('close')"
                        class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm hover:bg-slate-200"
                    >
                        ✕
                    </button>
                </div>

                <div class="mt-5">
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                        :class="badgeClass(document.verification_status)"
                    >
                        {{ document.verification_status || "pending" }}
                    </span>
                </div>

                <div class="mt-6 space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Category
                        </p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ document.document_type || "—" }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            File Size
                        </p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ fileSize(document.file_size) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Uploaded Date
                        </p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ formatDate(document.created_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Expiration Date
                        </p>
                        <p class="mt-1 font-medium text-slate-900">
                            {{ formatDate(document.expires_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Remarks
                        </p>
                        <p class="mt-1 text-slate-700">
                            {{ document.remarks || "—" }}
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-2">
                    <button
                        @click="emit('download', document)"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Download
                    </button>

                    <button
                        @click="emit('delete', document)"
                        class="rounded-lg bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100"
                    >
                        Delete Document
                    </button>

                    <button
                        @click="emit('close')"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50"
                    >
                        Close
                    </button>
                </div>
            </aside>
        </div>
    </div>
</template>