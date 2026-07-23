import {
    createRouter,
    createWebHistory,
} from "vue-router";

import { useAuthStore } from "@/stores/auth";

// Authentication
import LoginView from "@/views/auth/LoginView.vue";

// Dashboard
import DashboardView from "@/views/dashboard/DashboardView.vue";

// Property Management
import PropertyProjectsView from "@/views/property-management/PropertyProjectsView.vue";
import ProjectDetailsView from "@/views/property-management/ProjectDetailsView.vue";

// Sales Management
import ReservationsView from "@/views/sales-management/ReservationsView.vue";
import SalesView from "@/views/sales-management/SalesView.vue";
import CollectionsView from "@/views/sales-management/CollectionsView.vue";
import OverdueAccountsView from "@/views/sales-management/OverdueAccountsView.vue";

// Collection Management
import PaymentsView from "@/views/collection-management/PaymentsView.vue";
import ReceiptsView from "@/views/collection-management/ReceiptsView.vue";

// Commission Management
import CommissionsView from "@/views/commission-management/CommissionsView.vue";

// Reports
import ReportsDashboardView from "@/views/reports/ReportsDashboardView.vue";
import CollectionReportView from "@/views/reports/CollectionReportView.vue";
import SalesReportView from "@/views/reports/SaleReportView.vue";
import AgingReportView from "@/views/reports/AgingReportView.vue";
import AgentCommissionReportView from "@/views/reports/AgentCommissionReportView.vue";
import CommissionPaymentReportView from "@/views/reports/CommissionPaymentReportView.vue";
import AgentCommissionLedgerView from "@/views/reports/AgentCommissionLedgerView.vue";

// Agent Management
import AgentsView from "@/views/agent-management/AgentsView.vue";
import AgentDetailsView from "@/views/agent-management/AgentDetailsView.vue";

// Client Management
import ClientsView from "@/views/client-management/ClientsView.vue";
import ClientDetailsView from "@/views/client-management/ClientDetailsView.vue";
import ClientCreateView from "@/views/client-management/ClientCreateView.vue";
import ClientEditView from "@/views/client-management/ClientEditView.vue";

// Administration
import UsersView from "@/views/administration/UsersView.vue";
import RolesView from "@/views/administration/RolesView.vue";
import PermissionsView from "@/views/administration/PermissionsView.vue";

// Error Pages
import ForbiddenView from "@/views/errors/ForbiddenView.vue";
import NotFoundView from "@/views/errors/NotFoundView.vue";

