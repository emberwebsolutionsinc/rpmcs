<script setup>
import { computed, ref } from "vue";

import EmptyState from "@/components/common/EmptyState.vue";
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import BlockModal from "@/components/project-details/BlockModal.vue";

import projectBlockService from "@/services/projectBlockService.js";
import toast from "@/utils/toast";
import { confirmDelete } from "@/utils/swal";

import {
    Grid2X2,
    Plus,
    Pencil,
    Trash2,
    Layers3,
    MapPinned,
} from "lucide-vue-next";

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["refresh"]);

const showModal = ref(false);
const selectedBlock = ref(null);
const processing = ref(false);

const blocks = computed(() => props.project.blocks ?? []);
const phases = computed(() => props.project.phases ?? []);
const lots = computed(() => props.project.lots ?? []);

const getPhaseName = (phaseId) => {
    if (!phaseId) {
        return "No Phase";
    }

    const phase = phases.value.find((item) => item.id === phaseId);

    return phase ? phase.phase_name : "Unknown Phase";
};

const getBlockLotsCount = (blockId) => {
    return lots.value.filter(
        (lot) => lot.project_block_id === blockId
    ).length;
};

const openCreateModal = () => {
    selectedBlock.value = {
        property_project_id: props.project.id,
        project_phase_id: null,
    };

    showModal.value = true;
};

const openEditModal = (block) => {
    selectedBlock.value = block;
    showModal.value = true;
};

const closeModal = () => {
    selectedBlock.value = null;
    showModal.value = false;
};

const submitBlock = async (form) => {
    processing.value = true;

    const payload = {
        ...form,
        property_project_id: props.project.id,
    };

    try {
        if (selectedBlock.value?.id) {
            await projectBlockService.updateBlock(
                selectedBlock.value.id,
                payload
            );

            toast.success("Project block updated successfully.");
        } else {
            await projectBlockService.createBlock(payload);

            toast.success("Project block created successfully.");
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

const deleteBlock = async (block) => {
    const confirmed = await confirmDelete(
        `Delete ${block.block_no}?`
    );

    if (!confirmed) return;

    processing.value = true;

    try {
        await projectBlockService.deleteBlock(block.id);

        toast.success("Project block deleted successfully.");

        emit("refresh");
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || "Failed to delete block.");
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
                    Project Blocks
                </h3>

                <p class="text-sm text-slate-500">
                    Manage blocks under this project and assign each block to a phase.
                </p>
            </div>

            <button
                @click="openCreateModal"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
            >
                <Plus class="h-5 w-5" />
                Add Block
            </button>
        </div>

        <div
            v-if="blocks.length > 0"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Block
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Phase
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Lots
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
                            v-for="block in blocks"
                            :key="block.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-700">
                                        <Grid2X2 class="h-5 w-5" />
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{ block.block_no }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            Block ID: {{ block.id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                    <Layers3 class="h-3.5 w-3.5" />
                                    {{ getPhaseName(block.project_phase_id) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                    <MapPinned class="h-3.5 w-3.5" />
                                    {{ getBlockLotsCount(block.id) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ block.description || "—" }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <StatusBadge :value="block.status" />
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button
                                        @click="openEditModal(block)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        Edit
                                    </button>

                                    <button
                                        @click="deleteBlock(block)"
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
            title="No Blocks Yet"
            description="Create the first block under this property project."
            button-text="Add Block"
            :icon="Grid2X2"
            @action="openCreateModal"
        />

        <BlockModal
            :show="showModal"
            :block="selectedBlock"
            :phases="phases"
            @close="closeModal"
            @submit="submitBlock"
        />

        <LoadingOverlay
            :show="processing"
            title="Processing Block"
            description="Please wait while BPMCS updates the block records."
        />
    </div>
</template>