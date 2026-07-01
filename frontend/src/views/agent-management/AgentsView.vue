<script setup>
import { onMounted, reactive, ref } from "vue";
import { RouterLink } from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";

import agentService from "@/services/agentService";
import toast from "@/utils/toast";

const loading = ref(false);

const agents = ref({
    data: [],
});

const filters = reactive({
    search: "",
    agent_type: "",
    status: "",
    per_page: 10,
    page: 1,
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
});

const fetchAgents = async () => {
    loading.value = true;

    try {
        const response = await agentService.getAgents(filters);

        agents.value = response.data ?? { data: [] };

        pagination.current_page = agents.value.current_page ?? 1;
        pagination.last_page = agents.value.last_page ?? 1;
        pagination.total = agents.value.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load agents.");
    } finally {
        loading.value = false;
    }
};

const applyFilters = async () => {
    filters.page = 1;
    await fetchAgents();
};

const resetFilters = async () => {
    filters.search = "";
    filters.agent_type = "";
    filters.status = "";
    filters.page = 1;

    await fetchAgents();
};

const previousPage = async () => {
    if (pagination.current_page > 1) {
        filters.page = pagination.current_page - 1;
        await fetchAgents();
    }
};

const nextPage = async () => {
    if (pagination.current_page < pagination.last_page) {
        filters.page = pagination.current_page + 1;
        await fetchAgents();
    }
};

const goToPage = async (page) => {
    filters.page = page;
    await fetchAgents();
};

onMounted(fetchAgents);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Agent Management"
                description="Manage main agents, sub-agents, contact details, status, and commission rates."
            />

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Search
                        </label>

                        <input
                            v-model="filters.search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search code, name, contact, email..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Agent Type
                        </label>

                        <select
                            v-model="filters.agent_type"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">All</option>
                            <option value="main_agent">Main Agent</option>
                            <option value="sub_agent">Sub-Agent</option>
                            <option value="broker">Broker</option>
                            <option value="referral">Referral</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">
                            Status
                        </label>

                        <select
                            v-model="filters.status"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
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
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b px-5 py-4">
                    <h3 class="font-semibold text-slate-900">
                        Agents
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Agent
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Type
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                    Main Agent
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

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="loading">
                                <td
                                    colspan="7"
                                    class="px-4 py-8 text-center text-sm text-slate-500"
                                >
                                    Loading agents...
                                </td>
                            </tr>

                            <tr
                                v-for="agent in agents.data"
                                v-else
                                :key="agent.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ agent.first_name }}
                                        {{ agent.middle_name }}
                                        {{ agent.last_name }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ agent.agent_code || "—" }}
                                    </p>
                                </td>

                                <td class="px-4 py-4 text-sm capitalize">
                                    {{ agent.agent_type?.replace("_", " ") || "—" }}
                                </td>

                                <td class="px-4 py-4 text-sm">
                                    <template v-if="agent.main_agent">
                                        {{ agent.main_agent.first_name }}
                                        {{ agent.main_agent.last_name }}
                                    </template>

                                    <template v-else>
                                        —
                                    </template>
                                </td>

                                <td class="px-4 py-4 text-sm">
                                    <p>{{ agent.contact_number || "—" }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ agent.email || "—" }}
                                    </p>
                                </td>

                                <td class="px-4 py-4 text-center font-semibold text-emerald-700">
                                    {{ agent.default_commission_rate || 0 }}%
                                </td>

                                <td class="px-4 py-4 text-center">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            agent.status === 'active'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-100 text-slate-600'
                                        "
                                    >
                                        {{ agent.status || "—" }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <RouterLink
                                        :to="`/agent-management/agents/${agent.id}`"
                                        class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100"
                                    >
                                        View
                                    </RouterLink>
                                </td>
                            </tr>

                            <tr v-if="!loading && agents.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-4 py-8 text-center text-sm text-slate-500"
                                >
                                    No agents found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                v-if="!loading && pagination.total > 0"
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                :total="pagination.total"
                @previous="previousPage"
                @next="nextPage"
                @go-to-page="goToPage"
            />
        </div>
    </AppLayout>
</template>