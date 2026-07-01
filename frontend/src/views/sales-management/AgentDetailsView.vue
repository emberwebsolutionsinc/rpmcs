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

const agentId = computed(() => route.params.id);

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

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
                    <h1 class="text-2xl font-bold text-slate-900">
                        {{ fullName(agent) }}
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ agent.agent_code || "—" }}
                        ·
                        {{ agent.agent_type?.replace("_", " ") || "—" }}
                        ·
                        {{ agent.status || "—" }}
                    </p>
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

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-900">
                        Profile
                    </h3>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Contact Number
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ agent.contact_number || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Email
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ agent.email || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Commission Rate
                            </p>
                            <p class="font-medium text-emerald-700">
                                {{ agent.default_commission_rate || 0 }}%
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Sub-Agents
                            </p>
                            <p class="font-medium text-slate-900">
                                {{ subAgents.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-900">
                        Loaded Data Check
                    </h3>

                    <div class="mt-4 grid gap-4 md:grid-cols-4">
                        <p>Sales: {{ sales.length }}</p>
                        <p>Payments: {{ payments.length }}</p>
                        <p>Deleted: {{ deletedPayments.length }}</p>
                        <p>Sub-Agents: {{ subAgents.length }}</p>
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