<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import dashboardService from "@/services/dashboardService";
import toast from "@/utils/toast";

import {
    Building2,
    MapPinned,
    Users,
    UserCog,
    CalendarCheck,
    BadgeDollarSign,
    CheckCircle2,
    Wallet,
} from "lucide-vue-next";

const loading = ref(false);

const summary = reactive({
    projects: 0,
    lots: 0,
    available_lots: 0,
    reserved_lots: 0,
    sold_lots: 0,
    clients: 0,
    agents: 0,
    reservations: 0,
    sales: 0,
    total_sales_value: 0,
    total_balance: 0,
});

const formatMoney = (amount) => {
    return Number(amount || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
        maximumFractionDigits: 0,
    });
};

const loadDashboard = async () => {
    loading.value = true;

    try {
        const response = await dashboardService.getSummary();

        Object.assign(summary, response.data.data);
    } catch (error) {
        console.error(error);
        toast.error("Failed to load dashboard summary.");
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadDashboard();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Dashboard
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Overview of property inventory, reservations, sales, clients, and collections.
                </p>
            </div>

            <div
                v-if="loading"
                class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500"
            >
                Loading dashboard...
            </div>

            <div
                v-else
                class="grid gap-4"
                style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));"
            >
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-emerald-700">
                                Projects
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-emerald-950">
                                {{ summary.projects }}
                            </h2>

                            <p class="mt-1 text-xs text-emerald-700">
                                Active project records
                            </p>
                        </div>

                        <div class="rounded-xl bg-emerald-100 p-3 text-emerald-700">
                            <Building2 class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-blue-700">
                                Total Lots
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-blue-950">
                                {{ summary.lots }}
                            </h2>

                            <p class="mt-1 text-xs text-blue-700">
                                All inventory lots
                            </p>
                        </div>

                        <div class="rounded-xl bg-blue-100 p-3 text-blue-700">
                            <MapPinned class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-green-100 bg-green-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-green-700">
                                Available Lots
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-green-950">
                                {{ summary.available_lots }}
                            </h2>

                            <p class="mt-1 text-xs text-green-700">
                                Ready for reservation
                            </p>
                        </div>

                        <div class="rounded-xl bg-green-100 p-3 text-green-700">
                            <CheckCircle2 class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-amber-700">
                                Reserved Lots
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-amber-950">
                                {{ summary.reserved_lots }}
                            </h2>

                            <p class="mt-1 text-xs text-amber-700">
                                Pending sale conversion
                            </p>
                        </div>

                        <div class="rounded-xl bg-amber-100 p-3 text-amber-700">
                            <CalendarCheck class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-purple-100 bg-purple-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-purple-700">
                                Sold Lots
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-purple-950">
                                {{ summary.sold_lots }}
                            </h2>

                            <p class="mt-1 text-xs text-purple-700">
                                Completed sale inventory
                            </p>
                        </div>

                        <div class="rounded-xl bg-purple-100 p-3 text-purple-700">
                            <BadgeDollarSign class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-sky-700">
                                Clients
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-sky-950">
                                {{ summary.clients }}
                            </h2>

                            <p class="mt-1 text-xs text-sky-700">
                                Registered buyers
                            </p>
                        </div>

                        <div class="rounded-xl bg-sky-100 p-3 text-sky-700">
                            <Users class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-indigo-700">
                                Agents
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-indigo-950">
                                {{ summary.agents }}
                            </h2>

                            <p class="mt-1 text-xs text-indigo-700">
                                Main agents and sub-agents
                            </p>
                        </div>

                        <div class="rounded-xl bg-indigo-100 p-3 text-indigo-700">
                            <UserCog class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-rose-100 bg-rose-50 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-rose-700">
                                Total Sales Value
                            </p>

                            <h2 class="mt-2 text-xl font-bold text-rose-950">
                                {{ formatMoney(summary.total_sales_value) }}
                            </h2>

                            <p class="mt-1 text-xs text-rose-700">
                                Non-cancelled sales
                            </p>
                        </div>

                        <div class="rounded-xl bg-rose-100 p-3 text-rose-700">
                            <Wallet class="h-6 w-6" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>