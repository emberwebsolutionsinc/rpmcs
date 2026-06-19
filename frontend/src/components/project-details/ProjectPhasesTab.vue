<script setup>
import { ref } from "vue";

import EmptyState from "@/components/common/EmptyState.vue";
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import PhaseModal from "@/components/project-details/PhaseModal.vue";

import projectPhaseService from "@/services/projectPhaseService.js";
import toast from "@/utils/toast";
import { confirmDelete } from "@/utils/swal";

import {
    Layers3,
    Plus,
    Pencil,
    Trash2,
} from "lucide-vue-next";

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["refresh"]);

const showModal = ref(false);
const selectedPhase = ref(null);
const processing = ref(false);

const openCreateModal = () => {
    selectedPhase.value = {
        property_project_id: props.project.id,
    };

    showModal.value = true;
};

const openEditModal = (phase) => {
    selectedPhase.value = phase;
    showModal.value = true;
};

const closeModal = () => {
    selectedPhase.value = null;
    showModal.value = false;
};

const submitPhase = async (form) => {
    processing.value = true;

    const payload = {
        ...form,
        property_project_id: props.project.id,
    };

    try {
        if (selectedPhase.value?.id) {
            await projectPhaseService.updatePhase(
                selectedPhase.value.id,
                payload
            );

            toast.success("Project phase updated successfully.");
        } else {
            await projectPhaseService.createPhase(payload);

            toast.success("Project phase created successfully.");
        }

        closeModal();
        emit("refresh");
    } catch (error) {
        console.error(error);

        if (error.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0][0];
            toast.error(firstError);
            return;
        }

        toast.error(error.response?.data?.message || "Something went wrong.");
    } finally {
        processing.value = false;
    }
};

const deletePhase = async (phase) => {
    const confirmed = await confirmDelete(
        `Delete ${phase.phase_name}?`
    );

    if (!confirmed) return;

    processing.value = true;

    try {
        await projectPhaseService.deletePhase(phase.id);

        toast.success("Project phase deleted successfully.");

        emit("refresh");
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || "Failed to delete phase.");
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">
                    Project Phases
                </h3>

                <p class="text-sm text-slate-500">
                    Manage phases under this property project.
                </p>
            </div>

            <button
                @click="openCreateModal"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
            >
                <Plus class="h-5 w-5" />
                Add Phase
            </button>
        </div>

        <div
            v-if="project.phases && project.phases.length > 0"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Phase
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Description
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
                            v-for="phase in project.phases"
                            :key="phase.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                                        <Layers3 class="h-5 w-5" />
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{ phase.phase_name }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            Code: {{ phase.phase_code }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ phase.description || "—" }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <StatusBadge :value="phase.status" />
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button
                                        @click="openEditModal(phase)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        Edit
                                    </button>

                                    <button
                                        @click="deletePhase(phase)"
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

        <EmptyState
            v-else
            title="No Phases Yet"
            description="Create the first phase for this property project."
            button-text="Add Phase"
            :icon="Layers3"
            @action="openCreateModal"
        />

        <PhaseModal
            :show="showModal"
            :phase="selectedPhase"
            @close="closeModal"
            @submit="submitPhase"
        />

        <LoadingOverlay
            :show="processing"
            title="Processing Phase"
            description="Please wait while BPMCS updates the phase records."
        />
    </div>
</template>