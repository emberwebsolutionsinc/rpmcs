<script setup>
defineProps({
    buckets: {
        type: Object,
        default: () => ({}),
    },

    selectedBucket: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["select"]);

const orderedBuckets = [
    "current",
    "1_30",
    "31_60",
    "61_90",
    "91_180",
    "181_plus",
];

const bucketClasses = {
    current: "border-emerald-200 bg-emerald-50 text-emerald-800",
    "1_30": "border-yellow-200 bg-yellow-50 text-yellow-800",
    "31_60": "border-orange-200 bg-orange-50 text-orange-800",
    "61_90": "border-red-200 bg-red-50 text-red-800",
    "91_180": "border-red-300 bg-red-100 text-red-900",
    "181_plus": "border-red-400 bg-red-200 text-red-950",
};

const activeClass = "ring-2 ring-slate-900 ring-offset-2";

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const selectBucket = (key) => {
    emit("select", key);
};
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900">
                    Aging Buckets
                </h3>

                <p class="text-sm text-slate-500">
                    Click a bucket to filter the aging report.
                </p>
            </div>

            <p
                v-if="selectedBucket"
                class="text-xs font-medium text-slate-500"
            >
                Active filter:
                <span class="font-semibold text-slate-900">
                    {{ buckets[selectedBucket]?.label }}
                </span>
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <button
                v-for="key in orderedBuckets"
                :key="key"
                type="button"
                @click="selectBucket(key)"
                class="rounded-2xl border p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                :class="[
                    bucketClasses[key],
                    selectedBucket === key ? activeClass : '',
                ]"
            >
                <p class="text-xs font-semibold uppercase tracking-wide">
                    {{ buckets[key]?.label || key }}
                </p>

                <h3 class="mt-2 text-2xl font-bold">
                    {{ buckets[key]?.count || 0 }}
                </h3>

                <p class="mt-1 text-sm font-semibold">
                    {{ formatMoney(buckets[key]?.amount) }}
                </p>

                <p class="mt-2 text-xs opacity-80">
                    {{ selectedBucket === key ? "Click again to clear" : "Click to filter" }}
                </p>
            </button>
        </div>
    </div>
</template>