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
    const groupedPaths = [
        "/property-management/projects",
        "/client-management/clients",
        "/agent-management/agents",
    ];

    if (groupedPaths.includes(path)) {
        return route.path.startsWith(path);
    }

    return route.path === path;
};

const isSalesRoute = computed(() =>
    route.path.startsWith("/sales-management")
);

const isReportsRoute = computed(() =>
    route.path.startsWith("/reports")
);

const isAdministrationRoute = computed(() =>
    route.path.startsWith("/administration")
);

const openMenus = ref({
    sales: false,
    reports: false,
    administration: false,
});

const syncOpenMenusWithRoute = () => {
    if (isSalesRoute.value) {
        openMenus.value.sales = true;
    }

    if (isReportsRoute.value) {
        openMenus.value.reports = true;
    }

    if (isAdministrationRoute.value) {
        openMenus.value.administration = true;
    }
};

watch(
    () => route.path,
    syncOpenMenusWithRoute,
    { immediate: true }
);

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
        <!-- Mobile overlay -->
        <div
            v-if="open"
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            @click="$emit('close')"
        />

        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 transform bg-emerald-800 text-white shadow-xl transition-transform duration-300 lg:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-full flex-col">
                <!-- Sidebar header -->
                <div class="border-b border-emerald-700 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-emerald-700"
                            >
                                <Building2 class="h-7 w-7" />
                            </div>

                            <div>
                                <h1 class="text-xl font-bold">
                                    RPMCS
                                </h1>

                                <p class="text-xs text-emerald-200">
                                    Property Management
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 hover:bg-emerald-700 lg:hidden"
                            aria-label="Close sidebar"
                            @click="$emit('close')"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <!-- Sidebar navigation -->
                <nav class="flex-1 space-y-2 overflow-y-auto p-4 text-sm">
                    <!-- Dashboard -->
                    <RouterLink
                        to="/dashboard"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="linkClass('/dashboard')"
                        @click="$emit('close')"
                    >
                        <LayoutDashboard class="h-5 w-5" />
                        <span>Dashboard</span>
                    </RouterLink>

                    <!-- Property Management -->
                    <RouterLink
                        to="/property-management/projects"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass('/property-management/projects')
                        "
                        @click="$emit('close')"
                    >
                        <Building2 class="h-5 w-5" />
                        <span>Property Management</span>
                    </RouterLink>

                    <!-- Client Management -->
                    <RouterLink
                        to="/client-management/clients"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass('/client-management/clients')
                        "
                        @click="$emit('close')"
                    >
                        <Users class="h-5 w-5" />
                        <span>Client Management</span>
                    </RouterLink>

                    <!-- Agent Management -->
                    <RouterLink
                        to="/agent-management/agents"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass('/agent-management/agents')
                        "
                        @click="$emit('close')"
                    >
                        <UserCog class="h-5 w-5" />
                        <span>Agent Management</span>
                    </RouterLink>

                    <!-- Sales Management -->
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isSalesRoute)"
                        :aria-expanded="openMenus.sales"
                        @click="toggleMenu('sales')"
                    >
                        <span class="flex items-center gap-3">
                            <FileSignature class="h-5 w-5" />
                            <span>Sales Management</span>
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition-transform duration-200"
                            :class="{
                                'rotate-180': openMenus.sales,
                            }"
                        />
                    </button>

                    <div
                        v-show="openMenus.sales"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            to="/sales-management/reservations"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass(
                                    '/sales-management/reservations'
                                )
                            "
                            @click="$emit('close')"
                        >
                            <FileSignature class="h-4 w-4" />
                            <span>Reservations</span>
                        </RouterLink>

                        <RouterLink
                            to="/sales-management/sales"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass('/sales-management/sales')
                            "
                            @click="$emit('close')"
                        >
                            <BadgeDollarSign class="h-4 w-4" />
                            <span>Sales</span>
                        </RouterLink>

                        <RouterLink
                            to="/sales-management/collections"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass(
                                    '/sales-management/collections'
                                )
                            "
                            @click="$emit('close')"
                        >
                            <Wallet class="h-4 w-4" />
                            <span>Collections</span>
                        </RouterLink>

                        <RouterLink
                            to="/sales-management/overdue-accounts"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass(
                                    '/sales-management/overdue-accounts'
                                )
                            "
                            @click="$emit('close')"
                        >
                            <AlertTriangle class="h-4 w-4" />
                            <span>Overdue Accounts</span>
                        </RouterLink>
                    </div>

                    <!-- Commission Management -->
                    <RouterLink
                        to="/reports/commission-payments"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass('/reports/commission-payments')
                        "
                        @click="$emit('close')"
                    >
                        <BadgeDollarSign class="h-5 w-5" />
                        <span>Commission Management</span>
                    </RouterLink>

                    <!-- Reports -->
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="menuButtonClass(isReportsRoute)"
                        :aria-expanded="openMenus.reports"
                        @click="toggleMenu('reports')"
                    >
                        <span class="flex items-center gap-3">
                            <BarChart3 class="h-5 w-5" />
                            <span>Reports</span>
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition-transform duration-200"
                            :class="{
                                'rotate-180': openMenus.reports,
                            }"
                        />
                    </button>

                    <div
                        v-show="openMenus.reports"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            to="/reports/dashboard"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass('/reports/dashboard')
                            "
                            @click="$emit('close')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            <span>Reports Dashboard</span>
                        </RouterLink>

                        <RouterLink
                            to="/reports/collections"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass('/reports/collections')
                            "
                            @click="$emit('close')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            <span>Collections Report</span>
                        </RouterLink>

                        <RouterLink
                            to="/reports/sales"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/sales')"
                            @click="$emit('close')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            <span>Sales Report</span>
                        </RouterLink>

                        <RouterLink
                            to="/reports/aging"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="linkClass('/reports/aging')"
                            @click="$emit('close')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            <span>Aging Report</span>
                        </RouterLink>

                        <RouterLink
                            to="/reports/agent-commissions"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass(
                                    '/reports/agent-commissions'
                                )
                            "
                            @click="$emit('close')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            <span>Agent Commission Report</span>
                        </RouterLink>

                        <RouterLink
                            to="/reports/commission-payments"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass(
                                    '/reports/commission-payments'
                                )
                            "
                            @click="$emit('close')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            <span>Commission Payment Report</span>
                        </RouterLink>

                        <RouterLink
                            to="/reports/agent-commission-ledger"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass(
                                    '/reports/agent-commission-ledger'
                                )
                            "
                            @click="$emit('close')"
                        >
                            <BarChart3 class="h-4 w-4" />
                            <span>Agent Commission Ledger</span>
                        </RouterLink>
                    </div>

                    <!-- Administration -->
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                        :class="
                            menuButtonClass(isAdministrationRoute)
                        "
                        :aria-expanded="openMenus.administration"
                        @click="toggleMenu('administration')"
                    >
                        <span class="flex items-center gap-3">
                            <Settings class="h-5 w-5" />
                            <span>Administration</span>
                        </span>

                        <ChevronDown
                            class="h-4 w-4 transition-transform duration-200"
                            :class="{
                                'rotate-180':
                                    openMenus.administration,
                            }"
                        />
                    </button>

                    <div
                        v-show="openMenus.administration"
                        class="ml-4 space-y-1 sm:ml-6"
                    >
                        <RouterLink
                            to="/administration/users"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass('/administration/users')
                            "
                            @click="$emit('close')"
                        >
                            <Users class="h-4 w-4" />
                            <span>Users</span>
                        </RouterLink>

                        <RouterLink
                            to="/administration/roles"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass('/administration/roles')
                            "
                            @click="$emit('close')"
                        >
                            <UserCog class="h-4 w-4" />
                            <span>Roles</span>
                        </RouterLink>

                        <RouterLink
                            to="/administration/permissions"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="
                                linkClass(
                                    '/administration/permissions'
                                )
                            "
                            @click="$emit('close')"
                        >
                            <Settings class="h-4 w-4" />
                            <span>Permissions</span>
                        </RouterLink>
                    </div>
                </nav>

                <!-- Sidebar footer -->
                <div class="border-t border-emerald-700 p-4">
                    <div class="text-xs text-emerald-200">
                        RPMCS v1.0.0
                    </div>

                    <div class="mt-1 text-xs text-emerald-300">
                        Property Management &amp; Collection System
                    </div>
                </div>
            </div>
        </aside>
    </div>
</template>