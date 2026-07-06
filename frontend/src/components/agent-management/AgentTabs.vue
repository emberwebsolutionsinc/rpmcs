<script setup>
defineProps({
    tabs: {
        type: Array,
        required: true,
    },
    activeTab: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["change"]);
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6">
            <nav class="flex gap-6 overflow-x-auto">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    @click="emit('change', tab.key)"
                    class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium"
                    :class="
                        activeTab === tab.key
                            ? 'border-emerald-600 text-emerald-700'
                            : 'border-transparent text-slate-500 hover:text-slate-700'
                    "
                >
                    {{ tab.label }}
                </button>
            </nav>
        </div>

        <div class="p-6">
            <slot />
        </div>
    </div>
</template>