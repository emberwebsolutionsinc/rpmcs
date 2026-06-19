<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    reservation: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "submit"]);

const today = new Date().toISOString().slice(0, 10);

const form = reactive({
    sale_no: "",
    reservation_id: null,
    client_id: null,
    lot_id: null,
    agent_id: null,
    sale_date: today,
    contract_price: "",
    downpayment: "",
    balance: "",
    status: "active",
    remarks: "",
});

const reservationLabel = computed(() => {
    if (!props.reservation) return "No reservation selected";

    return `${props.reservation.reservation_no} — ${props.reservation.lot?.lot_no ?? ""}`;
});

const generateSaleNo = () => {
    const timestamp = Date.now().toString().slice(-6);
    return `SALE-${timestamp}`;
};

watch(
    () => props.reservation,
    (reservation) => {
        form.sale_no = generateSaleNo();

        form.reservation_id = reservation?.id ?? null;
        form.client_id = reservation?.client_id ?? null;
        form.lot_id = reservation?.lot_id ?? null;
        form.agent_id = reservation?.agent_id ?? null;

        form.sale_date = today;

        form.contract_price = reservation?.lot?.selling_price ?? "";
        form.downpayment = reservation?.reservation_fee ?? "";
        form.balance =
            Number(reservation?.lot?.selling_price ?? 0) -
            Number(reservation?.reservation_fee ?? 0);

        form.status = "active";
        form.remarks = "Converted from reservation.";
    },
    { immediate: true }
);

watch(
    () => [form.contract_price, form.downpayment],
    () => {
        const contractPrice = Number(form.contract_price || 0);
        const downpayment = Number(form.downpayment || 0);

        form.balance = contractPrice - downpayment;
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
        <div class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5">
                <h2 class="text-xl font-bold text-slate-900">
                    Convert Reservation To Sale
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Create a sales record from an active reservation.
                </p>
            </div>

            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-wide text-emerald-700">
                    Selected Reservation
                </p>

                <p class="mt-1 font-semibold text-emerald-900">
                    {{ reservationLabel }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Sale No.
                    </label>

                    <input
                        v-model="form.sale_no"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Sale Date
                    </label>

                    <input
                        v-model="form.sale_date"
                        type="date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Contract Price
                    </label>

                    <input
                        v-model="form.contract_price"
                        type="number"
                        step="0.01"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Downpayment
                    </label>

                    <input
                        v-model="form.downpayment"
                        type="number"
                        step="0.01"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Balance
                    </label>

                    <input
                        v-model="form.balance"
                        type="number"
                        step="0.01"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
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
                        <option value="fully_paid">Fully Paid</option>
                    </select>
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
                    Convert To Sale
                </button>
            </div>
        </div>
    </div>
</template>