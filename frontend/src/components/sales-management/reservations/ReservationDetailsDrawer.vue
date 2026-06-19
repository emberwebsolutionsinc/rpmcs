<script setup>
import {
    X,
    User,
    UserCog,
    Building2,
    MapPinned,
    CalendarDays,
    Wallet,
    FileText,
} from "lucide-vue-next";

defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    reservation: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close"]);

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
        person.name ||
        "—"
    );
};

const formatStatus = (status) => {
    const labels = {
        active: "Active",
        expired: "Expired",
        cancelled: "Cancelled",
        converted_to_sale: "Converted",
    };

    return labels[status] ?? status ?? "Active";
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
        class="fixed inset-y-0 right-0 z-[9999] w-full max-w-3xl overflow-y-auto border-l border-slate-200 bg-slate-50 shadow-2xl"
    >
        <!-- Header -->
        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white">
            <div class="bg-gradient-to-r from-blue-700 to-blue-600 px-6 py-5 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-100">
                            Reservation Details
                        </p>

                        <h2 class="mt-1 text-2xl font-bold">
                            {{ reservation?.reservation_no || "—" }}
                        </h2>

                        <p class="mt-1 text-sm text-blue-100">
                            {{ reservation?.lot?.project?.project_name || "No project" }}
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

            <div class="space-y-6 p-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <CalendarDays class="h-4 w-4" />
                        Reservation Information
                    </h3>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Reservation No.
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.reservation_no || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Status
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ formatStatus(reservation?.status) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Reservation Date
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ formatDate(reservation?.reservation_date) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Expiration Date
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ formatDate(reservation?.expiration_date) }}
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
                            <p class="text-xs uppercase text-slate-400">
                                Client Name
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ fullName(reservation?.client) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Client Code
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.client?.client_code || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Contact Number
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.client?.contact_number || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Email
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.client?.email || "—" }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <Building2 class="h-4 w-4" />
                        Property Information
                    </h3>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Project
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.lot?.project?.project_name || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Lot
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.lot?.lot_no || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Lot Code
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.lot?.lot_code || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Title No.
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.lot?.title_no || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Phase
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.lot?.phase?.phase_name || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Block
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.lot?.block?.block_no || "—" }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <UserCog class="h-4 w-4" />
                        Agent Information
                    </h3>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Agent
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ fullName(reservation?.agent) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase text-slate-400">
                                Agent Code
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ reservation?.agent?.agent_code || "—" }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <Wallet class="h-4 w-4" />
                        Financial Information
                    </h3>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                        <p class="text-xs font-semibold uppercase text-blue-700">
                            Reservation Fee
                        </p>

                        <p class="mt-1 text-xl font-bold text-blue-900">
                            {{ formatMoney(reservation?.reservation_fee) }}
                        </p>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 font-semibold text-slate-800">
                        <FileText class="h-4 w-4" />
                        Remarks
                    </h3>

                    <p class="text-sm text-slate-600">
                        {{ reservation?.remarks || "No remarks." }}
                    </p>
                </section>

                <div class="sticky bottom-0 border-t border-slate-200 bg-white px-6 py-4">
                    <div class="flex justify-end gap-3">
                        <button
                            @click="emit('close')"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Close
                        </button>

                        <button
                            v-if="reservation?.status === 'active'"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                        >
                            Convert To Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>