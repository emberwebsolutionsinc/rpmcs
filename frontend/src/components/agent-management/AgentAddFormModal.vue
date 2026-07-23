<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },

    loading: {
        type: Boolean,
        default: false,
    },

    errors: {
        type: Object,
        default: () => ({}),
    },

    agent: {
        type: Object,
        default: null,
    },
});

const isEditMode = computed(() => Boolean(props.agent?.id));

const populateForm = (agent) => {
    form.first_name = agent?.first_name ?? "";
    form.middle_name = agent?.middle_name ?? "";
    form.last_name = agent?.last_name ?? "";
    form.suffix = agent?.suffix ?? "";
    form.contact_number =
        agent?.contact_number ?? "";
    form.email = agent?.email ?? "";
    form.address = agent?.address ?? "";
    form.default_commission_rate =
        agent?.default_commission_rate ?? "";
    form.status = agent?.status ?? "active";
};

const emit = defineEmits([
    "close",
    "submit",
]);

const form = reactive({
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    contact_number: "",
    email: "",
    address: "",
    default_commission_rate: "",
    status: "active",
});

const resetForm = () => {
    form.first_name = "";
    form.middle_name = "";
    form.last_name = "";
    form.suffix = "";
    form.contact_number = "";
    form.email = "";
    form.address = "";
    form.default_commission_rate = "";
    form.status = "active";
};

const closeModal = () => {
    if (props.loading) {
        return;
    }

    emit("close");
};

const submitForm = () => {
    emit("submit", {
        first_name: form.first_name.trim(),

        middle_name:
            form.middle_name.trim() || null,

        last_name: form.last_name.trim(),

        suffix:
            form.suffix.trim() || null,

        contact_number:
            form.contact_number.trim() || null,

        email:
            form.email.trim() || null,

        address:
            form.address.trim() || null,

        default_commission_rate:
            form.default_commission_rate === ""
                ? 0
                : Number(
                      form.default_commission_rate
                  ),

        status: form.status,
    });
};

const getError = (field) => {
    const error = props.errors?.[field];

    if (Array.isArray(error)) {
        return error[0];
    }

    return error ?? "";
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.agent) {
                populateForm(props.agent);
            } else {
                resetForm();
            }

            return;
        }

        resetForm();
    }
);

watch(
    () => props.agent,
    (agent) => {
        if (props.open && agent) {
            populateForm(agent);
        }
    },
    {
        deep: true,
    }
);

