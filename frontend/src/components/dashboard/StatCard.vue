<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: Object,
});
</script>

<template>
    <div
        class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition"
    >
        <div class="flex justify-between items-center">
            <div>
                <p class="text-slate-500 text-sm">
                    {{ title }}
                </p>

                <h2 class="text-3xl font-bold mt-2">
                    {{ value }}
                </h2>
            </div>

            <div
                class="h-12 w-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700"
            >
                <component
                    :is="icon"
                    class="h-6 w-6"
                />
            </div>
        </div>
    </div>
</template>