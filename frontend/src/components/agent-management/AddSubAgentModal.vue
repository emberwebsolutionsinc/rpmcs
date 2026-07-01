<script setup>
import { reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    parentAgent: {
        type: Object,
        default: null,
    },
    saving: {
    type: Boolean,
    default: false,
},
});

const emit = defineEmits(["close", "submit"]);

const form = reactive({
    parent_agent_id: "",
    agent_type: "sub_agent",
    agent_code: "",
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    contact_number: "",
    email: "",
    address: "",
    license_number: "",
    default_commission_rate: "",
    status: "active",
});

const errors = reactive({});

const resetForm = () => {
    form.parent_agent_id = props.parentAgent?.id || "";
    form.agent_type = "sub_agent";
    form.agent_code = "";
    form.first_name = "";
    form.middle_name = "";
    form.last_name = "";
    form.suffix = "";
    form.contact_number = "";
    form.email = "";
    form.address = "";
    form.license_number = "";
    form.default_commission_rate = "";
    form.status = "active";

    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
};

watch(
    () => props.show,
    (value) => {
        if (value) {
            resetForm();
        }
    }
);

const validate = () => {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });

    if (!form.agent_code.trim()) {
        errors.agent_code = "Agent code is required.";
    }

    if (!form.first_name.trim()) {
        errors.first_name = "First name is required.";
    }

    if (!form.last_name.trim()) {
        errors.last_name = "Last name is required.";
    }

    if (!form.default_commission_rate && form.default_commission_rate !== 0) {
        errors.default_commission_rate = "Commission rate is required.";
    }

    if (Number(form.default_commission_rate) < 0) {
        errors.default_commission_rate = "Commission rate cannot be negative.";
    }

    if (Number(form.default_commission_rate) > 100) {
        errors.default_commission_rate = "Commission rate cannot exceed 100%.";
    }

    return Object.keys(errors).length === 0;
};

const submit = () => {
    if (!validate()) {
        return;
    }

    emit("submit", {
        parent_agent_id: props.parentAgent?.id,
        agent_type: "sub_agent",
        agent_code: form.agent_code,
        first_name: form.first_name,
        middle_name: form.middle_name,
        last_name: form.last_name,
        suffix: form.suffix,
        contact_number: form.contact_number,
        email: form.email,
        address: form.address,
        license_number: form.license_number,
        default_commission_rate: Number(form.default_commission_rate),
        status: form.status,
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
        <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl">
            <div class="border-b px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">
                    Add Sub-Agent
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    This sub-agent will be assigned under
                    <span class="font-semibold">
                        {{ parentAgent?.first_name }}
                        {{ parentAgent?.last_name }}
                    </span>.
                </p>
            </div>

            <div class="max-h-[75vh] overflow-y-auto px-6 py-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Agent Code
                        </label>

                        <input
                            v-model="form.agent_code"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                            placeholder="Example: SA-001"
                        />

                        <p
                            v-if="errors.agent_code"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ errors.agent_code }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Commission Rate (%)
                        </label>

                        <input
                            v-model="form.default_commission_rate"
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                            placeholder="Example: 3"
                        />

                        <p
                            v-if="errors.default_commission_rate"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ errors.default_commission_rate }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            First Name
                        </label>

                        <input
                            v-model="form.first_name"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />

                        <p
                            v-if="errors.first_name"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ errors.first_name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Middle Name
                        </label>

                        <input
                            v-model="form.middle_name"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Last Name
                        </label>

                        <input
                            v-model="form.last_name"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />

                        <p
                            v-if="errors.last_name"
                            class="mt-1 text-xs font-medium text-red-600"
                        >
                            {{ errors.last_name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Suffix
                        </label>

                        <input
                            v-model="form.suffix"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                            placeholder="Jr., Sr., III"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Contact Number
                        </label>

                        <input
                            v-model="form.contact_number"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Email
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            License Number
                        </label>

                        <input
                            v-model="form.license_number"
                            type="text"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Status
                        </label>

                        <select
                            v-model="form.status"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Address
                        </label>

                        <textarea
                            v-model="form.address"
                            rows="3"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t px-6 py-4">
                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50"
                >
                    Cancel
                </button>

               <button
                    type="button"
                    @click="submit"
                    :disabled="saving"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ saving ? "Saving..." : "Save Sub-Agent" }}
                </button>
            </div>
        </div>
    </div>
</template>