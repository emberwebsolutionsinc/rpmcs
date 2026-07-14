<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({}),
    },

    saving: {
        type: Boolean,
        default: false,
    },

    mode: {
        type: String,
        default: "create",
        validator: (value) =>
            ["create", "edit"].includes(value),
    },

    serverErrors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits([
    "submit",
    "cancel",
]);

const form = reactive({
    client_code: "",
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",

    birthdate: "",
    gender: "",
    civil_status: "",
    nationality: "Filipino",

    contact_number: "",
    email: "",
    address: "",

    occupation: "",
    employer: "",
    tin: "",

    status: "active",
});

const localErrors = reactive({});

const title = computed(() => {
    return props.mode === "edit"
        ? "Edit Client"
        : "Add Client";
});

const submitLabel = computed(() => {
    if (props.saving) {
        return props.mode === "edit"
            ? "Saving Changes..."
            : "Creating Client...";
    }

    return props.mode === "edit"
        ? "Save Changes"
        : "Create Client";
});

const civilStatuses = [
    "single",
    "married",
    "widowed",
    "separated",
    "divorced",
];

const genders = [
    "male",
    "female",
    "other",
];

const statuses = [
    "active",
    "inactive",
];

const resetLocalErrors = () => {
    Object.keys(localErrors).forEach((key) => {
        delete localErrors[key];
    });
};

const populateForm = (value) => {
    form.client_code =
        value?.client_code ?? "";

    form.first_name =
        value?.first_name ?? "";

    form.middle_name =
        value?.middle_name ?? "";

    form.last_name =
        value?.last_name ?? "";

    form.suffix =
        value?.suffix ?? "";

    form.birthdate =
        value?.birthdate
            ? String(value.birthdate).substring(0, 10)
            : "";

    form.gender =
        value?.gender ?? "";

    form.civil_status =
        value?.civil_status ?? "";

    form.nationality =
        value?.nationality ?? "Filipino";

    form.contact_number =
        value?.contact_number ?? "";

    form.email =
        value?.email ?? "";

    form.address =
        value?.address ?? "";

    form.occupation =
        value?.occupation ?? "";

    form.employer =
        value?.employer ?? "";

    form.tin =
        value?.tin ?? "";

    form.status =
        value?.status ?? "active";

    resetLocalErrors();
};

watch(
    () => props.modelValue,
    (value) => {
        populateForm(value);
    },
    {
        immediate: true,
        deep: true,
    }
);

const errorMessage = (field) => {
    return (
        props.serverErrors?.[field]?.[0] ??
        props.serverErrors?.[field] ??
        localErrors[field] ??
        ""
    );
};

const validate = () => {
    resetLocalErrors();

    if (!form.first_name.trim()) {
        localErrors.first_name =
            "First name is required.";
    }

    if (!form.last_name.trim()) {
        localErrors.last_name =
            "Last name is required.";
    }

    if (
        form.email &&
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
            form.email
        )
    ) {
        localErrors.email =
            "Enter a valid email address.";
    }

    if (
        form.contact_number &&
        form.contact_number.length < 7
    ) {
        localErrors.contact_number =
            "Enter a valid contact number.";
    }

    if (!form.status) {
        localErrors.status =
            "Status is required.";
    }

    return Object.keys(localErrors).length === 0;
};

const clean = (value) => {
    if (typeof value !== "string") {
        return value;
    }

    const trimmed = value.trim();

    return trimmed === ""
        ? null
        : trimmed;
};

const submitForm = () => {
    if (!validate()) {
        return;
    }

    emit("submit", {
        client_code: clean(form.client_code),

        first_name: clean(form.first_name),
        middle_name: clean(form.middle_name),
        last_name: clean(form.last_name),
        suffix: clean(form.suffix),

        birthdate: clean(form.birthdate),
        gender: clean(form.gender),
        civil_status: clean(form.civil_status),
        nationality: clean(form.nationality),

        contact_number: clean(form.contact_number),
        email: clean(form.email),
        address: clean(form.address),

        occupation: clean(form.occupation),
        employer: clean(form.employer),
        tin: clean(form.tin),

        status: form.status,
    });
};

const cancel = () => {
    if (props.saving) return;

    emit("cancel");
};
</script>

<template>
    <form
        class="space-y-6"
        @submit.prevent="submitForm"
    >
        <div
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    {{ title }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Enter the client’s personal, contact, and employment information.
                </p>
            </div>
        </div>

        <div
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <h3 class="font-semibold text-slate-900">
                Personal Information
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Client Code
                    </label>

                    <input
                        v-model="form.client_code"
                        type="text"
                        placeholder="Automatically generated if blank"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('client_code')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("client_code") }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        First Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        v-model="form.first_name"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('first_name')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("first_name") }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Middle Name
                    </label>

                    <input
                        v-model="form.middle_name"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('middle_name')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("middle_name") }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Last Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        v-model="form.last_name"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('last_name')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("last_name") }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Suffix
                    </label>

                    <input
                        v-model="form.suffix"
                        type="text"
                        placeholder="Jr., Sr., III"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Birthdate
                    </label>

                    <input
                        v-model="form.birthdate"
                        type="date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('birthdate')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("birthdate") }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Gender
                    </label>

                    <select
                        v-model="form.gender"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm capitalize"
                    >
                        <option value="">
                            Select gender
                        </option>

                        <option
                            v-for="gender in genders"
                            :key="gender"
                            :value="gender"
                        >
                            {{ gender }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Civil Status
                    </label>

                    <select
                        v-model="form.civil_status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm capitalize"
                    >
                        <option value="">
                            Select civil status
                        </option>

                        <option
                            v-for="status in civilStatuses"
                            :key="status"
                            :value="status"
                        >
                            {{ status }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Nationality
                    </label>

                    <input
                        v-model="form.nationality"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        TIN
                    </label>

                    <input
                        v-model="form.tin"
                        type="text"
                        placeholder="000-000-000-000"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('tin')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("tin") }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Status
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        v-model="form.status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm capitalize"
                    >
                        <option
                            v-for="status in statuses"
                            :key="status"
                            :value="status"
                        >
                            {{ status }}
                        </option>
                    </select>

                    <p
                        v-if="errorMessage('status')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("status") }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <h3 class="font-semibold text-slate-900">
                Contact Information
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Contact Number
                    </label>

                    <input
                        v-model="form.contact_number"
                        type="text"
                        placeholder="09XXXXXXXXX"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('contact_number')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("contact_number") }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Email
                    </label>

                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="client@email.com"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('email')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("email") }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Address
                    </label>

                    <textarea
                        v-model="form.address"
                        rows="4"
                        placeholder="Complete residential address"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    ></textarea>

                    <p
                        v-if="errorMessage('address')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("address") }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <h3 class="font-semibold text-slate-900">
                Employment Information
            </h3>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Occupation
                    </label>

                    <input
                        v-model="form.occupation"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('occupation')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("occupation") }}
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">
                        Employer
                    </label>

                    <input
                        v-model="form.employer"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errorMessage('employer')"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errorMessage("employer") }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:justify-end"
        >
            <button
                type="button"
                :disabled="saving"
                @click="cancel"
                class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50"
            >
                Cancel
            </button>

            <button
                type="submit"
                :disabled="saving"
                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>