</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
            @click.self="closeModal"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4"
                >
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{
                                isEditMode
                                    ? "Edit Agent"
                                    : "Add Main Agent"
                            }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{
                                isEditMode
                                    ? "Update the selected agent information."
                                    : "Enter the new Main Agent information."
                            }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
                        aria-label="Close modal"
                        :disabled="loading"
                        @click="closeModal"
                    >
                        ✕
                    </button>
                </div>

                <form
                    class="flex min-h-0 flex-1 flex-col"
                    @submit.prevent="submitForm"
                >
                    <div
                        class="flex-1 space-y-6 overflow-y-auto p-6"
                    >
                        <section>
                            <h3
                                class="mb-4 font-semibold text-slate-900"
                            >
                                Main Agent Information
                            </h3>

                            <div
                                class="grid gap-4 md:grid-cols-2"
                            >
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        First Name

                                        <span
                                            class="text-red-500"
                                        >
                                            *
                                        </span>
                                    </label>

                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        autocomplete="given-name"
                                        class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                        :class="
                                            getError(
                                                'first_name'
                                            )
                                                ? 'border-red-400'
                                                : 'border-slate-300'
                                        "
                                        :disabled="loading"
                                    />

                                    <p
                                        v-if="
                                            getError(
                                                'first_name'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "first_name"
                                            )
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Middle Name
                                    </label>

                                    <input
                                        v-model="form.middle_name"
                                        type="text"
                                        autocomplete="additional-name"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                        :disabled="loading"
                                    />

                                    <p
                                        v-if="
                                            getError(
                                                'middle_name'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "middle_name"
                                            )
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Last Name

                                        <span
                                            class="text-red-500"
                                        >
                                            *
                                        </span>
                                    </label>

                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        autocomplete="family-name"
                                        class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                        :class="
                                            getError(
                                                'last_name'
                                            )
                                                ? 'border-red-400'
                                                : 'border-slate-300'
                                        "
                                        :disabled="loading"
                                    />

                                    <p
                                        v-if="
                                            getError(
                                                'last_name'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "last_name"
                                            )
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Suffix
                                    </label>

                                    <input
                                        v-model="form.suffix"
                                        type="text"
                                        placeholder="Jr., Sr., III"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                        :disabled="loading"
                                    />

                                    <p
                                        v-if="
                                            getError(
                                                'suffix'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "suffix"
                                            )
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Commission Rate
                                    </label>

                                    <div class="relative">
                                        <input
                                            v-model="
                                                form.default_commission_rate
                                            "
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            class="w-full rounded-lg border px-3 py-2.5 pr-9 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                            :class="
                                                getError(
                                                    'default_commission_rate'
                                                )
                                                    ? 'border-red-400'
                                                    : 'border-slate-300'
                                            "
                                            :disabled="loading"
                                        />

                                        <span
                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-slate-500"
                                        >
                                            %
                                        </span>
                                    </div>

                                    <p
                                        v-if="
                                            getError(
                                                'default_commission_rate'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "default_commission_rate"
                                            )
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Status
                                    </label>

                                    <select
                                        v-model="form.status"
                                        class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                        :class="
                                            getError(
                                                'status'
                                            )
                                                ? 'border-red-400'
                                                : 'border-slate-300'
                                        "
                                        :disabled="loading"
                                    >
                                        <option
                                            value="active"
                                        >
                                            Active
                                        </option>

                                        <option
                                            value="inactive"
                                        >
                                            Inactive
                                        </option>
                                    </select>

                                    <p
                                        v-if="
                                            getError(
                                                'status'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "status"
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3
                                class="mb-4 font-semibold text-slate-900"
                            >
                                Contact Information
                            </h3>

                            <div
                                class="grid gap-4 md:grid-cols-2"
                            >
                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Contact Number
                                    </label>

                                    <input
                                        v-model="
                                            form.contact_number
                                        "
                                        type="text"
                                        inputmode="tel"
                                        autocomplete="tel"
                                        placeholder="09XXXXXXXXX"
                                        class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                        :class="
                                            getError(
                                                'contact_number'
                                            )
                                                ? 'border-red-400'
                                                : 'border-slate-300'
                                        "
                                        :disabled="loading"
                                    />

                                    <p
                                        v-if="
                                            getError(
                                                'contact_number'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "contact_number"
                                            )
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Email Address
                                    </label>

                                    <input
                                        v-model="form.email"
                                        type="email"
                                        autocomplete="email"
                                        placeholder="agent@example.com"
                                        class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                        :class="
                                            getError(
                                                'email'
                                            )
                                                ? 'border-red-400'
                                                : 'border-slate-300'
                                        "
                                        :disabled="loading"
                                    />

                                    <p
                                        v-if="
                                            getError(
                                                'email'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "email"
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="md:col-span-2"
                                >
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700"
                                    >
                                        Address
                                    </label>

                                    <textarea
                                        v-model="form.address"
                                        rows="3"
                                        class="w-full resize-y rounded-lg border px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500"
                                        :class="
                                            getError(
                                                'address'
                                            )
                                                ? 'border-red-400'
                                                : 'border-slate-300'
                                        "
                                        :disabled="loading"
                                    />

                                    <p
                                        v-if="
                                            getError(
                                                'address'
                                            )
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            getError(
                                                "address"
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4"
                    >
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="loading"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="loading"
                        >
                            {{
                                loading
                                    ? isEditMode
                                        ? "Updating..."
                                        : "Saving..."
                                    : isEditMode
                                    ? "Update Agent"
                                    : "Save Main Agent"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>