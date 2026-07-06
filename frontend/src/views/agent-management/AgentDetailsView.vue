<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";

import AgentHeader from "@/components/agent-management/AgentHeader.vue";
import AgentSummaryCards from "@/components/agent-management/AgentSummaryCards.vue";
import AgentTabs from "@/components/agent-management/AgentTabs.vue";

import AgentOverviewTab from "@/components/agent-management/tabs/AgentOverviewTab.vue";
import AgentSalesTab from "@/components/agent-management/tabs/AgentSalesTab.vue";
import AgentCommissionHistoryTab from "@/components/agent-management/tabs/AgentCommissionHistoryTab.vue";
import AgentPaymentsTab from "@/components/agent-management/tabs/AgentPaymentsTab.vue";
import AgentSubAgentsTab from "@/components/agent-management/tabs/AgentSubAgentsTab.vue";
import AgentTimelineTab from "@/components/agent-management/tabs/AgentTimelineTab.vue";
import AgentDocumentsTab from "@/components/agent-management/tabs/AgentDocumentsTab.vue";
import AgentActivitiesTab from "@/components/agent-management/tabs/AgentActivitiesTab.vue";

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
const documents = ref([]);
const activities = ref([]);

const activeTab = ref("overview");

const tabs = [
    { key: "overview", label: "Overview" },
    { key: "sales", label: "Sales" },
    { key: "commissions", label: "Commission History" },
    { key: "payments", label: "Payments" },
    { key: "sub_agents", label: "Sub Agents" },
    { key: "documents", label: "Documents" },
    { key: "activities", label: "Activities" },
     { key: "timeline", label: "Timeline" },
];

const agentId = computed(() => route.params.id);

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
        documents.value = response.data.documents ?? [];
        activities.value = response.data.activities ?? [];

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

const goToMainAgent = () => {
    if (!agent.value?.main_agent?.id) return;

    router.push(`/agent-management/agents/${agent.value.main_agent.id}`);
};

const goToSubAgent = (subAgent) => {
    router.push(`/agent-management/agents/${subAgent.id}`);
};

onMounted(() => {
    loadAgent();
});

watch(
    () => route.params.id,
    async () => {
        activeTab.value = "overview";
        await loadAgent();
    }
);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Agent Details"
                description="View the complete profile, sales, commissions, payments, sub-agents, documents, and activities of this agent."
            />

            <TableSkeleton v-if="loading" />

            <template v-else-if="agent">
                <AgentHeader
                    :agent="agent"
                    @back="goBack"
                    @view-ledger="goToLedger"
                    @view-main-agent="goToMainAgent"
                />

                <AgentSummaryCards
                    :summary="summary"
                />

                <AgentTabs
                    :tabs="tabs"
                    :active-tab="activeTab"
                    @change="activeTab = $event"
                >
                    <AgentOverviewTab
                        v-if="activeTab === 'overview'"
                        :agent="agent"
                        :summary="summary"
                        :sub-agents="subAgents"
                    />

                    <AgentSalesTab
                        v-else-if="activeTab === 'sales'"
                        :sales="sales"
                    />

                    <AgentCommissionHistoryTab
                        v-else-if="activeTab === 'commissions'"
                        :sales="sales"
                    />

                    <AgentPaymentsTab
                        v-else-if="activeTab === 'payments'"
                        :payments="payments"
                        :deleted-payments="deletedPayments"
                    />

                    <AgentSubAgentsTab
                        v-else-if="activeTab === 'sub_agents'"
                        :agent="agent"
                        :sub-agents="subAgents"
                        @view-sub-agent="goToSubAgent"
                    />

                   <AgentDocumentsTab
                        v-else-if="activeTab === 'documents'"
                        :agent="agent"
                        :documents="documents"
                        @refresh="loadAgent"
                    />

                    <AgentActivitiesTab
                        v-else-if="activeTab === 'activities'"
                        :agent="agent"
                    />

                    <AgentTimelineTab
                        v-else-if="activeTab === 'timeline'"
                        :activities="activities"
                    />

                   
                </AgentTabs>
            </template>

            <div
                v-else
                class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500 shadow-sm"
            >
                Agent not found.
            </div>
        </div>
    </AppLayout>
</template>