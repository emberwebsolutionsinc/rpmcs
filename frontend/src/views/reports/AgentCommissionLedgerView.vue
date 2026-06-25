<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";

import AgentCommissionLedgerSummary from "@/components/reports/AgentCommissionLedgerSummary.vue";
import AgentCommissionLedgerTable from "@/components/reports/AgentCommissionLedgerTable.vue";
import AgentLedgerPaymentHistoryTable from "@/components/reports/AgentLedgerPaymentHistoryTable.vue";

import agentService from "@/services/agentService";
import reportService from "@/services/reportService";
import toast from "@/utils/toast";

const loading = ref(false);
const agentsLoading = ref(false);

const agents = ref([]);

const summary = ref({});
const ledger = ref([]);
const payments = ref([]);
const deletedPayments = ref([]);

const filters = reactive({
    agent_id: "",
    from_date: "",
    to_date: "",
});

const fetchAgents = async () => {
    agentsLoading.value = true;

    try {
        const response = await agentService.getAgents({
            per_page: 100,
        });

        agents.value = response.data.data?.data ?? response.data.data ?? [];

        if (!filters.agent_id && agents.value.length > 0) {
            filters.agent_id = agents.value[0].id;
            await fetchLedger();
        }
    } catch (error) {
        console.error(error);
        toast.error("Failed to load agents.");
    } finally {
        agentsLoading.value = false;
    }
};

const fetchLedger = async () => {
    if (!filters.agent_id) {
        return;
    }

    loading.value = true;

    try {
        const response =
            await reportService.getAgentCommissionLedger(filters);

        summary.value = response.data.summary ?? {};
        ledger.value = response.data.ledger ?? [];
        payments.value = response.data.payments ?? [];
        deletedPayments.value = response.data.deleted_payments ?? [];
    } catch (error) {
        console.error(error);
        toast.error("Failed to load agent commission ledger.");
    } finally {
        loading.value = false;
    }
};

const exportExcel = async () => {
    if (!filters.agent_id) {
        toast.error("Please select an agent first.");
        return;
    }

    loading.value = true;

    try {
        const response =
            await reportService.exportAgentCommissionLedgerExcel(filters);

        const blob = new Blob([response.data], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `agent-commission-ledger-${new Date()
            .toISOString()
            .slice(0, 10)}.xlsx`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export agent commission ledger Excel.");
    } finally {
        loading.value = false;
    }
};

const exportPdf = async () => {
    if (!filters.agent_id) {
        toast.error("Please select an agent first.");
        return;
    }

    loading.value = true;

    try {
        const response =
            await reportService.exportAgentCommissionLedgerPdf(filters);

        const blob = new Blob([response.data], {
            type: "application/pdf",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `agent-commission-ledger-${new Date()
            .toISOString()
            .slice(0, 10)}.pdf`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        toast.error("Failed to export agent commission ledger PDF.");
    } finally {
        loading.value = false;
    }
};

const printReport = () => {
    window.print();
};

const applyFilters = async () => {
    await fetchLedger();
};

const resetFilters = async () => {
    filters.from_date = "";
    filters.to_date = "";

    await fetchLedger();
};

onMounted(fetchAgents);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Agent Commission Ledger"
                description="View per-agent commission earnings, payments, deleted payments, and remaining balances."
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Agent
                        </label>

                        <select
                            v-model="filters.agent_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                            :disabled="agentsLoading"
                            @change="fetchLedger"
                        >
                            <option value="">
                                Select Agent
                            </option>

                            <option
                                v-for="agent in agents"
                                :key="agent.id"
                                :value="agent.id"
                            >
                                {{ agent.agent_code }} -
                                {{ agent.first_name }}
                                {{ agent.last_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            From Date
                        </label>

                        <input
                            v-model="filters.from_date"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            To Date
                        </label>

                        <input
                            v-model="filters.to_date"
                            type="date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        @click="resetFilters"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-50"
                    >
                        Reset
                    </button>

                    <button
                        @click="applyFilters"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                    >
                        Apply Filters
                    </button>
                    <button
                        @click="exportExcel"
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        Export Excel
                    </button>

                    <button
                        @click="exportPdf"
                        class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Export PDF
                    </button>

                    <button
                        @click="printReport"
                        class="rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        Print
                    </button>
                </div>
            </div>

            <AgentCommissionLedgerSummary
                :summary="summary"
            />

            <AgentCommissionLedgerTable
                :rows="ledger"
            />

            <div class="grid gap-6 xl:grid-cols-2">
                <AgentLedgerPaymentHistoryTable
                    :payments="payments"
                />

                <AgentLedgerPaymentHistoryTable
                    :payments="deletedPayments"
                    deleted
                />
            </div>
        </div>
    </AppLayout>
</template>