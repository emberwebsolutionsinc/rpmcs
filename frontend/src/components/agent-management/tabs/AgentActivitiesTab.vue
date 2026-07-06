<script setup>
defineProps({
    agent: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <h3 class="font-semibold text-slate-900">
                Agent Activities
            </h3>

            <p class="text-sm text-slate-500">
                Activity logs will show important actions related to this agent.
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
            Activity logs are reserved for the audit trail module.
        </div>
    </div>
</template>