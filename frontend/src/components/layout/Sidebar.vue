<script setup>
/*
|--------------------------------------------------------------------------
| Admin Sidebar
|--------------------------------------------------------------------------
|
| This sidebar is intended for Super Administrator, Administrator, and
| permission-based staff accounts. Owner-specific navigation belongs in a
| separate SidebarOwner.vue component.
|
*/
import { computed, ref, watch } from "vue";
import {
    RouterLink,
    useRoute,
    useRouter,
} from "vue-router";
import { useAuthStore } from "@/stores/auth";

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
const router = useRouter();
const authStore = useAuthStore();

/*
|--------------------------------------------------------------------------
| Current authenticated user
|--------------------------------------------------------------------------
*/
const currentUser = computed(() => {
    return authStore.user?.user ?? authStore.user ?? {};
});

/*
|--------------------------------------------------------------------------
| Normalize roles and permissions
|--------------------------------------------------------------------------
*/
const normalizedValues = (values = []) => {
    if (!Array.isArray(values)) {
        return [];
    }

    return values
        .map((value) => {
            return typeof value === "string"
                ? value
                : value?.name;
        })
        .filter(Boolean)
        .map((value) =>
            String(value)
                .trim()
                .toLowerCase()
        );
};

const userRoles = computed(() =>
    normalizedValues(
        currentUser.value?.roles
    )
);

const userPermissions = computed(() =>
    normalizedValues(
        currentUser.value?.permissions
    )
);

const isSuperAdministrator = computed(() =>
    userRoles.value.includes(
        "super administrator"
    )
);

/*
|--------------------------------------------------------------------------
| Permission checking
|--------------------------------------------------------------------------
*/
const hasPermission = (permission) => {
    if (!permission) {
        return true;
    }

    if (isSuperAdministrator.value) {
        return true;
    }

    return userPermissions.value.includes(
        String(permission)
            .trim()
            .toLowerCase()
    );
};

/*
|--------------------------------------------------------------------------
| Read permission directly from router meta
|--------------------------------------------------------------------------
|
| The route names below must match the names in src/router/index.js.
|
*/
const getRoutePermission = (routeName) => {
    const matchedRoute = router
        .getRoutes()
        .find(
            (registeredRoute) =>
                registeredRoute.name ===
                routeName
        );

    return matchedRoute?.meta?.permission;
};

const canAccessRoute = (routeName) => {
    return hasPermission(
        getRoutePermission(routeName)
    );
};

/*
|--------------------------------------------------------------------------
| Standalone navigation access
|--------------------------------------------------------------------------
*/
const canViewDashboard = computed(() =>
    canAccessRoute("dashboard")
);

const canViewPropertyManagement =
    computed(() =>
        canAccessRoute(
            "property-projects"
        )
    );

const canViewClientManagement =
    computed(() =>
        canAccessRoute("clients")
    );

const canViewAgentManagement =
    computed(() =>
        canAccessRoute("agents")
    );

const canViewCommissionManagement =
    computed(() =>
        canAccessRoute(
            "commissions"
        )
    );

const canViewReceiptPrinting =
    computed(() =>
        canAccessRoute("receipts")
    );

/*
|--------------------------------------------------------------------------
| Sales navigation access
|--------------------------------------------------------------------------
*/
const canViewReservations = computed(() =>
    canAccessRoute("reservations")
);

const canViewSales = computed(() =>
    canAccessRoute("sales")
);

const canViewCollections = computed(() =>
    canAccessRoute("collections")
);

const canViewOverdueAccounts =
    computed(() =>
        canAccessRoute(
            "overdue-accounts"
        )
    );

const canViewSalesManagement =
    computed(() =>
        canViewReservations.value ||
        canViewSales.value ||
        canViewCollections.value ||
        canViewOverdueAccounts.value
    );

/*
|--------------------------------------------------------------------------
| Report navigation access
|--------------------------------------------------------------------------
*/
const canViewReportsDashboard =
    computed(() =>
        canAccessRoute(
            "reports.dashboard"
        )
    );

