<script setup>
import { reactive, ref, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    uploading: {
        type: Boolean,
        default: false,
    },
    progress: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(["close", "submit"]);

const fileInput = ref(null);
const previewImage = ref("");

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

const isImageFile = (file) => {

    if (!file) return false;

    const extension =
        file.name
            .split(".")
            .pop()
            ?.toLowerCase();

    return imageExtensions.includes(extension);

};

const isHeicFile = (file) => {

    if (!file) return false;

    const extension =
        file.name
            .split(".")
            .pop()
            ?.toLowerCase();

    return [
        "heic",
        "heif",
    ].includes(extension);

};

const form = reactive({
    document_type: "",
    document_title: "",
    expires_at: "",
    verification_status: "pending",
    remarks: "",
    file: null,
});

const documentTypes = [
    "PRC License",
    "DHSUD Accreditation",
    "Government ID",
    "Contract",
    "Tax Document",
    "Other",
];

const resetForm = () => {
    form.document_type = "";
    form.document_title = "";
    form.expires_at = "";
    form.verification_status = "pending";
    form.remarks = "";
    form.file = null;

    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

watch(
    () => props.show,
    (value) => {
        if (value) {
            resetForm();
        }
    }
);


const handleDrop = (event) => {

    const file =
        event.dataTransfer.files?.[0];

    form.file = file ?? null;

    previewImage.value = "";

    if (!file) return;

    if (!form.document_title) {

        form.document_title =
            file.name.replace(
                /\.[^/.]+$/,
                ""
            );

    }

    if (
        isImageFile(file) &&
        !isHeicFile(file)
    ) {

        previewImage.value =
            URL.createObjectURL(file);

    }

};

const handleFileChange = (event) => {

    const file =
        event.target.files?.[0];

    form.file = file ?? null;

    previewImage.value = "";

    if (!file) return;

    if (!form.document_title) {

        form.document_title =
            file.name.replace(
                /\.[^/.]+$/,
                ""
            );

    }

    /*
    |--------------------------------------------------------------------------
    | Browser Preview
    |--------------------------------------------------------------------------
    */

    if (
        isImageFile(file) &&
        !isHeicFile(file)
    ) {

        previewImage.value =
            URL.createObjectURL(file);

    }

};

const submit = () => {
    if (!form.document_type) {
        alert("Please select a document type.");
        return;
    }

    if (!form.document_title) {
        alert("Please enter a document title.");
        return;
    }

    if (!form.file) {
        alert("Please select a file.");
        return;
    }

    const formData = new FormData();

    formData.append("document_type", form.document_type);
    formData.append("document_title", form.document_title);
    formData.append("verification_status", form.verification_status);
    formData.append("remarks", form.remarks || "");

    if (form.expires_at) {
        formData.append("expires_at", form.expires_at);
    }

    formData.append("file", form.file, form.file.name);

    emit("submit", formData);
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">
                    Upload Agent Document
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Upload PRC, DHSUD, ID, contract, tax, or other supporting document.
                </p>
            </div>

            <div class="max-h-[75vh] overflow-y-auto px-6 py-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Document Type
                        </label>

                        <select
                            v-model="form.document_type"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">Select Type</option>

                            <option
                                v-for="type in documentTypes"
                                :key="type"
                                :value="type"
                            >
                                {{ type }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Document Title
                        </label>

                        <input
                            v-model="form.document_title"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                            placeholder="Example: PRC License 2026"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Expiration Date
                        </label>

                        <input
                            v-model="form.expires_at"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Verification Status
                        </label>

                        <select
                            v-model="form.verification_status"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            File
                        </label>

                       <div
                            class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center transition hover:border-emerald-400 hover:bg-emerald-50"
                            @dragover.prevent
                            @drop.prevent="handleDrop"
                        >
                           <input
                            ref="fileInput"
                            type="file"
                            accept="
                                image/*,
                                .jpg,
                                .jpeg,
                                .png,
                                .gif,
                                .bmp,
                                .tif,
                                .tiff,
                                .webp,
                                .heic,
                                .HEIC,
                                .heif,
                                .HEIF,
                                .avif,
                                .pdf,
                                .doc,
                                .docx
                            "
                            class="hidden"
                            @change="handleFileChange"
                        />

                            <div class="text-5xl">
                                📁
                            </div>

                            <p class="mt-3 font-semibold text-slate-900">
                                Drag and drop file here
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                or click below to browse from your computer
                            </p>

                            <button
                                type="button"
                                @click="fileInput?.click()"
                                class="mt-4 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                            >
                                Choose File
                            </button>

                           <div
                                v-if="form.file"
                                class="mt-6 rounded-xl border bg-white p-5"
                            >
                               <div class="flex items-center gap-5">

                                <img
                                    v-if="previewImage"
                                    :src="previewImage"
                                    class="h-28 w-28 rounded-xl border object-cover"
                                />

                                <div
                                    v-else-if="isHeicFile(form.file)"
                                    class="flex h-28 w-28 items-center justify-center rounded-xl border bg-slate-100 text-5xl"
                                >
                                    🍎
                                </div>

                                <div
                                    v-else
                                    class="flex h-28 w-28 items-center justify-center rounded-xl border bg-slate-100 text-5xl"
                                >
                                    📄
                                </div>

                                <div>

                                    <h3
                                        class="font-semibold text-slate-900"
                                    >
                                        {{ form.file.name }}
                                    </h3>

                                    <p
                                        class="mt-2 text-sm text-slate-500"
                                    >
                                        {{
                                            (
                                                form.file.size /
                                                1024 /
                                                1024
                                            ).toFixed(2)
                                        }}
                                        MB
                                    </p>

                                    <p
                                        v-if="isHeicFile(form.file)"
                                        class="mt-3 text-xs text-amber-600"
                                    >
                                        HEIC preview is not supported by most browsers.
                                        The image will upload successfully and can be downloaded later.
                                    </p>

                                    <p
                                        v-else
                                        class="mt-3 text-xs text-emerald-600"
                                    >
                                        Ready to upload.
                                    </p>

                                </div>

                            </div>
                        </div>
                        </div>

                        <p class="mt-1 text-xs text-slate-500">
                            Accepted: images, HEIC, HEIF, AVIF, PDF, DOC, DOCX. Max depends on PHP upload limit.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Remarks
                        </label>

                        <textarea
                            v-model="form.remarks"
                            rows="3"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                            placeholder="Optional remarks..."
                        ></textarea>
                    </div>
                </div>

                <div
                    v-if="uploading"
                    class="mt-5"
                >
                    <div class="mb-1 flex justify-between text-xs text-slate-500">
                        <span>Uploading...</span>
                        <span>{{ progress }}%</span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                        <div
                            class="h-full rounded-full bg-emerald-600 transition-all"
                            :style="{ width: progress + '%' }"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                <button
                    type="button"
                    @click="emit('close')"
                    :disabled="uploading"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    @click="submit"
                    :disabled="uploading"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ uploading ? "Uploading..." : "Upload Document" }}
                </button>
            </div>
        </div>
    </div>
</template>