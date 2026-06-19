<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    lot: {
        type: Object,
        default: null,
    },

    phases: {
        type: Array,
        default: () => [],
    },

    blocks: {
        type: Array,
        default: () => [],
    },

    propertyTypes: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "submit"]);

const form = reactive({
    property_project_id: null,
    project_phase_id: null,
    project_block_id: null,
    property_type_id: null,

    lot_code: "",
    lot_no: "",
    title_no: "",

    lot_area: "",
    price_per_sqm: "",
    selling_price: "",

    corner_lot: false,
    road_lot: false,

    status: "available",
    remarks: "",
});

const filteredBlocks = computed(() => {
    if (!form.project_phase_id) {
        return props.blocks;
    }

    return props.blocks.filter(
        (block) => block.project_phase_id === form.project_phase_id
    );
});

watch(
    () => props.lot,
    (lot) => {
        if (lot) {
            form.property_project_id = lot.property_project_id ?? null;
            form.project_phase_id = lot.project_phase_id ?? null;
            form.project_block_id = lot.project_block_id ?? null;
            form.property_type_id = lot.property_type_id ?? null;

            form.lot_code = lot.lot_code ?? "";
            form.lot_no = lot.lot_no ?? "";
            form.title_no = lot.title_no ?? "";

            form.lot_area = lot.lot_area ?? "";
            form.price_per_sqm = lot.price_per_sqm ?? "";
            form.selling_price = lot.selling_price ?? "";

            form.corner_lot = Boolean(lot.corner_lot);
            form.road_lot = Boolean(lot.road_lot);

            form.status = lot.status ?? "available";
            form.remarks = lot.remarks ?? "";
        } else {
            form.property_project_id = null;
            form.project_phase_id = null;
            form.project_block_id = null;
            form.property_type_id = null;

            form.lot_code = "";
            form.lot_no = "";
            form.title_no = "";

            form.lot_area = "";
            form.price_per_sqm = "";
            form.selling_price = "";

            form.corner_lot = false;
            form.road_lot = false;

            form.status = "available";
            form.remarks = "";
        }
    },
    { immediate: true }
);

watch(
    () => form.project_phase_id,
    () => {
        if (
            form.project_block_id &&
            !filteredBlocks.value.some((block) => block.id === form.project_block_id)
        ) {
            form.project_block_id = null;
        }
    }
);

watch(
    () => [form.lot_area, form.price_per_sqm],
    () => {
        const area = Number(form.lot_area);
        const price = Number(form.price_per_sqm);

        if (area > 0 && price > 0) {
            form.selling_price = area * price;
        }
    }
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
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5">
                <h2 class="text-xl font-bold text-slate-900">
                    {{ lot?.id ? "Edit Lot" : "Add Lot" }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Enter lot inventory, pricing, location, and classification details.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Phase
                    </label>

                    <select
                        v-model="form.project_phase_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option :value="null">
                            No Phase
                        </option>

                        <option
                            v-for="phase in phases"
                            :key="phase.id"
                            :value="phase.id"
                        >
                            {{ phase.phase_name }} — {{ phase.phase_code }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Block
                    </label>

                    <select
                        v-model="form.project_block_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option :value="null">
                            No Block
                        </option>

                        <option
                            v-for="block in filteredBlocks"
                            :key="block.id"
                            :value="block.id"
                        >
                            {{ block.block_no }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Property Type
                    </label>

                    <select
                        v-model="form.property_type_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option :value="null">
                            Select Property Type
                        </option>

                        <option
                            v-for="type in propertyTypes"
                            :key="type.id"
                            :value="type.id"
                        >
                            {{ type.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Status
                    </label>

                    <select
                        v-model="form.status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option value="available">Available</option>
                        <option value="reserved">Reserved</option>
                        <option value="sold">Sold</option>
                        <option value="fully_paid">Fully Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Lot Code
                    </label>

                    <input
                        v-model="form.lot_code"
                        type="text"
                        placeholder="Example: BPL-P1-B1-L1"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Lot Number
                    </label>

                    <input
                        v-model="form.lot_no"
                        type="text"
                        placeholder="Example: Lot 1"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Title Number
                    </label>

                    <input
                        v-model="form.title_no"
                        type="text"
                        placeholder="Optional title number"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Lot Area / Unit Area
                    </label>

                    <input
                        v-model="form.lot_area"
                        type="number"
                        step="0.01"
                        placeholder="Example: 120"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Price Per SQM
                    </label>

                    <input
                        v-model="form.price_per_sqm"
                        type="number"
                        step="0.01"
                        placeholder="Example: 4500"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Selling Price
                    </label>

                    <input
                        v-model="form.selling_price"
                        type="number"
                        step="0.01"
                        placeholder="Auto-computed from area x price per sqm"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div class="flex items-center gap-6 md:col-span-2">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input
                            v-model="form.corner_lot"
                            type="checkbox"
                            class="rounded border-slate-300 text-emerald-600"
                        />
                        Corner Lot
                    </label>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input
                            v-model="form.road_lot"
                            type="checkbox"
                            class="rounded border-slate-300 text-emerald-600"
                        />
                        Road Lot
                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Remarks
                    </label>

                    <textarea
                        v-model="form.remarks"
                        rows="3"
                        placeholder="Optional remarks"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    ></textarea>
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
                    Save Lot
                </button>
            </div>
        </div>
    </div>
</template>