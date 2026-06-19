<script setup>
defineProps({
    title: {
        type: String,
        default: "No records found",
    },

    description: {
        type: String,
        default: "There are currently no records available.",
    },

    buttonText: {
        type: String,
        default: null,
    },

    icon: {
        type: Object,
        default: null,
    },
});

defineEmits(["action"]);
</script>

<template>
    <div
        class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center"
    >
        <div
            v-if="icon"
            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700"
        >
            <component
                :is="icon"
                class="h-8 w-8"
            />
        </div>

        <h3
            class="text-lg font-semibold text-slate-900"
        >
            {{ title }}
        </h3>

        <p
            class="mt-2 text-sm text-slate-500"
        >
            {{ description }}
        </p>

        <button
            v-if="buttonText"
            @click="$emit('action')"
            class="mt-5 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
        >
            {{ buttonText }}
        </button>
    </div>
</template>