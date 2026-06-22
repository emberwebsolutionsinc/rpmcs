<script setup>
import {
    X,
    Receipt,
    User,
    Building2,
    Wallet,
    CalendarDays,
    FileText,
    Printer,
    RotateCcw,
} from "lucide-vue-next";

import StatusBadge from "@/components/common/StatusBadge.vue";

defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    collection: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits([
    "close",
    "print-or",
    "void",
]);

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });
};

const formatDate = (date) => {
    if (!date) return "—";

    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
};

const fullName = (person) => {
    if (!person) return "—";

    return (
        person.full_name ||
        [person.first_name, person.middle_name, person.last_name]
            .filter(Boolean)
            .join(" ") ||
        "—"
    );
};

const formatMethod = (method) => {
    if (!method) return "—";

    return method.replace("_", " ");
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300"
        leave-active-class="transition duration-300"
        enter-from-class="translate-x-full"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="show"
            class="fixed inset-y-0 right-0 z-[9999] flex w-full max-w-3xl flex-col border-l border-slate-200 bg-slate-50 shadow-2xl"
        >
            <div class="shrink-0 border-b border-slate-200 bg-white">
                <div class="bg-gradient-to-r from-emerald-700 to-emerald-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-100">
                                Collection Details
                            </p>

                            <h2 class="mt-1 text-2xl font-bold">
                                {{ collection?.collection_no || "—" }}
                            </h2>

                            <p class="mt-1 text-sm text-emerald-100">
                                OR No: {{ collection?.official_receipt_no || "—" }}
                            </p>
                        </div>

                        <button
                            @click="emit('close')"
                            class="rounded-xl bg-white/10 p-2 text-white hover:bg-white/20"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 space-y-6 overflow-y-auto p-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <Receipt class="h-4 w-4" />
                        Collection Information
                    </h3>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase text-slate-400">Collection No.</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.collection_no || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Official Receipt No.</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.official_receipt_no || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Payment Date</p>
                            <p class="font-medium text-slate-900">
                                {{ formatDate(collection?.payment_date) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Status</p>
                            <StatusBadge :value="collection?.status" />
                        </div>
                    </div>
                </section>
              <div
                    v-if="collection?.status === 'voided'"
                    class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4"
                >
                    <h3 class="text-sm font-semibold text-red-700">
                        Void Information
                    </h3>

                    <div class="mt-3 space-y-3">
                        <div>
                            <p class="text-xs text-red-600">
                                Voided By
                            </p>

                            <p class="font-medium text-slate-900">
                                {{ collection.voided_by?.name || 'System' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-red-600">
                                Voided At
                            </p>

                            <p class="font-medium text-slate-900">
                                {{ collection.voided_at || '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-red-600">
                                Reason for Voiding
                            </p>

                            <p class="font-medium text-slate-900 whitespace-pre-wrap">
                                {{ collection.void_reason || '-' }}
                            </p>
                        </div>
                    </div>
</div>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <Wallet class="h-4 w-4" />
                        Payment Information
                    </h3>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase text-emerald-700">
                                Amount Paid
                            </p>
                            <p class="mt-1 text-lg font-bold text-emerald-900">
                                {{ formatMoney(collection?.amount_paid) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <p class="text-xs font-semibold uppercase text-blue-700">
                                Method
                            </p>
                            <p class="mt-1 text-lg font-bold capitalize text-blue-900">
                                {{ formatMethod(collection?.payment_method) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-purple-100 bg-purple-50 p-4">
                            <p class="text-xs font-semibold uppercase text-purple-700">
                                Reference
                            </p>
                            <p class="mt-1 text-lg font-bold text-purple-900">
                                {{ collection?.reference_no || "—" }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <User class="h-4 w-4" />
                        Client Information
                    </h3>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase text-slate-400">Client Name</p>
                            <p class="font-medium text-slate-900">
                                {{ fullName(collection?.sale?.client) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Client Code</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.sale?.client?.client_code || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Contact Number</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.sale?.client?.contact_number || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Email</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.sale?.client?.email || "—" }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <Building2 class="h-4 w-4" />
                        Property / Sale Information
                    </h3>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase text-slate-400">Sale No.</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.sale?.sale_no || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Project</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.sale?.lot?.project?.project_name || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Lot</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.sale?.lot?.lot_no || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">Installment No.</p>
                            <p class="font-medium text-slate-900">
                                {{ collection?.payment_schedule?.installment_no || "—" }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <FileText class="h-4 w-4" />
                        Remarks
                    </h3>

                    <p class="text-sm text-slate-600">
                        {{ collection?.remarks || "No remarks." }}
                    </p>
                </section>
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-white px-6 py-4">
                <div class="flex justify-end gap-3">
                    <button
                        v-if="collection?.status === 'posted'"
                        @click="emit('print-or', collection)"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        <Printer class="h-4 w-4" />
                        Print OR
                    </button>

                    <button
                        v-if="collection?.status === 'posted'"
                        @click="emit('void', collection)"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        <RotateCcw class="h-4 w-4" />
                        Void
                    </button>

                    <button
                        @click="emit('close')"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>