<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        default: 0,
    },
    icon: {
        type: Object,
        required: true,
    },
    color: {
        type: String,
        default: "emerald",
    },
});
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">
                    {{ title }}
                </p>

                <h3 class="mt-2 text-2xl font-bold text-slate-900">
                    {{ value }}
                </h3>
            </div>

            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"
            >
                <component
                    :is="icon"
                    class="h-6 w-6"
                />
            </div>
        </div>
    </div>
</template>