<script setup>
import { computed, ref, watch } from "vue";
import { RouterLink, useRoute } from "vue-router";

import {
    LayoutDashboard,
    Building2,
    Users,
    UserCog,
    FileSignature,
    Wallet,
    BadgeDollarSign,
    BarChart3,
    Settings,
    ChevronDown,
    X,
    AlertTriangle,
} from "lucide-vue-next";

defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["close"]);

const route = useRoute();

const isActive = (path) => {
    if (path === "/property-management/projects") {
        return route.path.startsWith("/property-management/projects");
    }

    if (path === "/agent-management/agents") {
        return route.path.startsWith("/agent-management/agents");
    }

    return route.path === path;
};

const isPropertyRoute = computed(() =>
    route.path.startsWith("/property-management")
);

const isClientRoute = computed(() =>
    route.path.startsWith("/client-management")
);

const isAgentRoute = computed(() =>
    route.path.startsWith("/agent-management")
);

const isSalesRoute = computed(() =>
    route.path.startsWith("/sales-management")
);

const isCommissionsRoute = computed(() =>
    route.path.startsWith("/commission-management")
);

const isReportsRoute = computed(() =>
    route.path.startsWith("/reports")
);

const isAdministrationRoute = computed(() =>
    route.path.startsWith("/administration")
);

const openMenus = ref({
    property: false,
    clients: false,
    agents: false,
    sales: false,
    commissions: false,
    reports: false,
    administration: false,
});

const syncOpenMenusWithRoute = () => {
    if (isPropertyRoute.value) openMenus.value.property = true;
    if (isClientRoute.value) openMenus.value.clients = true;
    if (isAgentRoute.value) openMenus.value.agents = true;
    if (isSalesRoute.value) openMenus.value.sales = true;
    if (isCommissionsRoute.value) openMenus.value.commissions = true;
    if (isReportsRoute.value) openMenus.value.reports = true;
    if (isAdministrationRoute.value) openMenus.value.administration = true;
};

watch(() => route.path, syncOpenMenusWithRoute, { immediate: true });

const toggleMenu = (menu) => {
    openMenus.value[menu] = !openMenus.value[menu];
};

const menuButtonClass = (active) => {
    return active
        ? "bg-emerald-600 text-white font-semibold"
        : "text-emerald-50 hover:bg-emerald-700";
};

const linkClass = (path) => {
    return isActive(path)
        ? "bg-emerald-600 text-white font-semibold"
        : "text-emerald-50 hover:bg-emerald-700";
};
</script>