const canViewCollectionReport =
    computed(() =>
        canAccessRoute(
            "reports.collections"
        )
    );

const canViewSalesReport = computed(() =>
    canAccessRoute("reports.sales")
);

const canViewAgingReport = computed(() =>
    canAccessRoute("reports.aging")
);

const canViewAgentCommissionReport =
    computed(() =>
        canAccessRoute(
            "reports.agent-commissions"
        )
    );

const canViewCommissionPaymentReport =
    computed(() =>
        canAccessRoute(
            "reports.commission-payments"
        )
    );

const canViewAgentCommissionLedger =
    computed(() =>
        canAccessRoute(
            "reports.agent-commission-ledger"
        )
    );

const canViewReports = computed(() => {
    return (
        canViewReportsDashboard.value ||
        canViewCollectionReport.value ||
        canViewSalesReport.value ||
        canViewAgingReport.value ||
        canViewAgentCommissionReport.value ||
        canViewCommissionPaymentReport.value ||
        canViewAgentCommissionLedger.value
    );
});

/*
|--------------------------------------------------------------------------
| Administration navigation access
|--------------------------------------------------------------------------
*/
const canViewUsers = computed(() =>
    canAccessRoute("users")
);

const canViewRoles = computed(() =>
    canAccessRoute("roles")
);

const canViewPermissions = computed(() =>
    canAccessRoute("permissions")
);

const canViewAdministration =
    computed(() =>
        canViewUsers.value ||
        canViewRoles.value ||
        canViewPermissions.value
    );

/*
|--------------------------------------------------------------------------
| Active route helpers
|--------------------------------------------------------------------------
*/
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
    route.path.startsWith(
        "/sales-management"
    )
);

const isReportsRoute = computed(() =>
    route.path.startsWith("/reports")
);

const isAdministrationRoute =
    computed(() =>
        route.path.startsWith(
            "/administration"
        )
    );

/*
|--------------------------------------------------------------------------
| Dropdown state
|--------------------------------------------------------------------------
*/
const openMenus = ref({
    sales: false,
    reports: false,
    administration: false,
});

const syncOpenMenusWithRoute = () => {
    if (
        isSalesRoute.value &&
        canViewSalesManagement.value
    ) {
        openMenus.value.sales = true;
    }

    if (
        isReportsRoute.value &&
        canViewReports.value
    ) {
        openMenus.value.reports = true;
    }

    if (
        isAdministrationRoute.value &&
        canViewAdministration.value
    ) {
        openMenus.value.administration =
            true;
    }
};

watch(
    () => route.path,
    syncOpenMenusWithRoute,
    {
        immediate: true,
    }
);

const toggleMenu = (menu) => {
    openMenus.value[menu] =
        !openMenus.value[menu];
};

