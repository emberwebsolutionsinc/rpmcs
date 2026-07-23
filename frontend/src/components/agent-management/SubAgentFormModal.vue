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

    mainAgents: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    "close",
    "submit",
]);

const form = reactive({
    agent_code: "",
    agent_type: "sub_agent",
    parent_agent_id: "",
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

const isEditMode = computed(() => {
    return Boolean(props.agent?.id);
});

const fieldError = (field) => {
    return props.errors?.[field]?.[0] ?? "";
};

const resetForm = () => {
    form.agent_code = "";
    form.agent_type = "sub_agent";
    form.parent_agent_id = "";
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

const populateForm = (agent) => {
    form.agent_code =
        agent?.agent_code ?? "";

    form.agent_type =
        agent?.agent_type ?? "sub_agent";

    form.parent_agent_id =
        agent?.parent_agent_id ??
        agent?.main_agent_id ??
        "";

    form.first_name =
        agent?.first_name ?? "";

    form.middle_name =
        agent?.middle_name ?? "";

    form.last_name =
        agent?.last_name ?? "";

    form.suffix =
        agent?.suffix ?? "";

    form.contact_number =
        agent?.contact_number ?? "";

    form.email =
        agent?.email ?? "";

    form.address =
        agent?.address ?? "";

    form.default_commission_rate =
        agent?.default_commission_rate ?? "";

    form.status =
        agent?.status ?? "active";
};

const closeModal = () => {
    if (props.loading) {
        return;
    }

    emit("close");
};

const submitForm = () => {
    emit("submit", {
        agent_code: form.agent_code,
        agent_type: "sub_agent",
        parent_agent_id:
            form.parent_agent_id,
        first_name: form.first_name,
        middle_name:
            form.middle_name || null,
        last_name: form.last_name,
        suffix: form.suffix || null,
        contact_number:
            form.contact_number || null,
        email: form.email || null,
        address: form.address || null,
        default_commission_rate:
            form.default_commission_rate,
        status: form.status,
    });
};

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            resetForm();
            return;
        }

        if (props.agent) {
            populateForm(props.agent);
            return;
        }

        resetForm();
    }
);

watch(
    () => props.agent,
    (agent) => {
        if (
            props.open &&
            agent
        ) {
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4 py-6"
            @click.self="closeModal"
        >
            <div
                class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-xl"
            >
                <div
                    class="flex items-start justify-between border-b border-slate-200 px-6 py-5"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-slate-900"
                        >
                            {{
                                isEditMode
                                    ? "Edit Sub-Agent"
                                    : "Add Sub-Agent"
                            }}
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{
                                isEditMode
                                    ? "Update the selected Sub-Agent information."
                                    : "Enter the new Sub-Agent information."
                            }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg px-3 py-2 text-sm text-slate-500 hover:bg-slate-100"
                        :disabled="loading"
                        @click="closeModal"
                    >
                        Close
                    </button>
                </div>

                <form
                    class="space-y-6 px-6 py-6"
                    @submit.prevent="submitForm"
                >
                    <div
                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Main Agent
                            </label>

                            <select
                                v-model="form.parent_agent_id"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            >
                                <option value="">
                                    Select Main Agent
                                </option>

                                <option
                                    v-for="mainAgent in mainAgents"
                                    :key="mainAgent.id"
                                    :value="mainAgent.id"
                                >
                                    {{
                                        mainAgent.full_name ??
                                        `${mainAgent.first_name ?? ""} ${mainAgent.last_name ?? ""}`.trim()
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="fieldError('parent_agent_id')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        "parent_agent_id"
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Agent Code
                            </label>

                            <input
                                v-model="form.agent_code"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                placeholder="Example: S01"
                            />

                            <p
                                v-if="fieldError('agent_code')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        "agent_code"
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                First Name
                            </label>

                            <input
                                v-model="form.first_name"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />

                            <p
                                v-if="fieldError('first_name')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        "first_name"
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Middle Name
                            </label>

                            <input
                                v-model="form.middle_name"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Last Name
                            </label>

                            <input
                                v-model="form.last_name"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />

                            <p
                                v-if="fieldError('last_name')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        "last_name"
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Suffix
                            </label>

                            <input
                                v-model="form.suffix"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Contact Number
                            </label>

                            <input
                                v-model="form.contact_number"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Email
                            </label>

                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />

                            <p
                                v-if="fieldError('email')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        "email"
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Commission Rate
                            </label>

                            <input
                                v-model="form.default_commission_rate"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />

                            <p
                                v-if="
                                    fieldError(
                                        'default_commission_rate'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        "default_commission_rate"
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Status
                            </label>

                            <select
                                v-model="form.status"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            >
                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
                                    Inactive
                                </option>
                            </select>

                            <p
                                v-if="fieldError('status')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        "status"
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Address
                        </label>

                        <textarea
                            v-model="form.address"
                            rows="3"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        ></textarea>

                        <p
                            v-if="fieldError('address')"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{
                                fieldError(
                                    "address"
                                )
                            }}
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 pt-5"
                    >
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            :disabled="loading"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="loading"
                        >
                            {{
                                loading
                                    ? "Updating..."
                                    : "Update Sub-Agent"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>