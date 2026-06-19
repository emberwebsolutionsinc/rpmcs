<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import StatCard from "@/components/dashboard/StatCard.vue";

import {
    Building2,
    MapPinned,
    Users,
    Wallet,
    UserCog,
    BadgeDollarSign,
    FileSignature,
} from "lucide-vue-next";
</script>

<template>
    <AppLayout>

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-slate-800">
                Dashboard
            </h1>

            <p class="text-slate-500">
                Welcome to BPMCS
            </p>
        </div>

        <div
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5"
        >
            <StatCard
                title="Projects"
                :value="0"
                :icon="Building2"
            />

            <StatCard
                title="Properties"
                :value="0"
                :icon="MapPinned"
            />

            <StatCard
                title="Clients"
                :value="0"
                :icon="Users"
            />

            <StatCard
                title="Agents"
                :value="0"
                :icon="UserCog"
            />
        </div>

        <div
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-5"
        >
            <StatCard
                title="Collections"
                value="₱0.00"
                :icon="Wallet"
            />

            <StatCard
                title="Commissions"
                value="₱0.00"
                :icon="BadgeDollarSign"
            />

            <StatCard
                title="Reservations"
                :value="0"
                :icon="FileSignature"
            />

            <StatCard
                title="Sub-Agents"
                :value="0"
                :icon="Users"
            />
        </div>

        <div
            class="grid lg:grid-cols-2 gap-5 mt-6"
        >
            <div
                class="bg-white rounded-2xl border border-slate-200 p-6"
            >
                <h2 class="font-semibold text-lg">
                    Recent Reservations
                </h2>

                <div
                    class="text-slate-500 mt-4"
                >
                    No data available.
                </div>
            </div>

            <div
                class="bg-white rounded-2xl border border-slate-200 p-6"
            >
                <h2 class="font-semibold text-lg">
                    Recent Collections
                </h2>

                <div
                    class="text-slate-500 mt-4"
                >
                    No data available.
                </div>
            </div>
        </div>

    </AppLayout>
</template>