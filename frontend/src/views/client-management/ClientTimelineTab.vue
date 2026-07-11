<script setup>
import { computed, ref } from "vue";

import {
    Activity,
    Banknote,
    FileUp,
    House,
    ReceiptText,
    UserCog,
    UserPlus,
} from "lucide-vue-next";

const props = defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
});

const selectedType = ref("");

const filters = [
    {
        value: "",
        label: "All Activities",
    },
    {
        value: "client_created",
        label: "Client Created",
    },
    {
        value: "client_updated",
        label: "Client Updated",
    },
    {
        value: "sale_created",
        label: "Sales",
    },
    {
        value: "downpayment_received",
        label: "Downpayments",
    },
    {
        value: "payment_received",
        label: "Installments",
    },
    {
        value: "document_uploaded",
        label: "Documents",
    },
];

const filteredActivities = computed(() => {
    if (!selectedType.value) {
        return props.activities;
    }

    return props.activities.filter(
        (activity) =>
            activity.type === selectedType.value
    );
});

const iconComponent = (activity) => {
    switch (activity?.type) {
        case "client_created":
            return UserPlus;

        case "client_updated":
            return UserCog;

        case "sale_created":
            return House;

        case "downpayment_received":
            return Banknote;

        case "payment_received":
            return ReceiptText;

        case "document_uploaded":
            return FileUp;

        default:
            return Activity;
    }
};

const iconClass = (activity) => {
    switch (activity?.color) {
        case "blue":
            return "bg-blue-100 text-blue-700";

        case "emerald":
            return "bg-emerald-100 text-emerald-700";

        case "green":
            return "bg-green-100 text-green-700";

        case "purple":
            return "bg-purple-100 text-purple-700";

        case "amber":
            return "bg-amber-100 text-amber-700";

        default:
            return "bg-slate-100 text-slate-700";
    }
};

const formatDate = (value) => {
    if (!value) return "—";

    const parsedDate = new Date(value);

    if (Number.isNaN(parsedDate.getTime())) {
        return "—";
    }

    return parsedDate.toLocaleString("en-PH", {
        year: "numeric",
        month: "long",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

const hasAmount = (activity) => {
    return Number(activity?.amount || 0) > 0;
};
</script>

<template>
    <div class="space-y-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h3 class="font-semibold text-slate-900">
                    Client Timeline
                </h3>

                <p class="text-sm text-slate-500">
                    Chronological record of the client's sales, payments,
                    documents, and profile updates.
                </p>
            </div>

            <select
                v-model="selectedType"
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
            >
                <option
                    v-for="filter in filters"
                    :key="filter.value"
                    :value="filter.value"
                >
                    {{ filter.label }}
                </option>
            </select>
        </div>

        <div
            v-if="filteredActivities.length > 0"
            class="relative"
        >
            <div
                class="absolute bottom-0 left-5 top-0 w-px bg-slate-200"
            ></div>

            <div class="space-y-6">
                <div
                    v-for="activity in filteredActivities"
                    :key="activity.id"
                    class="relative flex gap-4"
                >
                    <div
                        class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                        :class="iconClass(activity)"
                    >
                        <component
                            :is="iconComponent(activity)"
                            class="h-5 w-5"
                        />
                    </div>

                    <div
                        class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div>
                                <h4 class="font-semibold text-slate-900">
                                    {{ activity.title || "Activity" }}
                                </h4>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{
                                        activity.description ||
                                        "No additional information."
                                    }}
                                </p>
                            </div>

                            <span
                                v-if="hasAmount(activity)"
                                class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
                            >
                                {{ money(activity.amount) }}
                            </span>
                        </div>

                        <div
                            class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500"
                        >
                            <span>
                                {{ formatDate(activity.date) }}
                            </span>

                            <span
                                v-if="activity.metadata?.sale_no"
                                class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-700"
                            >
                                Sale:
                                {{ activity.metadata.sale_no }}
                            </span>

                            <span
                                v-if="activity.metadata?.reference_no"
                                class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-700"
                            >
                                Ref:
                                {{ activity.metadata.reference_no }}
                            </span>

                            <span
                                v-if="activity.metadata?.document_type"
                                class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-700"
                            >
                                {{ activity.metadata.document_type }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else
            class="rounded-xl border border-dashed border-slate-300 px-6 py-14 text-center"
        >
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100"
            >
                <Activity class="h-7 w-7 text-slate-500" />
            </div>

            <h4 class="mt-4 font-semibold text-slate-900">
                No timeline activities
            </h4>

            <p class="mt-2 text-sm text-slate-500">
                No activities match the selected timeline filter.
            </p>
        </div>
    </div>
</template>