<template>
    <div>
        <div
            v-if="open"
            @click="$emit('close')"
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
        />

        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 transform bg-emerald-800 text-white shadow-xl transition-transform duration-300 lg:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-full flex-col">
                <div class="border-b border-emerald-700 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-emerald-700">
                                <Building2 class="h-7 w-7" />
                            </div>

                            <div>
                                <h1 class="text-xl font-bold">RPMCS</h1>
                                <p class="text-xs text-emerald-200">
                                    Property Management
                                </p>
                            </div>
                        </div>

                        <button
                            @click="$emit('close')"
                            class="rounded-lg p-2 hover:bg-emerald-700 lg:hidden"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <nav class="flex-1 space-y-2 overflow-y-auto p-4 text-sm">
                    <RouterLink
                        to="/dashboard"
                        @click="$emit('close')"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="linkClass('/dashboard')"
                    >
                        <LayoutDashboard class="h-5 w-5" />
                        Dashboard
                    </RouterLink>

                    <!-- Property Management -->
                    <button
                        @click="toggleMenu('property')"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isPropertyRoute)"
                    >
                        <span class="flex items-center gap-3">
                            <Building2 class="h-5 w-5" />
                            Property Management
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition"
                            :class="{ 'rotate-180': openMenus.property }"
                        />
                    </button>

                    <div
                        v-show="openMenus.property"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            @click="$emit('close')"
                            to="/property-management/projects"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/property-management/projects')"
                        >
                            <Building2 class="h-4 w-4" />
                            Projects
                        </RouterLink>
                    </div>

                    <!-- Client Management -->
                    <button
                        @click="toggleMenu('clients')"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isClientRoute)"
                    >
                        <span class="flex items-center gap-3">
                            <Users class="h-5 w-5" />
                            Client Management
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition"
                            :class="{ 'rotate-180': openMenus.clients }"
                        />
                    </button>

                    <div
                        v-show="openMenus.clients"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            @click="$emit('close')"
                            to="/client-management/clients"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/client-management/clients')"
                        >
                            <Users class="h-4 w-4" />
                            Clients
                        </RouterLink>
                    </div>

                    <!-- Agent Management -->
                    <button
                        @click="toggleMenu('agents')"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isAgentRoute)"
                    >
                        <span class="flex items-center gap-3">
                            <UserCog class="h-5 w-5" />
                            Agent Management
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition"
                            :class="{ 'rotate-180': openMenus.agents }"
                        />
                    </button>

                    <div
                        v-show="openMenus.agents"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            @click="$emit('close')"
                            to="/agent-management/agents"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/agent-management/agents')"
                        >
                            <UserCog class="h-4 w-4" />
                            Agents
                        </RouterLink>
                    </div>

                    <!-- Sales Management -->
                    <button
                        @click="toggleMenu('sales')"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isSalesRoute)"
                    >
                        <span class="flex items-center gap-3">
                            <FileSignature class="h-5 w-5" />
                            Sales Management
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition"
                            :class="{ 'rotate-180': openMenus.sales }"
                        />
                    </button>

                    <div
                        v-show="openMenus.sales"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            @click="$emit('close')"
                            to="/sales-management/reservations"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/sales-management/reservations')"
                        >
                            <FileSignature class="h-4 w-4" />
                            Reservations
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/sales-management/sales"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/sales-management/sales')"
                        >
                            <BadgeDollarSign class="h-4 w-4" />
                            Sales
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/sales-management/collections"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/sales-management/collections')"
                        >
                            <Wallet class="h-4 w-4" />
                            Collections
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/sales-management/overdue-accounts"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/sales-management/overdue-accounts')"
                        >
                            <AlertTriangle class="h-4 w-4" />
                            Overdue Accounts
                        </RouterLink>
                    </div>

                    <!-- Commission Management -->
                    <button
                        @click="toggleMenu('commissions')"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isCommissionsRoute)"
                    >
                        <span class="flex items-center gap-3">
                            <BadgeDollarSign class="h-5 w-5" />
                            Commission Management
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition"
                            :class="{ 'rotate-180': openMenus.commissions }"
                        />
                    </button>

                    <div
                        v-show="openMenus.commissions"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            @click="$emit('close')"
                            to="/commission-management/commissions"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/commission-management/commissions')"
                        >
                            <BadgeDollarSign class="h-4 w-4" />
                            Agent Commissions
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/reports/commission-payments"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/commission-payments')"
                        >
                            <BadgeDollarSign class="h-4 w-4" />
                            Commission Payments
                        </RouterLink>
                    </div>

                    <!-- Reports -->
                    <button
                        @click="toggleMenu('reports')"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isReportsRoute)"
                    >
                        <span class="flex items-center gap-3">
                            <BarChart3 class="h-5 w-5" />
                            Reports
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition"
                            :class="{ 'rotate-180': openMenus.reports }"
                        />
                    </button>

                    <div
                        v-show="openMenus.reports"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            @click="$emit('close')"
                            to="/reports/dashboard"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/dashboard')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            Reports Dashboard
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/reports/collections"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/collections')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            Collections Report
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/reports/sales"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/sales')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            Sales Report
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/reports/aging"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/aging')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            Aging Report
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/reports/agent-commissions"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/agent-commissions')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            Agent Commission Report
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/reports/commission-payments"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/commission-payments')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            Commission Payment Report
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/reports/agent-commission-ledger"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/agent-commission-ledger')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            Agent Commission Ledger
                        </RouterLink>
                    </div>

                    <!-- Administration -->
                    <button
                        @click="toggleMenu('administration')"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isAdministrationRoute)"
                    >
                        <span class="flex items-center gap-3">
                            <Settings class="h-5 w-5" />
                            Administration
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition"
                            :class="{ 'rotate-180': openMenus.administration }"
                        />
                    </button>

                    <div
                        v-show="openMenus.administration"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            @click="$emit('close')"
                            to="/administration/users"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/administration/users')"
                        >
                            <Settings class="h-4 w-4" />
                            Users
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/administration/roles"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/administration/roles')"
                        >
                            <Settings class="h-4 w-4" />
                            Roles
                        </RouterLink>

                        <RouterLink
                            @click="$emit('close')"
                            to="/administration/permissions"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/administration/permissions')"
                        >
                            <Settings class="h-4 w-4" />
                            Permissions
                        </RouterLink>
                    </div>
                </nav>

                <div class="border-t border-emerald-700 p-4">
                    <div class="text-xs text-emerald-200">
                        RPMCS v1.0.0
                    </div>

                    <div class="mt-1 text-xs text-emerald-300">
                        Property Management & Collection System
                    </div>
                </div>
            </div>
        </aside>
    </div>
</template>