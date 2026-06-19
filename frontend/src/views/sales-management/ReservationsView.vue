<script setup>
import { computed, onMounted, ref } from "vue";

import ReservationTable from "@/components/sales-management/reservations/ReservationTable.vue";
import reservationService from "@/services/reservationService";

import {
    Plus,
    CalendarCheck,
    CheckCircle,
    Clock,
    XCircle,
} from "lucide-vue-next";

const reservations = ref([]);
const loading = ref(false);

const fetchReservations = async () => {
    loading.value = true;

    try {
        const response = await reservationService.getReservations();
        const payload = response.data;

        reservations.value = payload.data ?? payload ?? [];
    } catch (error) {
        console.error("Failed to fetch reservations:", error);
        reservations.value = [];
    } finally {
        loading.value = false;
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

const openCreateModal = () => {
    console.log("Open create reservation modal");
};

const handleView = (reservation) => {
    console.log("View reservation:", reservation);
};

const handleEdit = (reservation) => {
    console.log("Edit reservation:", reservation);
};

const handleCancel = (reservation) => {
    console.log("Cancel reservation:", reservation);
};

onMounted(fetchReservations);
</script>

<template>
    <div class="space-y-6">
        <!-- Page Header -->
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

        <!-- Statistics Cards -->
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

        <!-- Reservation Table -->
        <div class="space-y-4">
            <ReservationTable
                :reservations="reservations"
                :loading="loading"
                @view="handleView"
                @edit="handleEdit"
                @cancel="handleCancel"
            />
        </div>
    </div>
</template>