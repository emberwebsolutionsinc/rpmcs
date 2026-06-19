<script setup>
import { reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    phase: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "submit"]);

const form = reactive({
    property_project_id: null,
    phase_code: "",
    phase_name: "",
    description: "",
    status: "active",
});

watch(
    () => props.phase,
    (phase) => {
        if (phase) {
            form.property_project_id = phase.property_project_id ?? null;
            form.phase_code = phase.phase_code ?? "";
            form.phase_name = phase.phase_name ?? "";
            form.description = phase.description ?? "";
            form.status = phase.status ?? "active";
        } else {
            form.property_project_id = null;
            form.phase_code = "";
            form.phase_name = "";
            form.description = "";
            form.status = "active";
        }
    },
    { immediate: true }
);

const submit = () => {
    emit("submit", { ...form });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5">
                <h2 class="text-xl font-bold text-slate-900">
                    {{ phase ? "Edit Phase" : "Add Phase" }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Enter the project phase information below.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Phase Code
                    </label>

                    <input
                        v-model="form.phase_code"
                        type="text"
                        placeholder="Example: PH-001"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Phase Name
                    </label>

                    <input
                        v-model="form.phase_name"
                        type="text"
                        placeholder="Example: Phase 1"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Description
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Optional phase description"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    ></textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Status
                    </label>

                    <select
                        v-model="form.status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    @click="emit('close')"
                    type="button"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    @click="submit"
                    type="button"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                >
                    Save Phase
                </button>
            </div>
        </div>
    </div>
</template>