<script setup>
import { reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    sale: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "submit"]);

const form = reactive({
    sale_id: null,
    months: 12,
    start_date: "",
    overwrite: false,
});

watch(
    () => props.sale,
    (sale) => {
        form.sale_id = sale?.id ?? null;
        form.months = 12;
        form.start_date = new Date().toISOString().slice(0, 10);
        form.overwrite = false;
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
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
            <h2 class="text-xl font-bold text-slate-900">
                Generate Payment Schedule
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Create installment schedule for {{ sale?.sale_no || "this sale" }}.
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Number of Months
                    </label>

                    <input
                        v-model="form.months"
                        type="number"
                        min="1"
                        max="360"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Start Date
                    </label>

                    <input
                        v-model="form.start_date"
                        type="date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input
                        v-model="form.overwrite"
                        type="checkbox"
                        class="rounded border-slate-300 text-emerald-600"
                    />

                    Overwrite existing schedule
                </label>
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
                    Generate
                </button>
            </div>
        </div>
    </div>
</template>