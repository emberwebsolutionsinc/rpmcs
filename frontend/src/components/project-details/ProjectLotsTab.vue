<script setup>
import { computed, onMounted, ref } from "vue";

import EmptyState from "@/components/common/EmptyState.vue";
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import LotModal from "@/components/project-details/LotModal.vue";

import lotService from "@/services/lotService.js";
import propertyTypeService from "@/services/propertyTypeService.js";
import toast from "@/utils/toast";
import { confirmDelete } from "@/utils/swal";

import ReservationModal from "@/components/reservations/ReservationModal.vue";
import reservationService from "@/services/reservationService.js";
import clientService from "@/services/clientService.js";
import agentService from "@/services/agentService.js";

import {
    MapPinned,
    Plus,
    Pencil,
    Trash2,
    Layers3,
    Grid2X2,
    PhilippinePeso,
    CalendarPlus,
} from "lucide-vue-next";

const showReservationModal = ref(false);
const selectedReservationLot = ref(null);
const clients = ref([]);
const agents = ref([]);

const loadClients = async () => {
    try {
        const response = await clientService.getClients({
            per_page: 100,
        });

        clients.value = response.data.data ?? [];
    } catch (error) {
        console.error(error);
        toast.error("Failed to load clients.");
    }
};

const loadAgents = async () => {
    try {
        const response = await agentService.getAgents({
            per_page: 100,
        });

        agents.value = response.data.data ?? [];
    } catch (error) {
        console.error(error);
        toast.error("Failed to load agents.");
    }
};

const openReservationModal = async (lot) => {
    selectedReservationLot.value = lot;

    await Promise.all([
        loadClients(),
        loadAgents(),
    ]);

    showReservationModal.value = true;
};

const closeReservationModal = () => {
    selectedReservationLot.value = null;
    showReservationModal.value = false;
};

const submitReservation = async (form) => {
    processing.value = true;

    try {
        await reservationService.createReservation(form);

        toast.success("Lot reserved successfully.");

        closeReservationModal();

        emit("refresh");
    } catch (error) {
        console.error(error);

        if (error.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0][0];
            toast.error(firstError);
            return;
        }

        toast.error(error.response?.data?.message || "Failed to reserve lot.");
    } finally {
        processing.value = false;
    }
};

