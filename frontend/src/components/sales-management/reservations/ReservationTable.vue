<script setup>
import {
    Eye,
    Pencil,
    XCircle,
    CalendarX,
} from "lucide-vue-next";

defineProps({
    reservations: {
        type: Array,
        default: () => [],
    },

    loading: {
        type: Boolean,
        default: false,
    },
});

defineEmits([
    "view",
    "edit",
    "cancel",
    "convert",
]);

const getClientName = (reservation) => {
    const client = reservation.client;

    if (!client) return "—";

    return client.full_name
        || [client.first_name, client.middle_name, client.last_name].filter(Boolean).join(" ")
        || client.name
        || "—";
};

const getAgentName = (reservation) => {
    const agent = reservation.agent;

    if (!agent) return "—";

    return agent.full_name
        || [agent.first_name, agent.middle_name, agent.last_name].filter(Boolean).join(" ")
        || agent.name
        || "—";
};

const formatDate = (date) => {
    if (!date) return "—";

    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
};

const formatMoney = (amount) => {
    const value = Number(amount || 0);

    return value.toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });
};

const formatStatus = (status) => {
    const labels = {
        active: "Active",
        reserved: "Reserved",
        expired: "Expired",
        cancelled: "Cancelled",
        converted_to_sale: "Converted",
        converted: "Converted",
        sold: "Sold",
        fully_paid: "Fully Paid",
    };

    return labels[status] ?? status;
};

const statusClass = (status) => {
    switch (status) {
        case "active":
        case "reserved":
            return "bg-green-50 text-green-700";

        case "expired":
            return "bg-yellow-50 text-yellow-700";

        case "cancelled":
            return "bg-red-50 text-red-700";

        case "converted_to_sale":
        case "converted":
        case "sold":
            return "bg-blue-50 text-blue-700";

        case "fully_paid":
            return "bg-emerald-50 text-emerald-700";

        default:
            return "bg-gray-100 text-gray-700";
    }
};

const canEdit = (reservation) => {
    return ![
        "cancelled",
        "converted_to_sale",
    ].includes(reservation.status);
};

const canCancel = (reservation) => {
    return ![
        "cancelled",
        "converted_to_sale",
    ].includes(reservation.status);
};

const canConvert = (reservation) => {
    return reservation.status === "active";
};
</script>
<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Reservation No.
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Client
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Lot
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Agent
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Fee
                        </th>

                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Status
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr
                        v-for="reservation in reservations"
                        :key="reservation.id"
                        class="transition hover:bg-gray-50"
                    >
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="font-semibold text-gray-900">
                                {{ reservation.reservation_no }}
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ formatDate(reservation.reservation_date) }}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="font-medium text-gray-900">
                                {{ getClientName(reservation) }}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="font-medium text-gray-900">
                                {{ reservation.lot?.lot_code || reservation.lot?.lot_no || "—" }}
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ reservation.lot?.title_no || "No title no." }}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                            {{ getAgentName(reservation) }}
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-gray-900">
                            {{ formatMoney(reservation.reservation_fee) }}
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-center">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="statusClass(reservation.status || 'active')"
                            >
                                {{ formatStatus(reservation.status || "active") }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                    title="View"
                                    @click="$emit('view', reservation)"
                                >
                                    <Eye class="h-4 w-4" />
                                    View
                                </button>

                                <button
                                    v-if="canEdit(reservation)"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                    title="Edit"
                                    @click="$emit('edit', reservation)"
                                >
                                    <Pencil class="h-4 w-4" />
                                    Edit
                                </button>

                                <button
                                    v-if="canCancel(reservation)"
                                    type="button"
                                    class="rounded-lg border border-red-200 p-2 text-red-500 transition hover:bg-red-50 hover:text-red-700"
                                    title="Cancel Reservation"
                                    @click="$emit('cancel', reservation)"
                                >
                                    <XCircle class="h-4 w-4" />
                                </button>

                                <button
                                    v-if="canConvert(reservation)"
                                    type="button"
                                    @click="$emit('convert', reservation)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                >
                                    Convert To Sale
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!loading && reservations.length === 0">
                        <td colspan="7" class="px-5 py-12 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center">
                                <CalendarX class="h-10 w-10 text-gray-300" />

                                <h3 class="mt-3 text-sm font-semibold text-gray-900">
                                    No reservations found
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Reservation records will appear here once created.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="loading">
                        <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-500">
                            Loading reservations...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

