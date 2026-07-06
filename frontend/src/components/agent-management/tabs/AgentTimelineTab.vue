<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
});

const search = ref("");

const selectedType = ref("");

const expandedRows = ref([]);

const activityFilters = [
    {
        value: "",
        label: "All Activities",
    },

    {
        value: "document_uploaded",
        label: "Uploads",
    },

    {
        value: "document_downloaded",
        label: "Downloads",
    },

    {
        value: "document_previewed",
        label: "Preview",
    },

    {
        value: "document_deleted",
        label: "Deleted",
    },

    {
        value: "payment_created",
        label: "Payments",
    },

    {
        value: "commission_created",
        label: "Commissions",
    },
];

const filteredActivities = computed(() => {

    return props.activities.filter((activity) => {

        const keyword =
            search.value.toLowerCase();

        const matchesKeyword =
            !keyword ||

            activity.title
                ?.toLowerCase()
                .includes(keyword) ||

            activity.description
                ?.toLowerCase()
                .includes(keyword);

        const matchesType =
            !selectedType.value ||

            activity.activity_type ===
            selectedType.value;

        return (
            matchesKeyword &&
            matchesType
        );

    });

});

const groupedActivities = computed(() => {

    const groups = {};

    filteredActivities.value.forEach((activity) => {

        const activityDate =
            new Date(activity.created_at);

        const today =
            new Date();

        const yesterday =
            new Date();

        yesterday.setDate(
            yesterday.getDate() - 1
        );

        let groupName = "";

        if (
            activityDate.toDateString() ===
            today.toDateString()
        ) {

            groupName = "Today";

        }
        else if (
            activityDate.toDateString() ===
            yesterday.toDateString()
        ) {

            groupName = "Yesterday";

        }
        else {

            groupName =
                activityDate.toLocaleDateString(
                    "en-PH",
                    {
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                    }
                );

        }

        if (!groups[groupName]) {

            groups[groupName] = [];

        }

        groups[groupName].push(activity);

    });

    return groups;

});

const badgeClass = (type) => {

    switch (type) {

        case "document_uploaded":

            return "bg-green-100 text-green-700";

        case "document_downloaded":

            return "bg-blue-100 text-blue-700";

        case "document_previewed":

            return "bg-yellow-100 text-yellow-700";

        case "document_deleted":

            return "bg-red-100 text-red-700";

        case "payment_created":

            return "bg-indigo-100 text-indigo-700";

        case "commission_created":

            return "bg-purple-100 text-purple-700";

        default:

            return "bg-slate-100 text-slate-700";

    }

};

const activityIcon = (type) => {

    switch (type) {

        case "document_uploaded":

            return "📤";

        case "document_downloaded":

            return "📥";

        case "document_previewed":

            return "👁️";

        case "document_deleted":

            return "🗑️";

        case "payment_created":

            return "💰";

        case "commission_created":

            return "📈";

        default:

            return "📝";

    }

};

const relativeTime = (value) => {

    if (!value) return "";

    const now =
        new Date();

    const date =
        new Date(value);

    const seconds =
        Math.floor(
            (now - date) / 1000
        );

    if (seconds < 60)
        return "Just now";

    if (seconds < 3600)
        return `${Math.floor(seconds / 60)} minutes ago`;

    if (seconds < 86400)
        return `${Math.floor(seconds / 3600)} hours ago`;

    if (seconds < 604800)
        return `${Math.floor(seconds / 86400)} days ago`;

    return date.toLocaleDateString(
        "en-PH",
        {
            year: "numeric",
            month: "long",
            day: "numeric",
        }
    );

};

const isExpanded = (id) => {

    return expandedRows.value.includes(id);

};