const props = defineProps({
    project: { 
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["refresh"]);

const showModal = ref(false);
const selectedLot = ref(null);
const processing = ref(false);
const propertyTypes = ref([]);

const lots = computed(() => props.project.lots ?? []);
const phases = computed(() => props.project.phases ?? []);
const blocks = computed(() => props.project.blocks ?? []);

const availableLots = computed(() =>
    lots.value.filter((lot) => lot.status === "available").length
);

const reservedLots = computed(() =>
    lots.value.filter((lot) => lot.status === "reserved").length
);

const soldLots = computed(() =>
    lots.value.filter((lot) => lot.status === "sold").length
);

const getPhaseName = (phaseId) => {
    if (!phaseId) return "No Phase";

    const phase = phases.value.find((item) => item.id === phaseId);

    return phase ? phase.phase_name : "Unknown Phase";
};

const getBlockName = (blockId) => {
    if (!blockId) return "No Block";

    const block = blocks.value.find((item) => item.id === blockId);

    return block ? block.block_no : "Unknown Block";
};

const getPropertyTypeName = (typeId) => {
    if (!typeId) return "—";

    const type = propertyTypes.value.find((item) => item.id === typeId);

    return type ? type.name : "—";
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(Number(amount || 0));
};

const loadPropertyTypes = async () => {
    try {
        const response = await propertyTypeService.getPropertyTypes();

        propertyTypes.value = response.data.data ?? [];
    } catch (error) {
        console.error(error);
        toast.error("Failed to load property types.");
    }
};

const openCreateModal = () => {
    selectedLot.value = {
        property_project_id: props.project.id,
        status: "available",
    };

    showModal.value = true;
};

const openEditModal = (lot) => {
    selectedLot.value = lot;
    showModal.value = true;
};

const closeModal = () => {
    selectedLot.value = null;
    showModal.value = false;
};

const submitLot = async (form) => {
    processing.value = true;

    const payload = {
        ...form,
        property_project_id: props.project.id,
    };

    try {
        if (selectedLot.value?.id) {
            await lotService.updateLot(selectedLot.value.id, payload);

            toast.success("Lot updated successfully.");
        } else {
            await lotService.createLot(payload);

            toast.success("Lot created successfully.");
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

const deleteLot = async (lot) => {
    const confirmed = await confirmDelete(
        `Delete ${lot.lot_no}?`
    );

    if (!confirmed) return;

    processing.value = true;

    try {
        await lotService.deleteLot(lot.id);

        toast.success("Lot deleted successfully.");

        emit("refresh");
    } catch (error) {
        console.error(error);

        toast.error(error.response?.data?.message || "Failed to delete lot.");
    } finally {
        processing.value = false;
    }
};

onMounted(() => {
    loadPropertyTypes();
    
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">
                    Project Lots
                </h3>

                <p class="text-sm text-slate-500">
                    Manage lot inventory, pricing, availability, and property classification.
                </p>
            </div>

            <button
                @click="openCreateModal"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
            >
                <Plus class="h-5 w-5" />
                Add Lot
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Available</p>
                <p class="mt-1 text-2xl font-bold text-emerald-700">
                    {{ availableLots }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Reserved</p>
                <p class="mt-1 text-2xl font-bold text-blue-700">
                    {{ reservedLots }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Sold</p>
                <p class="mt-1 text-2xl font-bold text-purple-700">
                    {{ soldLots }}
                </p>
            </div>
        </div>

        <div
            v-if="lots.length > 0"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Lot
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Location
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Type
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Area
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Selling Price
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
                            v-for="lot in lots"
                            :key="lot.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                        <MapPinned class="h-5 w-5" />
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{ lot.lot_no }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ lot.lot_code }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                        <Layers3 class="h-3.5 w-3.5" />
                                        {{ getPhaseName(lot.project_phase_id) }}
                                    </span>

                                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-3 py-1 text-xs font-medium text-purple-700">
                                        <Grid2X2 class="h-3.5 w-3.5" />
                                        {{ getBlockName(lot.project_block_id) }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ getPropertyTypeName(lot.property_type_id) }}
                            </td>

                            <td class="px-6 py-4 text-right text-sm text-slate-700">
                                {{ lot.lot_area }} sqm
                            </td>

                            <td class="px-6 py-4 text-right text-sm font-semibold text-slate-900">
                                <span class="inline-flex items-center gap-1">
                                    <PhilippinePeso class="h-4 w-4 text-slate-400" />
                                    {{ formatCurrency(lot.selling_price).replace("₱", "") }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <StatusBadge :value="lot.status" />
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button
                                        v-if="lot.status === 'available'"
                                        @click="openReservationModal(lot)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                    >
                                        <CalendarPlus class="h-4 w-4" />
                                        Reserve
                                    </button>
                                    <button
                                        @click="openEditModal(lot)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        Edit
                                    </button>

                                    <button
                                        @click="deleteLot(lot)"
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
            title="No Lots Yet"
            description="Create the first lot or property inventory item under this project."
            button-text="Add Lot"
            :icon="MapPinned"
            @action="openCreateModal"
        />

        <LotModal
            :show="showModal"
            :lot="selectedLot"
            :phases="phases"
            :blocks="blocks"
            :property-types="propertyTypes"
            @close="closeModal"
            @submit="submitLot"
        />

        <ReservationModal
            :show="showReservationModal"
            :lot="selectedReservationLot"
            :clients="clients"
            :agents="agents"
            @close="closeReservationModal"
            @submit="submitReservation"
        />

        <LoadingOverlay
            :show="processing"
            title="Processing Lot"
            description="Please wait while BPMCS updates the lot inventory records."
        />
    </div>
</template>