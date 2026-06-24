<script setup>
import { reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    payment: {
        type: Object,
        default: null,
    },

    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "close",
    "submit",
]);

const form = reactive({
    delete_reason: "",
});

watch(
    () => props.show,
    (value) => {
        if (value) {
            form.delete_reason = "";
        }
    }
);

const submit = () => {
    emit("submit", form.delete_reason);
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="w-full max-w-lg rounded-2xl bg-white shadow-2xl"
        >
            <div
                class="border-b px-6 py-4"
            >
                <h2
                    class="text-lg font-bold text-red-700"
                >
                    Delete Commission Payment
                </h2>

                <p
                    class="mt-1 text-sm text-slate-500"
                >
                    This action will remove the payment
                    from commission computations.
                </p>
            </div>

            <div
                class="space-y-4 p-6"
            >
                <div
                    class="rounded-xl border border-red-200 bg-red-50 p-4"
                >
                    <p
                        class="font-semibold text-red-800"
                    >
                        Payment Amount
                    </p>

                    <p
                        class="mt-1 text-lg font-bold text-red-900"
                    >
                        ₱{{ Number(payment?.amount || 0).toLocaleString() }}
                    </p>

                    <p
                        class="mt-2 text-sm text-red-700"
                    >
                        Sale:
                        {{ payment?.sale?.sale_no || "—" }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Reason for Deletion
                    </label>

                    <textarea
                        v-model="form.delete_reason"
                        rows="4"
                        placeholder="Example: Wrong amount encoded. Duplicate commission entry."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2"
                    ></textarea>

                    <p
                        class="mt-1 text-xs text-slate-500"
                    >
                        Minimum 5 characters required.
                    </p>
                </div>
            </div>

            <div
                class="flex justify-end gap-3 border-t px-6 py-4"
            >
                <button
                    @click="emit('close')"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm"
                >
                    Cancel
                </button>

                <button
                    :disabled="
                        processing ||
                        form.delete_reason.trim().length < 5
                    "
                    @click="submit"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                >
                    {{
                        processing
                            ? "Deleting..."
                            : "Delete Payment"
                    }}
                </button>
            </div>
        </div>
    </div>
</template>