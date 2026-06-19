<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                {{ title }}
            </h1>

            <p
                v-if="description"
                class="mt-1 text-sm text-slate-500"
            >
                {{ description }}
            </p>
        </div>

        <div v-if="$slots.actions">
            <slot name="actions" />
        </div>
    </div>
</template>