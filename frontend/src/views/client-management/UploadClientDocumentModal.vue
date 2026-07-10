<script setup>
import { reactive, ref, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    client: {
        type: Object,
        default: null,
    },

    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "close",
    "submit",
]);

const fileInput = ref(null);

const form = reactive({
    document_type: "",
    document_name: "",
    remarks: "",
    file: null,
});

const errors = reactive({});

const documentTypes = [
    "Valid ID",
    "Reservation Agreement",
    "Contract to Sell",
    "Proof of Billing",
    "Birth Certificate",
    "Marriage Certificate",
    "Special Power of Attorney",
    "Income Document",
    "Tax Document",
    "Payment Document",
    "Other",
];

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

const resetErrors = () => {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
};

const resetForm = () => {
    form.document_type = "";
    form.document_name = "";
    form.remarks = "";
    form.file = null;

    resetErrors();

    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

watch(
    () => props.show,
    (isVisible) => {
        if (isVisible) {
            resetForm();
        }
    }
);

const handleFileChange = (event) => {
    const selectedFile = event.target.files?.[0] ?? null;

    form.file = selectedFile;

    if (selectedFile && !form.document_name.trim()) {
        form.document_name = selectedFile.name.replace(/\.[^/.]+$/, "");
    }

    if (selectedFile) {
        delete errors.file;
    }
};

const validate = () => {
    resetErrors();

    if (!form.document_type) {
        errors.document_type = "Document type is required.";
    }

    if (!form.document_name.trim()) {
        errors.document_name = "Document name is required.";
    }

    if (!form.file) {
        errors.file = "Please select a file.";
    }

    if (form.file) {
        const allowedMimeTypes = [
            "application/pdf",
            "image/jpeg",
            "image/png",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        ];

        const maximumSize = 10 * 1024 * 1024;

        if (!allowedMimeTypes.includes(form.file.type)) {
            errors.file =
                "Only PDF, JPG, JPEG, PNG, DOC, and DOCX files are allowed.";
        }

        if (form.file.size > maximumSize) {
            errors.file = "The selected file must not exceed 10 MB.";
        }
    }

    return Object.keys(errors).length === 0;
};

const submit = () => {
    if (!validate()) {
        return;
    }

    emit("submit", {
        document_type: form.document_type,
        document_name: form.document_name.trim(),
        remarks: form.remarks.trim(),
        file: form.file,
    });
};

const closeModal = () => {
    if (props.saving) return;

    emit("close");
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4"
        @click.self="closeModal"
    >
        <div
            class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl"
        >
            <div
                class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5"
            >
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Upload Client Document
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Upload a document for
                        <span class="font-semibold text-slate-700">
                            {{ fullName(client) }}
                        </span>.
                    </p>
                </div>

                <button
                    type="button"
                    @click="closeModal"
                    :disabled="saving"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                    aria-label="Close modal"
                >
                    ✕
                </button>
            </div>

            <div class="max-h-[72vh] overflow-y-auto px-6 py-5">
                <div class="space-y-5">
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Document Type
                        </label>

                        <select
                            v-model="form.document_type"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="">
                                Select document type
                            </option>

                            <option
                                v-for="type in documentTypes"
                                :key="type"
                                :value="type"
                            >
                                {{ type }}
                            </option>
                        </select>

                        <p
                            v-if="errors.document_type"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ errors.document_type }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Document Name
                        </label>

                        <input
                            v-model="form.document_name"
                            type="text"
                            placeholder="Example: Driver's License"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        />

                        <p
                            v-if="errors.document_name"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ errors.document_name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            File
                        </label>

                        <input
                            ref="fileInput"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            @change="handleFileChange"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                        />

                        <p class="mt-1 text-xs text-slate-500">
                            Accepted files: PDF, JPG, JPEG, PNG, DOC, and DOCX.
                            Maximum size: 10 MB.
                        </p>

                        <p
                            v-if="form.file"
                            class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600"
                        >
                            Selected:
                            <span class="font-semibold">
                                {{ form.file.name }}
                            </span>
                        </p>

                        <p
                            v-if="errors.file"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ errors.file }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Remarks
                        </label>

                        <textarea
                            v-model="form.remarks"
                            rows="4"
                            placeholder="Add optional notes about this document."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end"
            >
                <button
                    type="button"
                    @click="closeModal"
                    :disabled="saving"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    @click="submit"
                    :disabled="saving"
                    class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ saving ? "Uploading..." : "Upload Document" }}
                </button>
            </div>
        </div>
    </div>
</template>