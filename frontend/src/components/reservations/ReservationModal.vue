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

    clients: {
        type: Array,
        default: () => [],
    },

    agents: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "submit"]);

const today = new Date().toISOString().slice(0, 10);

const form = reactive({
    reservation_no: "",
    client_id: null,
    lot_id: null,
    agent_id: null,
    reservation_date: today,
    expiration_date: "",
    reservation_fee: "",
    status: "active",
    remarks: "",
});

const lotLabel = computed(() => {
    if (!props.lot) return "No lot selected";

    return `${props.lot.lot_no} — ${props.lot.lot_code}`;
});

const generateReservationNo = () => {
    const timestamp = Date.now().toString().slice(-6);
    return `RSV-${timestamp}`;
};

watch(
    () => props.lot,
    (lot) => {
        form.reservation_no = generateReservationNo();
        form.client_id = null;
        form.lot_id = lot?.id ?? null;
        form.agent_id = null;
        form.reservation_date = today;
        form.expiration_date = "";
        form.reservation_fee = "";
        form.status = "active";
        form.remarks = "";
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
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5">
                <h2 class="text-xl font-bold text-slate-900">
                    Reserve Lot
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Create a reservation record for the selected lot.
                </p>
            </div>

            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-wide text-emerald-700">
                    Selected Lot
                </p>

                <p class="mt-1 font-semibold text-emerald-900">
                    {{ lotLabel }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Reservation No.
                    </label>

                    <input
                        v-model="form.reservation_no"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Reservation Fee
                    </label>

                    <input
                        v-model="form.reservation_fee"
                        type="number"
                        step="0.01"
                        placeholder="Example: 5000"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Client
                    </label>

                    <select
                        v-model="form.client_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option :value="null">
                            Select Client
                        </option>

                        <option
                            v-for="client in clients"
                            :key="client.id"
                            :value="client.id"
                        >
                            {{ client.first_name }} {{ client.last_name }}
                            — {{ client.client_code }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Agent
                    </label>

                    <select
                        v-model="form.agent_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option :value="null">
                            No Agent
                        </option>

                        <option
                            v-for="agent in agents"
                            :key="agent.id"
                            :value="agent.id"
                        >
                            {{ agent.first_name }} {{ agent.last_name }}
                            — {{ agent.agent_code }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Reservation Date
                    </label>

                    <input
                        v-model="form.reservation_date"
                        type="date"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Expiration Date
                    </label>

                    <input
                        v-model="form.expiration_date"
                        type="date"
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
                    Save Reservation
                </button>
            </div>
        </div>
    </div>
</template>