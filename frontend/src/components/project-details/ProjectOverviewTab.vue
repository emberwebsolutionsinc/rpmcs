<script setup>
import StatusBadge from "@/components/common/StatusBadge.vue";

defineProps({
    project: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">
                Project Information
            </h3>

            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Project Code
                    </p>
                    <p class="mt-1 font-medium text-slate-800">
                        {{ project.project_code }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Project Name
                    </p>
                    <p class="mt-1 font-medium text-slate-800">
                        {{ project.project_name }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Developer
                    </p>
                    <p class="mt-1 font-medium text-slate-800">
                        {{ project.developer_name || "—" }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Province
                    </p>
                    <p class="mt-1 font-medium text-slate-800">
                        {{ project.province || "—" }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Municipality
                    </p>
                    <p class="mt-1 font-medium text-slate-800">
                        {{ project.municipality || "—" }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Barangay
                    </p>
                    <p class="mt-1 font-medium text-slate-800">
                        {{ project.barangay || "—" }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Street
                    </p>
                    <p class="mt-1 font-medium text-slate-800">
                        {{ project.street || "—" }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Country
                    </p>
                    <p class="mt-1 font-medium text-slate-800">
                        {{ project.country || "Philippines" }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">
                        Status
                    </p>
                    <div class="mt-1">
                        <StatusBadge :value="project.status" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>