/*
|--------------------------------------------------------------------------
| Styling helpers
|--------------------------------------------------------------------------
*/
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
            :class="
                open
                    ? 'translate-x-0'
                    : '-translate-x-full'
            "
        >
            <div class="flex h-full flex-col">
                <!-- Sidebar header -->
                <div
                    class="border-b border-emerald-700 p-6"
                >
                    <div
                        class="flex items-center justify-between"
                    >
                        <div
                            class="flex items-center gap-3"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-emerald-700"
                            >
                                <Building2
                                    class="h-7 w-7"
                                />
                            </div>

                            <div>
                                <h1
                                    class="text-xl font-bold"
                                >
                                    RPMCS
                                </h1>

                                <p
                                    class="text-xs text-emerald-200"
                                >
                                    Property
                                    Management
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
                <nav
                    class="flex-1 space-y-2 overflow-y-auto p-4 text-sm"
                >
                    <!-- Dashboard -->
                    <RouterLink
                        v-if="canViewDashboard"
                        to="/dashboard"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass('/dashboard')
                        "
                        @click="$emit('close')"
                    >
                        <LayoutDashboard
                            class="h-5 w-5"
                        />
                        <span>Dashboard</span>
                    </RouterLink>

                    <!-- Property Management -->
                    <RouterLink
                        v-if="
                            canViewPropertyManagement
                        "
                        to="/property-management/projects"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass(
                                '/property-management/projects'
                            )
                        "
                        @click="$emit('close')"
                    >
                        <Building2
                            class="h-5 w-5"
                        />
                        <span>
                            Property Management
                        </span>
                    </RouterLink>

                    <!-- Client Management -->
                    <RouterLink
                        v-if="
                            canViewClientManagement
                        "
                        to="/client-management/clients"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass(
                                '/client-management/clients'
                            )
                        "
                        @click="$emit('close')"
                    >
                        <Users class="h-5 w-5" />
                        <span>
                            Client Management
                        </span>
                    </RouterLink>

                    <!-- Agent Management -->
                    <RouterLink
                        v-if="
                            canViewAgentManagement
                        "
                        to="/agent-management/agents"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass(
                                '/agent-management/agents'
                            )
                        "
                        @click="$emit('close')"
                    >
                        <UserCog
                            class="h-5 w-5"
                        />
                        <span>
                            Agent Management
                        </span>
                    </RouterLink>

                    <!-- Sales Management -->
                    <template
                        v-if="
                            canViewSalesManagement
                        "
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                            :class="
                                menuButtonClass(
                                    isSalesRoute
                                )
                            "
                            :aria-expanded="
                                openMenus.sales
                            "
                            @click="
                                toggleMenu('sales')
                            "
                        >
                            <span
                                class="flex items-center gap-3"
                            >
                                <FileSignature
                                    class="h-5 w-5"
                                />
                                <span>
                                    Sales Management
                                </span>
                            </span>

                            <ChevronDown
                                class="h-4 w-4 transition-transform duration-200"
                                :class="{
                                    'rotate-180':
                                        openMenus.sales,
                                }"
                            />
                        </button>

                        <div
                            v-show="
                                openMenus.sales
                            "
                            class="ml-4 space-y-1 sm:ml-6"
                        >
                            <RouterLink
                                v-if="
                                    canViewReservations
                                "
                                to="/sales-management/reservations"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/sales-management/reservations'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <FileSignature
                                    class="h-4 w-4"
                                />
                                <span>
                                    Reservations
                                </span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewSales
                                "
                                to="/sales-management/sales"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/sales-management/sales'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <BadgeDollarSign
                                    class="h-4 w-4"
                                />
                                <span>Sales</span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewCollections
                                "
                                to="/sales-management/collections"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/sales-management/collections'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <Wallet
                                    class="h-4 w-4"
                                />
                                <span>
                                    Collections
                                </span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewOverdueAccounts
                                "
                                to="/sales-management/overdue-accounts"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/sales-management/overdue-accounts'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <AlertTriangle
                                    class="h-4 w-4"
                                />
                                <span>
                                    Overdue Accounts
                                </span>
                            </RouterLink>
                        </div>
                    </template>

                    <!-- Commission Management -->
                    <RouterLink
                        v-if="
                            canViewCommissionManagement
                        "
                        to="/commission-management/commissions"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass(
                                '/commission-management/commissions'
                            )
                        "
                        @click="$emit('close')"
                    >
                        <BadgeDollarSign
                            class="h-5 w-5"
                        />
                        <span>
                            Commission Management
                        </span>
                    </RouterLink>

                    <!-- Print Receipts -->
                    <RouterLink
                        v-if="
                            canViewReceiptPrinting
                        "
                        to="/collection-management/receipts"
                        class="flex items-center gap-3 rounded-lg px-4 py-3 transition"
                        :class="
                            linkClass(
                                '/collection-management/receipts'
                            )
                        "
                        @click="$emit('close')"
                    >
                        <FileSignature
                            class="h-5 w-5"
                        />
                        <span>
                            Print Receipts
                        </span>
                    </RouterLink>

                    <!-- Reports -->
                    <template
                        v-if="canViewReports"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                            :class="
                                menuButtonClass(
                                    isReportsRoute
                                )
                            "
                            :aria-expanded="
                                openMenus.reports
                            "
                            @click="
                                toggleMenu(
                                    'reports'
                                )
                            "
                        >
                            <span
                                class="flex items-center gap-3"
                            >
                                <BarChart3
                                    class="h-5 w-5"
                                />
                                <span>Reports</span>
                            </span>

                            <ChevronDown
                                class="h-4 w-4 transition-transform duration-200"
                                :class="{
                                    'rotate-180':
                                        openMenus.reports,
                                }"
                            />
                        </button>

                        <div
                            v-show="
                                openMenus.reports
                            "
                            class="ml-4 space-y-1 sm:ml-6"
                        >
                            <RouterLink
                                v-if="
                                    canViewReportsDashboard
                                "
                                to="/reports/dashboard"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/reports/dashboard'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <BarChart3
                                    class="h-4 w-4"
                                />
                                <span>
                                    Reports Dashboard
                                </span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewCollectionReport
                                "
                                to="/reports/collections"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/reports/collections'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <BarChart3
                                    class="h-4 w-4"
                                />
                                <span>
                                    Collections Report
                                </span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewSalesReport
                                "
                                to="/reports/sales"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/reports/sales'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <BarChart3
                                    class="h-4 w-4"
                                />
                                <span>
                                    Sales Report
                                </span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewAgingReport
                                "
                                to="/reports/aging"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/reports/aging'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <BarChart3
                                    class="h-4 w-4"
                                />
                                <span>
                                    Aging Report
                                </span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewAgentCommissionReport
                                "
                                to="/reports/agent-commissions"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/reports/agent-commissions'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <BarChart3
                                    class="h-4 w-4"
                                />
                                <span>
                                    Agent Commission
                                    Report
                                </span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewCommissionPaymentReport
                                "
                                to="/reports/commission-payments"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/reports/commission-payments'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <BarChart3
                                    class="h-4 w-4"
                                />
                                <span>
                                    Commission Payment
                                    Report
                                </span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewAgentCommissionLedger
                                "
                                to="/reports/agent-commission-ledger"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/reports/agent-commission-ledger'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <BarChart3
                                    class="h-4 w-4"
                                />
                                <span>
                                    Agent Commission
                                    Ledger
                                </span>
                            </RouterLink>
                        </div>
                    </template>

                    <!-- Administration -->
                    <template
                        v-if="
                            canViewAdministration
                        "
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-lg px-4 py-3 transition"
                            :class="
                                menuButtonClass(
                                    isAdministrationRoute
                                )
                            "
                            :aria-expanded="
                                openMenus.administration
                            "
                            @click="
                                toggleMenu(
                                    'administration'
                                )
                            "
                        >
                            <span
                                class="flex items-center gap-3"
                            >
                                <Settings
                                    class="h-5 w-5"
                                />
                                <span>
                                    Administration
                                </span>
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
                            v-show="
                                openMenus.administration
                            "
                            class="ml-4 space-y-1 sm:ml-6"
                        >
                            <RouterLink
                                v-if="
                                    canViewUsers
                                "
                                to="/administration/users"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/administration/users'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <Users
                                    class="h-4 w-4"
                                />
                                <span>Users</span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewRoles
                                "
                                to="/administration/roles"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/administration/roles'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <UserCog
                                    class="h-4 w-4"
                                />
                                <span>Roles</span>
                            </RouterLink>

                            <RouterLink
                                v-if="
                                    canViewPermissions
                                "
                                to="/administration/permissions"
                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                                :class="
                                    linkClass(
                                        '/administration/permissions'
                                    )
                                "
                                @click="
                                    $emit('close')
                                "
                            >
                                <Settings
                                    class="h-4 w-4"
                                />
                                <span>
                                    Permissions
                                </span>
                            </RouterLink>
                        </div>
                    </template>
                </nav>

                <!-- Sidebar footer -->
                <div
                    class="border-t border-emerald-700 p-4"
                >
                    <div
                        class="text-xs text-emerald-200"
                    >
                        RPMCS v1.0.0
                    </div>

                    <div
                        class="mt-1 text-xs text-emerald-300"
                    >
                        Property Management
                        &amp; Collection
                        System
                    </div>
                </div>
            </div>
        </aside>
    </div>
</template>
