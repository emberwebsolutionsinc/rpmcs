<script setup>
import { computed, onMounted, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import ReservationTable from "@/components/sales-management/reservations/ReservationTable.vue";
import ReservationModal from "@/components/reservations/ReservationModal.vue";
import ConvertToSaleModal from "@/components/sales-management/reservations/ConvertToSaleModal.vue";
import ReservationDetailsDrawer from "@/components/sales-management/reservations/ReservationDetailsDrawer.vue";

import reservationService from "@/services/reservationService";
import saleService from "@/services/saleService.js";
import clientService from "@/services/clientService";
import agentService from "@/services/agentService";
import lotService from "@/services/lotService.js";

import { confirmDelete } from "@/utils/swal";

import toast from "@/utils/toast";

import {
    Plus,
    CalendarCheck,
    CheckCircle,
    Clock,
    XCircle,
} from "lucide-vue-next";

const reservations = ref([]);
const loading = ref(false);
const processing = ref(false);

const showReservationModal = ref(false);
const clients = ref([]);
const agents = ref([]);
const lots = ref([]);

const showConvertModal = ref(false);
const selectedReservation = ref(null);

const showDetailsDrawer = ref(false);
const selectedReservationDetails = ref(null);

const fetchReservations = async () => {
    loading.value = true;

    try {
        const response = await reservationService.getReservations({
            per_page: 100,
        });

        reservations.value = response.data.data ?? [];
    } catch (error) {
        console.error("Failed to fetch reservations:", error);
        toast.error("Failed to load reservations.");
        reservations.value = [];
    } finally {
        loading.value = false;
    }
};
const openConvertFromDrawer = (reservation) => {
    closeDetailsDrawer();
    openConvertModal(reservation);
};

const loadClients = async () => {
    const response = await clientService.getClients({
        per_page: 100,
    });

    clients.value = response.data.data ?? [];
};

const loadAgents = async () => {
    const response = await agentService.getAgents({
        per_page: 100,
    });

    agents.value = response.data.data ?? [];
};

const loadAvailableLots = async () => {
    const response = await lotService.getLots({
        status: "available",
        per_page: 500,
    });

    lots.value = response.data.data ?? [];
};

const openCreateModal = async () => {
    processing.value = true;

    try {
        await Promise.all([
            loadClients(),
            loadAgents(),
            loadAvailableLots(),
        ]);

        showReservationModal.value = true;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load reservation form data.");
    } finally {
        processing.value = false;
    }
};

const closeReservationModal = () => {
    showReservationModal.value = false;
};

const submitReservation = async (form) => {
    processing.value = true;

    try {
        await reservationService.createReservation(form);

        toast.success("Reservation created successfully.");

        closeReservationModal();

        await fetchReservations();
    } catch (error) {
        console.error(error);

        if (error.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0][0];
            toast.error(firstError);
            return;
        }

        toast.error(
            error.response?.data?.message ||
            "Failed to create reservation."
        );
    } finally {
        processing.value = false;
    }
};

const openConvertModal = (reservation) => {
    selectedReservation.value = reservation;
    showConvertModal.value = true;
};

const closeConvertModal = () => {
    selectedReservation.value = null;
    showConvertModal.value = false;
};

const submitConvertToSale = async (form) => {
    processing.value = true;

    try {
        await saleService.createSale(form);

        toast.success("Reservation converted to sale successfully.");

        closeConvertModal();

        await fetchReservations();
    } catch (error) {
        console.error(error);

        if (error.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0][0];
            toast.error(firstError);
            return;
        }

        toast.error(
            error.response?.data?.message ||
            "Failed to convert reservation to sale."
        );
    } finally {
        processing.value = false;
    }
};

const stats = computed(() => ({
    total: reservations.value.length,

    active: reservations.value.filter((item) => {
        return item.status === "active" || item.status === "reserved" || !item.status;
    }).length,

    expired: reservations.value.filter((item) => {
        return item.status === "expired";
    }).length,

    cancelled: reservations.value.filter((item) => {
        return item.status === "cancelled";
    }).length,
}));

const handleView = (reservation) => {
    selectedReservationDetails.value = reservation;
    showDetailsDrawer.value = true;
};

const closeDetailsDrawer = () => {
    selectedReservationDetails.value = null;
    showDetailsDrawer.value = false;
};

const handleEdit = (reservation) => {
    console.log("Edit reservation:", reservation);
};

const handleCancel = async (reservation) => {
    const confirmed = await confirmDelete(
        `Cancel reservation ${reservation.reservation_no}? This will make the lot available again.`
    );

    if (!confirmed) return;

    processing.value = true;

    try {
        await reservationService.cancelReservation(reservation.id);

        toast.success("Reservation cancelled successfully.");

        await fetchReservations();
    } catch (error) {
        console.error(error);

        toast.error(
            error.response?.data?.message ||
            "Failed to cancel reservation."
        );
    } finally {
        processing.value = false;
    }
};
onMounted(() => {
    fetchReservations();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Reservations
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Manage lot reservations, client details, agents, reservation fees, and reservation status.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                    @click="openCreateModal"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    New Reservation
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Total Reservations
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-gray-900">
                                {{ stats.total }}
                            </h2>
                        </div>

                        <div class="rounded-lg bg-blue-50 p-3 text-blue-600">
                            <CalendarCheck class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Active
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-gray-900">
                                {{ stats.active }}
                            </h2>
                        </div>

                        <div class="rounded-lg bg-green-50 p-3 text-green-600">
                            <CheckCircle class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Expired
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-gray-900">
                                {{ stats.expired }}
                            </h2>
                        </div>

                        <div class="rounded-lg bg-yellow-50 p-3 text-yellow-600">
                            <Clock class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Cancelled
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-gray-900">
                                {{ stats.cancelled }}
                            </h2>
                        </div>

                        <div class="rounded-lg bg-red-50 p-3 text-red-600">
                            <XCircle class="h-6 w-6" />
                        </div>
                    </div>
                </div>
            </div>

            <ReservationTable
                :reservations="reservations"
                :loading="loading"
                @view="handleView"
                @edit="handleEdit"
                @cancel="handleCancel"
                @convert="openConvertModal"
            />

            <ReservationModal
                :show="showReservationModal"
                :clients="clients"
                :agents="agents"
                :lots="lots"
                @close="closeReservationModal"
                @submit="submitReservation"
            />

            <ConvertToSaleModal
                :show="showConvertModal"
                :reservation="selectedReservation"
                @close="closeConvertModal"
                @submit="submitConvertToSale"
            />

            <ReservationDetailsDrawer
                :show="showDetailsDrawer"
                :reservation="selectedReservationDetails"
                @close="closeDetailsDrawer"
                @convert="openConvertFromDrawer"
            />
        </div>
    </AppLayout>
</template>