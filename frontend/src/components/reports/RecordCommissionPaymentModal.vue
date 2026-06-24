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
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "submit"]);

const today = new Date().toISOString().slice(0, 10);

const form = reactive({
    sale_id: null,
    payment_date: today,
    amount: "",
    payment_method: "",
    reference_no: "",
    remarks: "",
});

const errors = reactive({
    amount: "",
    payment_method: "",
    reference_no: "",
});

watch(
    () => props.sale,
    (sale) => {
        form.sale_id = sale?.id ?? null;
        form.payment_date = today;
        form.amount = "";
        form.payment_method = "";
        form.reference_no = "";
        form.remarks = "";
    },
    { immediate: true }
);

const submit = () => {
    errors.amount = "";
    errors.payment_method = "";
    errors.reference_no = "";

    const amount = Number(form.amount || 0);
    const maxAmount = Number(props.sale?.commission_balance || 0);

    if (amount <= 0) {
        errors.amount = "Amount is required.";
        return;
    }

    if (amount > maxAmount) {
        errors.amount = `Amount must not exceed ₱${maxAmount.toLocaleString()}.`;
        return;
    }

    if (!form.payment_method) {
        errors.payment_method = "Payment method is required.";
        return;
    }

    if (!form.reference_no.trim()) {
        errors.reference_no = "Reference number is required.";
        return;
    }

    emit("submit", { ...form });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5">
                <h2 class="text-xl font-bold text-slate-900">
                    Record Commission Payment
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Record payment made to the agent for this sale.
                </p>
            </div>

            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs font-semibold uppercase text-emerald-700">
                    Selected Sale
                </p>

                <p class="mt-1 font-semibold text-emerald-950">
                    {{ sale?.sale_no || "No sale selected" }}
                </p>

                <p class="mt-1 text-sm text-emerald-800">
                    Agent:
                    {{ sale?.agent?.first_name }}
                    {{ sale?.agent?.last_name }}
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Payment Date
                    </label>

                    <input
                        v-model="form.payment_date"
                        type="date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Amount
                    </label>

                    <input
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="1"
                        :max="sale?.commission_balance || 0"
                        placeholder="Example: 5000"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p class="mt-1 text-xs text-slate-500">
                        Maximum allowed:
                        ₱{{ Number(sale?.commission_balance || 0).toLocaleString() }}
                    </p>

                    <p
                        v-if="errors.amount"
                        class="mt-1 text-xs font-medium text-red-600"
                    >
                        {{ errors.amount }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Payment Method
                    </label>

                    <select
                        v-model="form.payment_method"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    >
                        <option value="">Select method</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="gcash">GCash</option>
                        <option value="check">Check</option>
                    </select>
                    <p
                        v-if="errors.payment_method"
                        class="mt-1 text-xs font-medium text-red-600"
                    >
                        {{ errors.payment_method }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Reference No.
                    </label>

                    <input
                        v-model="form.reference_no"
                        type="text"
                        placeholder="OR, voucher, check, or transfer reference"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />

                    <p
                        v-if="errors.reference_no"
                        class="mt-1 text-xs font-medium text-red-600"
                    >
                        {{ errors.reference_no }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Remarks
                    </label>

                    <textarea
                        v-model="form.remarks"
                        rows="3"
                        placeholder="Optional remarks"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    ></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    :disabled="processing"
                    @click="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-60"
                >
                    {{ processing ? "Saving..." : "Save Payment" }}
                </button>
            </div>
        </div>
    </div>
</template>