/*
|--------------------------------------------------------------------------
| Application Permissions
|--------------------------------------------------------------------------
|
| These permission names must exactly match the permission names stored
| in your Laravel permissions table.
|
| Example:
|
| property.projects.view
| clients.view
| clients.create
| administration.users.view
|
| If your database uses different names, update the values below.
|
*/
export const PERMISSIONS = {
    /*
    |--------------------------------------------------------------------------
    | Property Management
    |--------------------------------------------------------------------------
    */
    PROPERTY_PROJECTS_VIEW:
        "property.projects.view",

    /*
    |--------------------------------------------------------------------------
    | Client Management
    |--------------------------------------------------------------------------
    */
    CLIENTS_VIEW:
        "clients.view",

    CLIENTS_CREATE:
        "clients.create",

    CLIENTS_EDIT:
        "clients.edit",

    /*
    |--------------------------------------------------------------------------
    | Agent Management
    |--------------------------------------------------------------------------
    */
    AGENTS_VIEW:
        "agents.view",

    /*
    |--------------------------------------------------------------------------
    | Sales Management
    |--------------------------------------------------------------------------
    */
    RESERVATIONS_VIEW:
        "sales.reservations.view",

    SALES_VIEW:
        "sales.view",

    COLLECTIONS_VIEW:
        "sales.collections.view",

    OVERDUE_ACCOUNTS_VIEW:
        "sales.overdue-accounts.view",

    /*
    |--------------------------------------------------------------------------
    | Collection Management
    |--------------------------------------------------------------------------
    */
    PAYMENTS_VIEW:
        "collections.payments.view",

    RECEIPTS_VIEW:
        "collections.receipts.view",

    RECEIPTS_PRINT:
    "collections.receipts.print",

    /*
    |--------------------------------------------------------------------------
    | Commission Management
    |--------------------------------------------------------------------------
    */
    COMMISSIONS_VIEW:
        "commissions.view",

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    REPORTS_DASHBOARD_VIEW:
        "reports.dashboard.view",

    REPORTS_COLLECTIONS_VIEW:
        "reports.collections.view",

    REPORTS_SALES_VIEW:
        "reports.sales.view",

    REPORTS_AGING_VIEW:
        "reports.aging.view",

    REPORTS_AGENT_COMMISSIONS_VIEW:
        "reports.agent-commissions.view",

    REPORTS_COMMISSION_PAYMENTS_VIEW:
        "reports.commission-payments.view",

    REPORTS_AGENT_COMMISSION_LEDGER_VIEW:
        "reports.agent-commission-ledger.view",

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */
    USERS_VIEW:
        "administration.users.view",

    ROLES_VIEW:
        "administration.roles.view",

    PERMISSIONS_VIEW:
        "administration.permissions.view",
};

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/
const routes = [
    /*
    |--------------------------------------------------------------------------
    | Root Redirect
    |--------------------------------------------------------------------------
    */
    {
        path: "/",

        redirect: {
            name: "dashboard",
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */
    {
        path: "/login",

        name: "login",

        component: LoginView,

        meta: {
            guest: true,

            title: "Login",
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    |
    | No permission is currently required for the dashboard.
    | Every authenticated user may access it.
    |
    */
    {
        path: "/dashboard",

        name: "dashboard",

        component: DashboardView,

        meta: {
            requiresAuth: true,

            title: "Dashboard",
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Property Management Routes
    |--------------------------------------------------------------------------
    */

    /*
    | Property project listing
    */
    {
        path: "/property-management/projects",

        name: "property-projects",

        component: PropertyProjectsView,

        meta: {
            requiresAuth: true,

            title: "Property Projects",

            permission:
                PERMISSIONS.PROPERTY_PROJECTS_VIEW,
        },
    },

    /*
    | Redirect old or grouped phase path to the projects page
    */
    {
        path: "/property-management/phases",

        redirect: {
            name: "property-projects",
        },
    },

    /*
    | Redirect old or grouped block path to the projects page
    */
    {
        path: "/property-management/blocks",

        redirect: {
            name: "property-projects",
        },
    },

    /*
    | Redirect old or grouped lot path to the projects page
    */
    {
        path: "/property-management/lots",

        redirect: {
            name: "property-projects",
        },
    },

    /*
    | Property project details
    |
    | This uses the same view permission as the project listing page.
    */
    {
        path: "/property-management/projects/:id",

        name: "property-project-details",

        component: ProjectDetailsView,

        meta: {
            requiresAuth: true,

            title: "Project Details",

            permission:
                PERMISSIONS.PROPERTY_PROJECTS_VIEW,
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Client Management Routes
    |--------------------------------------------------------------------------
    */

    /*
    | Client listing
    */
    {
        path: "/client-management/clients",

        name: "clients",

        component: ClientsView,

        meta: {
            requiresAuth: true,

            title: "Client Management",

            permission:
                PERMISSIONS.CLIENTS_VIEW,
        },
    },

    /*
    | Create client
    */
    {
        path: "/client-management/clients/create",

        name: "client-create",

        component: ClientCreateView,

        meta: {
            requiresAuth: true,

            title: "Add Client",

            permission:
                PERMISSIONS.CLIENTS_CREATE,
        },
    },

    /*
    | Edit client
    |
    | This route must appear before the dynamic client details route.
    */
    {
        path: "/client-management/clients/:id/edit",

        name: "client-edit",

        component: ClientEditView,

        meta: {
            requiresAuth: true,

            title: "Edit Client",

            permission:
                PERMISSIONS.CLIENTS_EDIT,
        },
    },

    /*
    | Client details
    |
    | The duplicate client-details route from the previous file was removed.
    */
    {
        path: "/client-management/clients/:id",

        name: "client-details",

        component: ClientDetailsView,

        meta: {
            requiresAuth: true,

            title: "Client Details",

            permission:
                PERMISSIONS.CLIENTS_VIEW,
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Agent Management Routes
    |--------------------------------------------------------------------------
    */

    /*
    | Agent listing
    */
    {
        path: "/agent-management/agents",

        name: "agents",

        component: AgentsView,

        meta: {
            requiresAuth: true,

            title: "Agents",

            permission:
                PERMISSIONS.AGENTS_VIEW,
        },
    },

    /*
    | Agent details
    |
    | This uses the same permission as the agent listing page.
    */
    {
        path: "/agent-management/agents/:id",

        name: "agent-details",

        component: AgentDetailsView,

        meta: {
            requiresAuth: true,

            title: "Agent Details",

            permission:
                PERMISSIONS.AGENTS_VIEW,
        },
    },
        /*
    |--------------------------------------------------------------------------
    | Sales Management Routes
    |--------------------------------------------------------------------------
    */

    /*
    | Reservations
    */
    {
        path: "/sales-management/reservations",

        name: "reservations",

        component: ReservationsView,

        meta: {
            requiresAuth: true,

            title: "Reservations",

            permission:
                PERMISSIONS.RESERVATIONS_VIEW,
        },
    },

    /*
    | Sales
    */
    {
        path: "/sales-management/sales",

        name: "sales",

        component: SalesView,

        meta: {
            requiresAuth: true,

            title: "Sales",

            permission:
                PERMISSIONS.SALES_VIEW,
        },
    },

    /*
    | Collections
    */
    {
        path: "/sales-management/collections",

        name: "collections",

        component: CollectionsView,

        meta: {
            requiresAuth: true,

            title: "Collections",

            permission:
                PERMISSIONS.COLLECTIONS_VIEW,
        },
    },

    /*
    | Overdue Accounts
    */
    {
        path: "/sales-management/overdue-accounts",

        name: "overdue-accounts",

        component: OverdueAccountsView,

        meta: {
            requiresAuth: true,

            title: "Overdue Accounts",

            permission:
                PERMISSIONS.OVERDUE_ACCOUNTS_VIEW,
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Collection Management
    |--------------------------------------------------------------------------
    */

    /*
    | Payments
    */
    {
        path: "/collection-management/payments",

        name: "payments",

        component: PaymentsView,

        meta: {
            requiresAuth: true,

            title: "Payments",

            permission:
                PERMISSIONS.PAYMENTS_VIEW,
        },
    },

    /*
    | Receipts
    */
    {
        path: "/collection-management/receipts",

        name: "receipts",

        component: ReceiptsView,

        meta: {
            requiresAuth: true,

            title: "Receipts",

            permission:
                PERMISSIONS.RECEIPTS_VIEW,
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Commission Management
    |--------------------------------------------------------------------------
    */

    /*
    | Commission List
    */
    {
        path: "/commission-management/commissions",

        name: "commissions",

        component: CommissionsView,

        meta: {
            requiresAuth: true,

            title: "Commissions",

            permission:
                PERMISSIONS.COMMISSIONS_VIEW,
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    /*
    | Reports Landing Page
    */
    {
        path: "/reports",

        redirect: {
            name: "reports.dashboard",
        },
    },

    /*
    | Reports Dashboard
    */
    {
        path: "/reports/dashboard",

        name: "reports.dashboard",

        component: ReportsDashboardView,

        meta: {
            requiresAuth: true,

            title: "Reports Dashboard",

            permission:
                PERMISSIONS.REPORTS_DASHBOARD_VIEW,
        },
    },

    /*
    | Collection Report
    */
    {
        path: "/reports/collections",

        name: "reports.collections",

        component: CollectionReportView,

        meta: {
            requiresAuth: true,

            title: "Collections Report",

            permission:
                PERMISSIONS.REPORTS_COLLECTIONS_VIEW,
        },
    },

    /*
    | Sales Report
    */
    {
        path: "/reports/sales",

        name: "reports.sales",

        component: SalesReportView,

        meta: {
            requiresAuth: true,

            title: "Sales Report",

            permission:
                PERMISSIONS.REPORTS_SALES_VIEW,
        },
    },

    /*
    | Aging Report
    */
    {
        path: "/reports/aging",

        name: "reports.aging",

        component: AgingReportView,

        meta: {
            requiresAuth: true,

            title: "Aging Report",

            permission:
                PERMISSIONS.REPORTS_AGING_VIEW,
        },
    },

    /*
    | Agent Commission Report
    */
    {
        path: "/reports/agent-commissions",

        name: "reports.agent-commissions",

        component:
            AgentCommissionReportView,

        meta: {
            requiresAuth: true,

            title:
                "Agent Commission Report",

            permission:
                PERMISSIONS.REPORTS_AGENT_COMMISSIONS_VIEW,
        },
    },

    /*
    | Commission Payment Report
    */
    {
        path: "/reports/commission-payments",

        name: "reports.commission-payments",

        component:
            CommissionPaymentReportView,

        meta: {
            requiresAuth: true,

            title:
                "Commission Payment Report",

            permission:
                PERMISSIONS.REPORTS_COMMISSION_PAYMENTS_VIEW,
        },
    },

    /*
    | Agent Commission Ledger
    */
    {
        path: "/reports/agent-commission-ledger",

        name: "reports.agent-commission-ledger",

        component:
            AgentCommissionLedgerView,

        meta: {
            requiresAuth: true,

            title:
                "Agent Commission Ledger",

            permission:
                PERMISSIONS.REPORTS_AGENT_COMMISSION_LEDGER_VIEW,
        },
    },

        /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */

    {
        path: "/administration/users",
        name: "users",
        component: UsersView,
        meta: {
            requiresAuth: true,
            title: "Users",
            permission:
                PERMISSIONS.USERS_VIEW,
        },
    },

    {
        path: "/administration/roles",
        name: "roles",
        component: RolesView,
        meta: {
            requiresAuth: true,
            title: "Roles",
            permission:
                PERMISSIONS.ROLES_VIEW,
        },
    },

    {
        path: "/administration/permissions",
        name: "permissions",
        component: PermissionsView,
        meta: {
            requiresAuth: true,
            title: "Permissions",
            permission:
                PERMISSIONS.PERMISSIONS_VIEW,
        },
    },

    /*
    |--------------------------------------------------------------------------
    | Error Pages
    |--------------------------------------------------------------------------
    */

    {
        path: "/403",
        name: "forbidden",
        component: ForbiddenView,
        meta: {
            requiresAuth: true,
            title: "Access Denied",
        },
    },

    {
        path: "/:pathMatch(.*)*",
        name: "not-found",
        component: NotFoundView,
        meta: {
            title: "Page Not Found",
        },
    },
];

/*
|--------------------------------------------------------------------------
| Router Instance
|--------------------------------------------------------------------------
*/
const router = createRouter({
    history: createWebHistory(),
    routes,
});

/*
|--------------------------------------------------------------------------
| Normalize roles and permissions
|--------------------------------------------------------------------------
|
| Supports:
|
| ["administration.users.view"]
|
| or:
|
| [{ name: "administration.users.view" }]
|
*/
const normalizeValues = (
    values = []
) => {
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

/*
|--------------------------------------------------------------------------
| Route permission checker
|--------------------------------------------------------------------------
*/
const hasRoutePermission = (
    authStore,
    requiredPermission
) => {
    if (!requiredPermission) {
        return true;
    }

    const user =
        authStore.user?.user ??
        authStore.user ??
        {};

    const roles = normalizeValues(
        user.roles
    );

    const permissions = normalizeValues(
        user.permissions
    );

    /*
    |--------------------------------------------------------------------------
    | Super Administrator bypass
    |--------------------------------------------------------------------------
    */
    const isSuperAdministrator =
        roles.includes(
            "super administrator"
        );

    if (isSuperAdministrator) {
        return true;
    }

    return permissions.includes(
        String(requiredPermission)
            .trim()
            .toLowerCase()
    );
};

/*
|--------------------------------------------------------------------------
| Global Navigation Guard
|--------------------------------------------------------------------------
*/
router.beforeEach((to) => {
    const authStore = useAuthStore();

    /*
    |--------------------------------------------------------------------------
    | Browser page title
    |--------------------------------------------------------------------------
    */
    document.title = to.meta.title
        ? `${to.meta.title} | RPMCS`
        : "RPMCS";

    /*
    |--------------------------------------------------------------------------
    | Protected route authentication check
    |--------------------------------------------------------------------------
    */
    if (
        to.meta.requiresAuth &&
        !authStore.isAuthenticated
    ) {
        return {
            name: "login",

            query: {
                redirect: to.fullPath,
            },
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent authenticated users from returning to login
    |--------------------------------------------------------------------------
    */
    if (
        to.meta.guest &&
        authStore.isAuthenticated
    ) {
        return {
            name: "dashboard",
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Route permission authorization check
    |--------------------------------------------------------------------------
    |
    | The route is blocked when:
    |
    | 1. The route declares meta.permission;
    | 2. The user is authenticated; and
    | 3. The user does not possess the required permission.
    |
    */
    if (
        to.meta.permission &&
        authStore.isAuthenticated &&
        !hasRoutePermission(
            authStore,
            to.meta.permission
        )
    ) {
        return {
            name: "forbidden",

            query: {
                from: to.fullPath,
            },
        };
    }

    return true;
});

export default router;