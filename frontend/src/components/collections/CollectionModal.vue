<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    sale: {
        type: Object,
        default: null,
    },

    schedule: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits([
    "close",
    "submit",
]);

const form = reactive({
    sale_id: null,
    payment_schedule_id: null,
    official_receipt_no: "",
    payment_date: new Date().toISOString().slice(0, 10),
    amount_paid: "",
    payment_method: "cash",
    reference_no: "",
    remarks: "",
});

const scheduleBalance = computed(() => {
    return Number(props.schedule?.balance || 0);
});

const saleBalance = computed(() => {
    return Number(props.sale?.balance || 0);
});

const maxPayment = computed(() => {
    return props.schedule ? scheduleBalance.value : saleBalance.value;
});

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });
};

watch(
    () => [
        props.sale?.id,
        props.schedule?.id,
        props.show,
    ],
    () => {
        if (!props.show) return;

        form.sale_id = props.sale?.id ?? null;
        form.payment_schedule_id = props.schedule?.id ?? null;

        // Leave blank. Backend will auto-generate a unique OR number.
        form.official_receipt_no = "";

        form.payment_date = new Date().toISOString().slice(0, 10);
        form.amount_paid = maxPayment.value || "";
        form.payment_method = "cash";
        form.reference_no = "";

        form.remarks = props.schedule
            ? `Payment for installment #${props.schedule.installment_no}`
            : "Direct payment to sale balance.";
    },
    {
        immediate: true,
    }
);

const submit = () => {
    const amount = Number(form.amount_paid || 0);

    if (amount <= 0) {
        alert("Amount paid must be greater than zero.");
        return;
    }

    if (amount > maxPayment.value) {
        alert("Amount paid cannot exceed the maximum allowed payment.");
        return;
    }

    emit("submit", {
        ...form,
        amount_paid: amount,
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 p-4"
    >
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
            <div>
                <h2 class="text-xl font-bold text-slate-900">
                    Record Payment
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Post a collection payment and update the installment balance.
                </p>
            </div>

            <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                <p class="text-xs font-semibold uppercase text-emerald-700">
                    Maximum Allowed Payment
                </p>

                <p class="mt-1 text-lg font-bold text-emerald-900">
                    {{ formatMoney(maxPayment) }}
                </p>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Official Receipt No.
                    </label>

                    <input
                        v-model="form.official_receipt_no"
                        type="text"
                        placeholder="Leave blank to auto-generate"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />

                    <p class="mt-1 text-xs text-slate-500">
                        Leave this blank if you want RPMCS to generate the OR number.
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Payment Date
                    </label>

                    <input
                        v-model="form.payment_date"
                        type="date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Amount Paid
                    </label>

                    <input
                        v-model="form.amount_paid"
                        type="number"
                        step="0.01"
                        min="0"
                        :max="maxPayment"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />

                    <p class="mt-1 text-xs text-slate-500">
                        Maximum: {{ formatMoney(maxPayment) }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Payment Method
                    </label>

                    <select
                        v-model="form.payment_method"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="gcash">GCash</option>
                        <option value="maya">Maya</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Reference No.
                    </label>

                    <input
                        v-model="form.reference_no"
                        type="text"
                        placeholder="Check no., transfer ref no., or transaction id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Remarks
                    </label>

                    <textarea
                        v-model="form.remarks"
                        rows="3"
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
                    Save Payment
                </button>
            </div>
        </div>
    </div>
</template>