<script setup>
import { reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    block: {
        type: Object,
        default: null,
    },

    phases: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "submit"]);

const form = reactive({
    property_project_id: null,
    project_phase_id: null,
    block_no: "",
    description: "",
    status: "active",
});

watch(
    () => props.block,
    (block) => {
        if (block) {
            form.property_project_id = block.property_project_id ?? null;
            form.project_phase_id = block.project_phase_id ?? null;
            form.block_no = block.block_no ?? "";
            form.description = block.description ?? "";
            form.status = block.status ?? "active";
        } else {
            form.property_project_id = null;
            form.project_phase_id = null;
            form.block_no = "";
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
                    {{ block?.id ? "Edit Block" : "Add Block" }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Assign a block to a project phase and enter its block details.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Phase
                    </label>

                    <select
                        v-model="form.project_phase_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option :value="null">
                            No Phase / General Block
                        </option>

                        <option
                            v-for="phase in phases"
                            :key="phase.id"
                            :value="phase.id"
                        >
                            {{ phase.phase_name }} — {{ phase.phase_code }}
                        </option>
                    </select>

                    <p class="mt-1 text-xs text-slate-400">
                        Use “No Phase” if the project does not use phases.
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Block Number
                    </label>

                    <input
                        v-model="form.block_no"
                        type="text"
                        placeholder="Example: Block 1, Block A, BLK-001"
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
                        placeholder="Optional block description"
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
                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
                            Inactive
                        </option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    @click="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                >
                    Save Block
                </button>
            </div>
        </div>
    </div>
</template>