const toggleDetails = (id) => {

    if (
        expandedRows.value.includes(id)
    ) {

        expandedRows.value =
            expandedRows.value.filter(
                item => item !== id
            );

        return;

    }

    expandedRows.value.push(id);

};
</script>
<template>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Activity Timeline
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Complete audit trail of activities performed on this agent.
                    </p>
                </div>

                <div class="flex flex-col gap-3 md:flex-row">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search activity..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none md:w-72"
                    />

                    <select
                        v-model="selectedType"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none md:w-56"
                    >
                        <option
                            v-for="item in activityFilters"
                            :key="item.value"
                            :value="item.value"
                        >
                            {{ item.label }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div
            v-if="filteredActivities.length === 0"
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-14 text-center shadow-sm"
        >
            <div class="text-7xl">
                📜
            </div>

            <h3 class="mt-6 text-xl font-semibold text-slate-900">
                No Activities Found
            </h3>

            <p class="mt-2 text-sm text-slate-500">
                No timeline records match your current search or filter.
            </p>
        </div>

        <div
            v-else
            class="space-y-10"
        >
        <!-- Timeline Groups -->

<div
    v-for="(activities, groupName) in groupedActivities"
    :key="groupName"
>

    <!-- Date Header -->

    <div class="mb-6">

        <div class="flex items-center gap-4">

            <div
                class="h-px flex-1 bg-slate-200"
            ></div>

            <span
                class="rounded-full bg-slate-100 px-4 py-1 text-sm font-bold uppercase tracking-wider text-slate-600"
            >
                {{ groupName }}
            </span>

            <div
                class="h-px flex-1 bg-slate-200"
            ></div>

        </div>

    </div>

    <!-- Timeline Container -->

    <div class="relative">

        <!-- Vertical Line -->

        <div
            class="absolute bottom-0 left-6 top-0 w-0.5 bg-slate-200"
        ></div>

        <!-- Timeline Item -->

        <div
            v-for="activity in activities"
            :key="activity.id"
            class="relative mb-8 flex gap-6"
        >

            <!-- Timeline Icon -->

            <div
                class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-4 border-white bg-emerald-600 text-xl shadow-lg"
            >
                {{ activityIcon(activity.activity_type) }}
            </div>

            <!-- Card -->

            <div
                class="flex-1 rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:shadow-lg"
            >

                <!-- Header -->

                <div
                    class="border-b border-slate-100 px-6 py-5"
                >

                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                    >

                        <div class="flex-1">

                            <div
                                class="flex flex-wrap items-center gap-3"
                            >

                                <h3
                                    class="text-lg font-bold text-slate-900"
                                >
                                    {{ activity.title }}
                                </h3>

                                <span
                                    :class="badgeClass(activity.activity_type)"
                                    class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                >
                                    {{
                                        activity.activity_type
                                            .replaceAll("_", " ")
                                    }}
                                </span>

                            </div>

                            <p
                                class="mt-3 leading-7 text-slate-600"
                            >
                                {{
                                    activity.description ||
                                    "No description available."
                                }}
                            </p>

                        </div>

                        <div
                            class="text-right"
                        >

                            <p
                                class="font-semibold text-slate-800"
                            >
                                {{
                                    activity.user?.name ??
                                    "System"
                                }}
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                {{
                                    relativeTime(
                                        activity.created_at
                                    )
                                }}
                            </p>

                        </div>

                    </div>

                </div>

                <!-- Body -->

                <div class="px-6 py-5">
                                    <!-- Details Toggle -->

                    <button
                        type="button"
                        @click="toggleDetails(activity.id)"
                        class="rounded-lg bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100"
                    >
                        {{
                            isExpanded(activity.id)
                                ? "Hide Details"
                                : "Show Details"
                        }}
                    </button>

                    <!-- Expanded Details -->

                    <div
                        v-if="isExpanded(activity.id)"
                        class="mt-5 space-y-5"
                    >
                        <div class="grid gap-4 lg:grid-cols-2">
                            <div
                                v-if="activity.old_values"
                                class="rounded-xl border border-red-200 bg-red-50 p-4"
                            >
                                <h4 class="mb-3 font-semibold text-red-700">
                                    Previous Values
                                </h4>

                                <pre class="overflow-auto whitespace-pre-wrap break-all text-xs leading-6 text-red-700">{{ JSON.stringify(activity.old_values, null, 2) }}</pre>
                            </div>

                            <div
                                v-if="activity.new_values"
                                class="rounded-xl border border-emerald-200 bg-emerald-50 p-4"
                            >
                                <h4 class="mb-3 font-semibold text-emerald-700">
                                    New Values
                                </h4>

                                <pre class="overflow-auto whitespace-pre-wrap break-all text-xs leading-6 text-emerald-700">{{ JSON.stringify(activity.new_values, null, 2) }}</pre>
                            </div>
                        </div>

                        <div class="grid gap-4 border-t border-slate-100 pt-5 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    IP Address
                                </p>

                                <p class="mt-1 break-all text-sm text-slate-700">
                                    {{ activity.ip_address || "—" }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Browser / Device
                                </p>

                                <p class="mt-1 break-all text-sm text-slate-700">
                                    {{ activity.user_agent || "—" }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5 text-xs text-slate-500">
                            <span>
                                Activity ID:
                                <strong>#{{ activity.id }}</strong>
                            </span>

                            <span>
                                {{
                                    new Date(activity.created_at).toLocaleString(
                                        "en-PH",
                                        {
                                            year: "numeric",
                                            month: "long",
                                            day: "numeric",
                                            hour: "2-digit",
                                            minute: "2-digit",
                                        }
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</template>