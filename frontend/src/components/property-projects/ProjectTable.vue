<script setup>
import { RouterLink } from "vue-router";

import {
    Pencil,
    Trash2,
    Eye,
    Building2,
    MapPin,
    Layers3,
    Grid2X2,
    MapPinned,
} from "lucide-vue-next";

import StatusBadge from "@/components/common/StatusBadge.vue";

defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
});

defineEmits([
    "edit",
    "delete",
]);
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Project
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Developer
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Location
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Inventory
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr
                        v-for="project in projects"
                        :key="project.id"
                        class="hover:bg-slate-50 transition"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                    <Building2 class="h-5 w-5" />
                                </div>

                                <div>
                                    <div class="font-semibold text-slate-900">
                                        {{ project.project_name }}
                                    </div>

                                    <div class="text-xs text-slate-500">
                                        Code: {{ project.project_code }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-700">
                            {{ project.developer_name || "—" }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-start gap-2 text-sm text-slate-700">
                                <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />

                                <span>
                                    {{ project.street || "" }}
                                    {{ project.barangay ? ", " + project.barangay : "" }}
                                    {{ project.municipality ? ", " + project.municipality : "" }}
                                    {{ project.province ? ", " + project.province : "" }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                    <Layers3 class="h-3.5 w-3.5" />
                                    {{ project.phases_count ?? 0 }}
                                </span>

                                <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-3 py-1 text-xs font-medium text-purple-700">
                                    <Grid2X2 class="h-3.5 w-3.5" />
                                    {{ project.blocks_count ?? 0 }}
                                </span>

                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                    <MapPinned class="h-3.5 w-3.5" />
                                    {{ project.lots_count ?? 0 }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <StatusBadge :value="project.status" />
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <RouterLink
                                    :to="`/property-management/projects/${project.id}`"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                >
                                    <Eye class="h-4 w-4" />
                                    View
                                </RouterLink>

                                <button
                                    @click="$emit('edit', project)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                >
                                    <Pencil class="h-4 w-4" />
                                    Edit
                                </button>

                                <button
                                    @click="$emit('delete', project)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>