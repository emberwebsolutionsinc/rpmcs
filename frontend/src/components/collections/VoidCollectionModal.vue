<script setup>
import { reactive, watch } from "vue";
import { AlertTriangle, X } from "lucide-vue-next";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    collection: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "submit"]);

const form = reactive({
    void_reason: "",
});

watch(
    () => props.show,
    () => {
        if (props.show) {
            form.void_reason = "";
        }
    },
    { immediate: true }
);

const submit = () => {
    emit("submit", {
        void_reason: form.void_reason,
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/60 p-4"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-red-100 p-2 text-red-700">
                        <AlertTriangle class="h-5 w-5" />
                    </div>

                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Void Collection
                        </h2>

                        <p class="text-sm text-slate-500">
                            This will reverse the payment transaction.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="space-y-5 p-6">
                <div class="rounded-xl border border-red-100 bg-red-50 p-4">
                    <p class="text-xs font-semibold uppercase text-red-700">
                        Collection to Void
                    </p>

                    <p class="mt-1 font-bold text-red-950">
                        {{ collection?.collection_no || "—" }}
                    </p>

                    <p class="mt-1 text-sm text-red-700">
                        OR No: {{ collection?.official_receipt_no || "—" }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Reason for Voiding
                    </label>

                    <textarea
                        v-model="form.void_reason"
                        rows="4"
                        placeholder="Example: Duplicate payment entry, wrong OR number, encoding error..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                    ></textarea>

                    <p class="mt-1 text-xs text-slate-500">
                        Required. Minimum of 5 characters.
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4">
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
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    Void Collection
                </button>
            </div>
        </div>
    </div>
</template>