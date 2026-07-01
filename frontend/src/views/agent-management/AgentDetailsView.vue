<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";

import agentService from "@/services/agentService";
import toast from "@/utils/toast";

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const agent = ref(null);
const summary = ref({});
const sales = ref([]);
const payments = ref([]);
const deletedPayments = ref([]);
const subAgents = ref([]);

const activeTab = ref("dashboard");

const tabs = [
    { key: "dashboard", label: "Dashboard" },
    { key: "profile", label: "Profile" },
    { key: "sales", label: "Sales" },
    { key: "commissions", label: "Commissions" },
    { key: "payments", label: "Payments" },
    { key: "sub_agents", label: "Sub-Agents" },
];

const agentId = computed(() => route.params.id);

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

const date = (value) => {
    if (!value) return "—";

    return new Date(value).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    });
};

const fullName = (person) => {
    if (!person) return "—";

    return [
        person.first_name,
        person.middle_name,
        person.last_name,
        person.suffix,
    ]
        .filter(Boolean)
        .join(" ");
};

const commissionStatus = (sale) => {
    const earned = Number(sale.commission_earned || 0);
    const paid = Number(sale.commission_paid || 0);

    if (earned <= 0) return "No Commission";
    if (paid <= 0) return "Unpaid";
    if (paid >= earned) return "Fully Paid";

    return "Partially Paid";
};

const commissionStatusClass = (sale) => {
    const status = commissionStatus(sale);

    if (status === "Fully Paid") {
        return "bg-emerald-100 text-emerald-700";
    }

    if (status === "Partially Paid") {
        return "bg-amber-100 text-amber-700";
    }

    if (status === "Unpaid") {
        return "bg-red-100 text-red-700";
    }

    return "bg-slate-100 text-slate-600";
};

const loadAgent = async () => {
    loading.value = true;

    try {
        const response = await agentService.getAgent(agentId.value);

        agent.value = response.data.data ?? null;
        summary.value = response.data.summary ?? {};
        sales.value = response.data.sales ?? [];
        payments.value = response.data.payments ?? [];
        deletedPayments.value = response.data.deleted_payments ?? [];
        subAgents.value = response.data.sub_agents ?? [];
    } catch (error) {
        console.error(error);
        toast.error("Failed to load agent details.");
    } finally {
        loading.value = false;
    }
};

const goBack = () => {
    router.push("/agent-management/agents");
};

const goToLedger = () => {
    router.push({
        path: "/reports/agent-commission-ledger",
        query: {
            agent_id: agentId.value,
        },
    });
};

