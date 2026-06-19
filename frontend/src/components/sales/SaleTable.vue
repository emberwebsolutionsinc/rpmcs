<script setup>
import StatusBadge from "@/components/common/StatusBadge.vue";

import {
    BadgeDollarSign,
    User,
    MapPinned,
    CalendarDays,
    XCircle,
    Eye,
    Building2,
    Wallet,
} from "lucide-vue-next";

defineProps({
    sales: {
        type: Array,
        default: () => [],
    },
});

defineEmits([
    "view",
    "cancel",
]);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(Number(amount || 0));
};

const formatDate = (date) => {
    if (!date) return "—";

    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const clientName = (sale) => {
    if (!sale.client) return "—";

    return `${sale.client.first_name ?? ""} ${sale.client.last_name ?? ""}`;
};
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Sale
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Client
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Project / Lot
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Contract Price
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Downpayment
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Balance
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr
                        v-for="sale in sales"
                        :key="sale.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                    <BadgeDollarSign class="h-5 w-5" />
                                </div>

                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ sale.sale_no }}
                                    </p>

                                    <p class="flex items-center gap-1 text-xs text-slate-500">
                                        <CalendarDays class="h-3.5 w-3.5" />
                                        {{ formatDate(sale.sale_date) }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-sm text-slate-700">
                                <User class="h-4 w-4 text-slate-400" />
                                {{ clientName(sale) }}
                            </div>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ sale.client?.client_code || "" }}
                            </p>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 text-sm font-medium text-slate-800">
                                <Building2 class="h-4 w-4 text-slate-400" />
                                {{ sale.lot?.project?.project_name || "—" }}
                            </div>

                            <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                <MapPinned class="h-3.5 w-3.5 text-slate-400" />
                                {{ sale.lot?.lot_no || "—" }}
                                <span v-if="sale.lot?.lot_code">
                                    — {{ sale.lot?.lot_code }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right text-sm font-semibold text-slate-900">
                            {{ formatCurrency(sale.contract_price) }}
                        </td>

                        <td class="px-6 py-4 text-right text-sm font-semibold text-slate-900">
                            <span class="inline-flex items-center gap-1">
                                <Wallet class="h-4 w-4 text-slate-400" />
                                {{ formatCurrency(sale.downpayment) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-right text-sm font-semibold text-slate-900">
                            {{ formatCurrency(sale.balance) }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            <StatusBadge :value="sale.status" />
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <button
                                    @click="$emit('view', sale)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                >
                                    <Eye class="h-4 w-4" />
                                    View
                                </button>

                                <button
                                    v-if="sale.status === 'active'"
                                    @click="$emit('cancel', sale)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100"
                                >
                                    <XCircle class="h-4 w-4" />
                                    Cancel
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>