import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

import LoginView from "@/views/auth/LoginView.vue";
import DashboardView from "@/views/dashboard/DashboardView.vue";

import PropertyProjectsView from "@/views/property-management/PropertyProjectsView.vue";
import ProjectDetailsView from "@/views/property-management/ProjectDetailsView.vue";

import ClientsView from "@/views/client-management/ClientsView.vue";
import AgentsView from "@/views/agent-management/AgentsView.vue";

import ReservationsView from "@/views/sales-management/ReservationsView.vue";
import SalesView from "@/views/sales-management/SalesView.vue";
import CollectionsView from "@/views/sales-management/CollectionsView.vue";
import OverdueAccountsView from "@/views/sales-management/OverdueAccountsView.vue";

import PaymentsView from "@/views/collection-management/PaymentsView.vue";
import ReceiptsView from "@/views/collection-management/ReceiptsView.vue";

import CommissionsView from "@/views/commission-management/CommissionsView.vue";

import ReportsView from "@/views/reports/ReportsView.vue";
import ReportsDashboardView from "@/views/reports/ReportsDashboardView.vue";
import CollectionReportView from "@/views/reports/CollectionReportView.vue";
import SalesReportView from "@/views/reports/SaleReportView.vue";
import AgingReportView from "@/views/reports/AgingReportView.vue";
import AgentCommissionReportView from "@/views/reports/AgentCommissionReportView.vue";
import CommissionPaymentReportView from "@/views/reports/CommissionPaymentReportView.vue";
import AgentCommissionLedgerView from "@/views/reports/AgentCommissionLedgerView.vue";

import UsersView from "@/views/administration/UsersView.vue";
import RolesView from "@/views/administration/RolesView.vue";
import PermissionsView from "@/views/administration/PermissionsView.vue";

const router = createRouter({
    history: createWebHistory(),

    routes: [
        {
            path: "/",
            redirect: "/dashboard",
        },

        {
            path: "/login",
            name: "login",
            component: LoginView,
            meta: {
                guest: true,
                title: "Login",
            },
        },

        {
            path: "/dashboard",
            name: "dashboard",
            component: DashboardView,
            meta: {
                requiresAuth: true,
                title: "Dashboard",
            },
        },

        {
            path: "/property-management/projects",
            name: "property-projects",
            component: PropertyProjectsView,
            meta: {
                requiresAuth: true,
                title: "Property Projects",
            },
        },

        {
            path: "/property-management/phases",
            redirect: "/property-management/projects",
        },

        {
            path: "/property-management/blocks",
            redirect: "/property-management/projects",
        },

        {
            path: "/property-management/lots",
            redirect: "/property-management/projects",
        },

        {
            path: "/property-management/projects/:id",
            name: "property-project-details",
            component: ProjectDetailsView,
            meta: {
                requiresAuth: true,
                title: "Project Details",
            },
        },

        {
            path: "/client-management/clients",
            name: "clients",
            component: ClientsView,
            meta: {
                requiresAuth: true,
                title: "Clients",
            },
        },

        {
            path: "/agent-management/agents",
            name: "agents",
            component: AgentsView,
            meta: {
                requiresAuth: true,
                title: "Agents",
            },
        },

        {
            path: "/sales-management/reservations",
            name: "reservations",
            component: ReservationsView,
            meta: {
                requiresAuth: true,
                title: "Reservations",
            },
        },

        {
            path: "/sales-management/sales",
            name: "sales",
            component: SalesView,
            meta: {
                requiresAuth: true,
                title: "Sales",
            },
        },

        {
            path: "/sales-management/collections",
            name: "collections",
            component: CollectionsView,
            meta: {
                requiresAuth: true,
                title: "Collections",
            },
        },

        {
            path: "/sales-management/overdue-accounts",
            name: "overdue-accounts",
            component: OverdueAccountsView,
            meta: {
                requiresAuth: true,
                title: "Overdue Accounts",
            },
        },

        {
            path: "/collection-management/payments",
            name: "payments",
            component: PaymentsView,
            meta: {
                requiresAuth: true,
                title: "Payments",
            },
        },

        {
            path: "/collection-management/receipts",
            name: "receipts",
            component: ReceiptsView,
            meta: {
                requiresAuth: true,
                title: "Receipts",
            },
        },

        {
            path: "/commission-management/commissions",
            name: "commissions",
            component: CommissionsView,
            meta: {
                requiresAuth: true,
                title: "Commissions",
            },
        },

        {
            path: "/reports",
            redirect: "/reports/dashboard",
        },

        {
            path: "/reports/dashboard",
            name: "reports.dashboard",
            component: ReportsDashboardView,
            meta: {
                requiresAuth: true,
                title: "Reports Dashboard",
            },
        },

        {
            path: "/reports/collections",
            name: "reports.collections",
            component: CollectionReportView,
            meta: {
                requiresAuth: true,
                title: "Collections Report",
            },
        },

        {
            path: "/reports/sales",
            name: "reports.sales",
            component: SalesReportView,
            meta: {
                requiresAuth: true,
                title: "Sales Report",
            },
        },

        {
            path: "/reports/aging",
            name: "reports.aging",
            component: AgingReportView,
            meta: {
                requiresAuth: true,
                title: "Aging Report",
            },
        },

        {
            path: "/reports/agent-commissions",
            name: "reports.agent-commissions",
            component: AgentCommissionReportView,
            meta: {
                requiresAuth: true,
                title: "Agent Commission Report",
            },
        },

        {
            path: "/reports/commission-payments",
            name: "reports.commission-payments",
            component: CommissionPaymentReportView,
            meta: {
                requiresAuth: true,
                title: "Commission Payment Report",
            },
        },

        {
            path: "/reports/agent-commission-ledger",
            name: "reports.agent-commission-ledger",
            component: AgentCommissionLedgerView,
            meta: {
                requiresAuth: true,
                title: "Agent Commission Ledger",
            },
        },

        {
            path: "/administration/users",
            name: "users",
            component: UsersView,
            meta: {
                requiresAuth: true,
                title: "Users",
            },
        },

        {
            path: "/administration/roles",
            name: "roles",
            component: RolesView,
            meta: {
                requiresAuth: true,
                title: "Roles",
            },
        },

        {
            path: "/administration/permissions",
            name: "permissions",
            component: PermissionsView,
            meta: {
                requiresAuth: true,
                title: "Permissions",
            },
        },
    ],
});

router.beforeEach((to) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return {
            name: "login",
        };
    }

    if (to.meta.guest && authStore.isAuthenticated) {
        return {
            name: "dashboard",
        };
    }

    if (to.meta.title) {
        document.title = `${to.meta.title} | RPMCS`;
    }
});

export default router;