onMounted(() => {
    loadAgent();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <PageHeader
                    title="Agent Details"
                    description="View agent profile, sales, commissions, payments, and sub-agents."
                />

                <div class="flex gap-2">
                    <button
                        @click="goToLedger"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                    >
                        View Ledger
                    </button>

                    <button
                        @click="goBack"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Back
                    </button>
                </div>
            </div>

            <TableSkeleton v-if="loading" />

            <div
                v-else-if="agent"
                class="space-y-6"
            >
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">
                                {{ fullName(agent) }}
                            </h1>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ agent.agent_code || "—" }}
                                ·
                                {{ agent.agent_type?.replace("_", " ") || "—" }}
                            </p>
                        </div>

                        <span
                            class="w-fit rounded-full px-3 py-1 text-xs font-semibold capitalize"
                            :class="
                                agent.status === 'active'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-slate-100 text-slate-600'
                            "
                        >
                            {{ agent.status || "—" }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Total Sales
                        </p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">
                            {{ summary.total_sales || 0 }}
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Commission Earned
                        </p>
                        <p class="mt-2 font-bold text-emerald-700">
                            {{ money(summary.total_commission_earned) }}
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Commission Paid
                        </p>
                        <p class="mt-2 font-bold text-blue-700">
                            {{ money(summary.total_commission_paid) }}
                        </p>
                    </div>

                    <div class="rounded-xl border bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Commission Balance
                        </p>
                        <p class="mt-2 font-bold text-red-700">
                            {{ money(summary.total_commission_balance) }}
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6">
                        <nav class="flex gap-6 overflow-x-auto">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                @click="activeTab = tab.key"
                                class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium"
                                :class="
                                    activeTab === tab.key
                                        ? 'border-emerald-600 text-emerald-700'
                                        : 'border-transparent text-slate-500 hover:text-slate-700'
                                "
                            >
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <div
                            v-if="activeTab === 'dashboard'"
                            class="space-y-6"
                        >
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase text-slate-500">
                                        Total Contract Price
                                    </p>
                                    <p class="mt-2 font-bold text-slate-900">
                                        {{ money(summary.total_contract_price) }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase text-slate-500">
                                        Downpayment
                                    </p>
                                    <p class="mt-2 font-bold text-slate-900">
                                        {{ money(summary.total_downpayment) }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase text-slate-500">
                                        Sale Balance
                                    </p>
                                    <p class="mt-2 font-bold text-red-700">
                                        {{ money(summary.total_sale_balance) }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-semibold uppercase text-slate-500">
                                        Sub-Agents
                                    </p>
                                    <p class="mt-2 text-2xl font-bold text-slate-900">
                                        {{ summary.sub_agents_count || 0 }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-6 xl:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 p-5">
                                    <h3 class="font-semibold text-slate-900">
                                        Agent Profile Summary
                                    </h3>

                                    <div class="mt-4 space-y-3 text-sm">
                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-slate-500">
                                                Agent Type
                                            </span>
                                            <span class="font-semibold capitalize">
                                                {{ agent.agent_type?.replace("_", " ") || "—" }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-slate-500">
                                                Main Agent
                                            </span>
                                            <span class="font-semibold">
                                                {{ fullName(agent.main_agent) }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-slate-500">
                                                Commission Rate
                                            </span>
                                            <span class="font-semibold text-emerald-700">
                                                {{ agent.default_commission_rate || 0 }}%
                                            </span>
                                        </div>

                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-slate-500">
                                                Contact
                                            </span>
                                            <span class="font-semibold">
                                                {{ agent.contact_number || "—" }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between">
                                            <span class="text-slate-500">
                                                Email
                                            </span>
                                            <span class="font-semibold">
                                                {{ agent.email || "—" }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-5">
                                    <h3 class="font-semibold text-slate-900">
                                        Commission Health
                                    </h3>

                                    <div class="mt-4 space-y-3 text-sm">
                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-slate-500">
                                                Earned
                                            </span>
                                            <span class="font-semibold text-emerald-700">
                                                {{ money(summary.total_commission_earned) }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-slate-500">
                                                Paid
                                            </span>
                                            <span class="font-semibold text-blue-700">
                                                {{ money(summary.total_commission_paid) }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between border-b pb-2">
                                            <span class="text-slate-500">
                                                Deleted / Voided
                                            </span>
                                            <span class="font-semibold text-orange-700">
                                                {{ money(summary.total_commission_deleted) }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between">
                                            <span class="text-slate-500">
                                                Balance
                                            </span>
                                            <span class="font-bold text-red-700">
                                                {{ money(summary.total_commission_balance) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else-if="activeTab === 'profile'"
                            class="grid gap-4 md:grid-cols-2"
                        >
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Agent Code
                                </p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ agent.agent_code || "—" }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Agent Type
                                </p>
                                <p class="mt-1 font-semibold capitalize text-slate-900">
                                    {{ agent.agent_type?.replace("_", " ") || "—" }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Main Agent
                                </p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ fullName(agent.main_agent) }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Commission Rate
                                </p>
                                <p class="mt-1 font-semibold text-emerald-700">
                                    {{ agent.default_commission_rate || 0 }}%
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Contact Number
                                </p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ agent.contact_number || "—" }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Email
                                </p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ agent.email || "—" }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4 md:col-span-2">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Address
                                </p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ agent.address || "—" }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    License Number
                                </p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ agent.license_number || "—" }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Sub-Agents
                                </p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ summary.sub_agents_count || 0 }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-else-if="activeTab === 'sales'"
                            class="overflow-x-auto"
                        >
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                            Sale
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                            Client / Property
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                            Contract
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                                            Commission Status
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                                            Sale Status
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="sale in sales"
                                        :key="sale.sale_id"
                                    >
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-900">
                                                {{ sale.sale_no }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ date(sale.sale_date) }}
                                            </p>
                                        </td>

                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-900">
                                                {{ fullName(sale.client) }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ sale.project?.project_name || "—" }}
                                                /
                                                {{ sale.lot?.lot_no || "—" }}
                                            </p>
                                        </td>

                                        <td class="px-4 py-4 text-right font-semibold">
                                            {{ money(sale.contract_price) }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                                :class="commissionStatusClass(sale)"
                                            >
                                                {{ commissionStatus(sale) }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 text-center capitalize">
                                            {{ sale.status || "—" }}
                                        </td>
                                    </tr>

                                    <tr v-if="sales.length === 0">
                                        <td
                                            colspan="5"
                                            class="px-4 py-8 text-center text-sm text-slate-500"
                                        >
                                            No sales found for this agent.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-else-if="activeTab === 'commissions'"
                            class="overflow-x-auto"
                        >
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                            Sale
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                                            Rate
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                            Earned
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                            Paid
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                            Deleted
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                            Balance
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                                            Status
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="sale in sales"
                                        :key="sale.sale_id"
                                    >
                                        <td class="px-4 py-4 font-semibold">
                                            {{ sale.sale_no }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{ sale.commission_rate }}%
                                        </td>

                                        <td class="px-4 py-4 text-right font-semibold text-emerald-700">
                                            {{ money(sale.commission_earned) }}
                                        </td>

                                        <td class="px-4 py-4 text-right font-semibold text-blue-700">
                                            {{ money(sale.commission_paid) }}
                                        </td>

                                        <td class="px-4 py-4 text-right font-semibold text-red-700">
                                            {{ money(sale.commission_deleted) }}
                                        </td>

                                        <td class="px-4 py-4 text-right font-bold">
                                            {{ money(sale.commission_balance) }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                                :class="commissionStatusClass(sale)"
                                            >
                                                {{ commissionStatus(sale) }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr v-if="sales.length === 0">
                                        <td
                                            colspan="7"
                                            class="px-4 py-8 text-center text-sm text-slate-500"
                                        >
                                            No commission records found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-else-if="activeTab === 'payments'"
                            class="space-y-6"
                        >
                            <div class="overflow-x-auto">
                                <h3 class="mb-3 font-semibold text-slate-900">
                                    Active Payments
                                </h3>

                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                                Date
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                                Sale
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                                Amount
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                                Method / Reference
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                                Encoded By
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="payment in payments"
                                            :key="payment.id"
                                        >
                                            <td class="px-4 py-4">
                                                {{ date(payment.payment_date) }}
                                            </td>

                                            <td class="px-4 py-4">
                                                {{ payment.sale?.sale_no || "—" }}
                                            </td>

                                            <td class="px-4 py-4 text-right font-bold text-emerald-700">
                                                {{ money(payment.amount) }}
                                            </td>

                                            <td class="px-4 py-4">
                                                <p class="capitalize">
                                                    {{ payment.payment_method?.replace("_", " ") || "—" }}
                                                </p>
                                                <p class="text-xs text-slate-500">
                                                    {{ payment.reference_no || "—" }}
                                                </p>
                                            </td>

                                            <td class="px-4 py-4">
                                                {{ payment.created_by?.name || payment.created_by?.email || "—" }}
                                            </td>
                                        </tr>

                                        <tr v-if="payments.length === 0">
                                            <td
                                                colspan="5"
                                                class="px-4 py-8 text-center text-sm text-slate-500"
                                            >
                                                No active payments found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="overflow-x-auto">
                                <h3 class="mb-3 font-semibold text-red-700">
                                    Deleted / Voided Payments
                                </h3>

                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                                Deleted At
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                                Sale
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                                Amount
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                                Reason
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                                Deleted By
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="payment in deletedPayments"
                                            :key="payment.id"
                                        >
                                            <td class="px-4 py-4">
                                                {{ date(payment.deleted_at) }}
                                            </td>

                                            <td class="px-4 py-4">
                                                {{ payment.sale?.sale_no || "—" }}
                                            </td>

                                            <td class="px-4 py-4 text-right font-bold text-red-700">
                                                {{ money(payment.amount) }}
                                            </td>

                                            <td class="px-4 py-4">
                                                {{ payment.delete_reason || "—" }}
                                            </td>

                                            <td class="px-4 py-4">
                                                {{ payment.deleted_by?.name || payment.deleted_by?.email || "—" }}
                                            </td>
                                        </tr>

                                        <tr v-if="deletedPayments.length === 0">
                                            <td
                                                colspan="5"
                                                class="px-4 py-8 text-center text-sm text-slate-500"
                                            >
                                                No deleted payments found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div
                            v-else-if="activeTab === 'sub_agents'"
                            class="overflow-x-auto"
                        >
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                            Sub-Agent
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                            Contact
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                                            Rate
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                                            Status
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="subAgent in subAgents"
                                        :key="subAgent.id"
                                    >
                                        <td class="px-4 py-4">
                                            <p class="font-semibold">
                                                {{ fullName(subAgent) }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ subAgent.agent_code || "—" }}
                                            </p>
                                           
                                        </td>

                                        <td class="px-4 py-4">
                                            <p>{{ subAgent.contact_number || "—" }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ subAgent.email || "—" }}
                                            </p>
                                        </td>

                                        <td class="px-4 py-4 text-center font-semibold text-emerald-700">
                                            {{ subAgent.default_commission_rate || 0 }}%
                                        </td>

                                        <td class="px-4 py-4 text-center capitalize">
                                            {{ subAgent.status || "—" }}
                                        </td>
                                    </tr>

                                    <tr v-if="subAgents.length === 0">
                                        <td
                                            colspan="4"
                                            class="px-4 py-8 text-center text-sm text-slate-500"
                                        >
                                            No sub-agents found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500 shadow-sm"
            >
                Agent not found.
            </div>
        </div>
    </AppLayout>
</template>