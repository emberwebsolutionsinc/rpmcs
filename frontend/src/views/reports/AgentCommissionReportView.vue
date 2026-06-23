<script setup>
import { onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";

import AgentCommissionSummary from "@/components/reports/AgentCommissionSummary.vue";
import AgentCommissionTable from "@/components/reports/AgentCommissionTable.vue";
import AgentCommissionSalesTable from "@/components/reports/AgentCommissionSalesTable.vue";

import reportService from "@/services/reportService";

const loading = ref(false);

const summary = ref({});
const agents = ref([]);
const sales = ref({
    data: [],
});

const filters = reactive({
    search: "",
    per_page: 10,
});

const fetchReport = async () => {
    loading.value = true;

    try {
        const response =
            await reportService.getAgentCommissionReport(filters);

        summary.value = response.data.summary;
        agents.value = response.data.agents;
        sales.value = response.data.sales;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchReport);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Agent Commission Report"
                description="Monitor sales commissions and agent performance."
            />

            <AgentCommissionSummary
                :summary="summary"
            />

            <AgentCommissionTable
                :agents="agents"
            />

            <AgentCommissionSalesTable
                :sales="sales"
            />
        </div>
    </AppLayout>
</template>