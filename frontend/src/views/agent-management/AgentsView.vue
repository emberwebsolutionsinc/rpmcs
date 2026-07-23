<script setup>
import {
    computed,
    onMounted,
    reactive,
    ref,
} from "vue";
import { RouterLink } from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";
import AgentAddFormModal from "@/components/agent-management/AgentAddFormModal.vue";
import SubAgentFormModal from "@/components/agent-management/SubAgentFormModal.vue";

import agentService from "@/services/agentService";
import toast from "@/utils/toast";
import { useAuthStore } from "@/stores/auth";

/*
|--------------------------------------------------------------------------
| General loading state
|--------------------------------------------------------------------------
*/

const loading = ref(false);

/*
|--------------------------------------------------------------------------
| Authentication and permissions
|--------------------------------------------------------------------------
*/

const authStore = useAuthStore();

const currentUser = computed(() => {
    return authStore.user?.user ?? authStore.user ?? {};
});

const normalizedValues = (values = []) => {
    if (!Array.isArray(values)) {
        return [];
    }

    return values
        .map((value) =>
            typeof value === "string"
                ? value
                : value?.name
        )
        .filter(Boolean)
        .map((value) =>
            String(value)
                .trim()
                .toLowerCase()
        );
};

const userRoles = computed(() =>
    normalizedValues(currentUser.value?.roles)
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

const canCreateAgent = computed(() =>
    hasPermission("agents.create")
);

/*
|--------------------------------------------------------------------------
| Agent listing state
|--------------------------------------------------------------------------
*/

const agents = ref({
    data: [],
});

/*
|--------------------------------------------------------------------------
| Main Agents available for Sub-Agent dropdown
|--------------------------------------------------------------------------
|
| The agents response is an object containing a data array, so we must filter
| agents.value.data instead of agents.value.
|
*/

const mainAgents = computed(() => {
    return (
        agents.value?.data ?? []
    ).filter(
        (agent) =>
            agent.agent_type === "main_agent"
    );
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

/*
|--------------------------------------------------------------------------
| Main Agent modal state
|--------------------------------------------------------------------------
*/

const showAgentModal = ref(false);
const savingMainAgent = ref(false);

const editingAgent = ref(null);
const updatingAgent = ref(false);

const mainAgentFormErrors = reactive({});
const updateAgentErrors = ref({});

const clearMainAgentFormErrors = () => {
    Object.keys(
        mainAgentFormErrors
    ).forEach((key) => {
        delete mainAgentFormErrors[key];
    });
};

const openAgentModal = () => {
    editingAgent.value = null;
    updateAgentErrors.value = {};
    clearMainAgentFormErrors();

    showAgentModal.value = true;
};

const closeAgentModal = () => {
    if (
        savingMainAgent.value ||
        updatingAgent.value
    ) {
        return;
    }

    showAgentModal.value = false;
    editingAgent.value = null;
    updateAgentErrors.value = {};

    clearMainAgentFormErrors();
};

/*
|--------------------------------------------------------------------------
| Sub-Agent modal state
|--------------------------------------------------------------------------
*/

const showSubAgentModal = ref(false);
const editingSubAgent = ref(null);
const updatingSubAgent = ref(false);
const subAgentFormErrors = ref({});

const closeSubAgentModal = () => {
    if (updatingSubAgent.value) {
        return;
    }

    showSubAgentModal.value = false;
    editingSubAgent.value = null;
    subAgentFormErrors.value = {};
};

/*
|--------------------------------------------------------------------------
| Open edit modal
|--------------------------------------------------------------------------
*/

const editAgent = (agent) => {
    if (
        agent.agent_type === "main_agent"
    ) {
        editingAgent.value = agent;
        updateAgentErrors.value = {};
        clearMainAgentFormErrors();

        showAgentModal.value = true;

        return;
    }

    if (
        agent.agent_type === "sub_agent"
    ) {
        editingSubAgent.value = agent;
        subAgentFormErrors.value = {};

        showSubAgentModal.value = true;

        return;
    }

    toast.error(
        "This agent type cannot be edited using the current forms."
    );
};

/*
|--------------------------------------------------------------------------
| Create Main Agent
|--------------------------------------------------------------------------
*/

const createMainAgent = async (
    payload
) => {
    savingMainAgent.value = true;
    clearMainAgentFormErrors();

    try {
        const response =
            await agentService.createMainAgent(
                payload
            );

        toast.success(
            response.data?.message ??
                "Main Agent created successfully."
        );

        showAgentModal.value = false;
        editingAgent.value = null;
        filters.page = 1;

        await fetchAgents();
    } catch (error) {
        console.error(
            "Failed to create Main Agent:",
            error
        );

        if (
            error.response?.status === 422 &&
            error.response?.data?.errors
        ) {
            Object.assign(
                mainAgentFormErrors,
                error.response.data.errors
            );

            toast.error(
                error.response.data.message ??
                    "Please correct the form errors."
            );

            return;
        }

        toast.error(
            error.response?.data?.message ??
                "Failed to create Main Agent."
        );
    } finally {
        savingMainAgent.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| Update Main Agent
|--------------------------------------------------------------------------
*/

const updateSelectedAgent = async (
    payload
) => {
    const selectedAgent =
        editingAgent.value;

    if (!selectedAgent?.id) {
        return;
    }

    if (
        selectedAgent.agent_type !==
        "main_agent"
    ) {
        toast.error(
            "The selected record is not a Main Agent."
        );

        return;
    }

    updatingAgent.value = true;
    updateAgentErrors.value = {};

    try {
        const response =
            await agentService.updateMainAgent(
                selectedAgent.id,
                payload
            );

        toast.success(
            response.data?.message ??
                "Main Agent updated successfully."
        );

        showAgentModal.value = false;
        editingAgent.value = null;

        await fetchAgents();
    } catch (error) {
        updateAgentErrors.value =
            error.response?.data?.errors ??
            {};

        toast.error(
            error.response?.data?.message ??
                "Unable to update the Main Agent."
        );
    } finally {
        updatingAgent.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| Submit Main Agent form
|--------------------------------------------------------------------------
*/

const submitAgentForm = async (
    payload
) => {
    if (editingAgent.value?.id) {
        await updateSelectedAgent(
            payload
        );

        return;
    }

    await createMainAgent(payload);
};

/*
|--------------------------------------------------------------------------
| Update Sub-Agent
|--------------------------------------------------------------------------
*/

const updateSubAgent = async (
    payload
) => {
    const selectedSubAgent =
        editingSubAgent.value;

    if (!selectedSubAgent?.id) {
        return;
    }

    if (
        selectedSubAgent.agent_type !==
        "sub_agent"
    ) {
        toast.error(
            "The selected record is not a Sub-Agent."
        );

        return;
    }

    updatingSubAgent.value = true;
    subAgentFormErrors.value = {};

    try {
        const response =
            await agentService.updateAgent(
                selectedSubAgent.id,
                payload
            );

        toast.success(
            response.data?.message ??
                "Sub-Agent updated successfully."
        );

        showSubAgentModal.value = false;
        editingSubAgent.value = null;

        await fetchAgents();
    } catch (error) {
        subAgentFormErrors.value =
            error.response?.data?.errors ??
            {};

        toast.error(
            error.response?.data?.message ??
                "Unable to update the Sub-Agent."
        );
    } finally {
        updatingSubAgent.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| Delete Agent modal
|--------------------------------------------------------------------------
*/

const showDeleteModal = ref(false);
const deletingAgent = ref(null);
const deleting = ref(false);
const deleteError = ref("");

const confirmDeleteAgent = (
    agent
) => {
    deletingAgent.value = agent;
    deleteError.value = "";
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    if (deleting.value) {
        return;
    }

    showDeleteModal.value = false;
    deletingAgent.value = null;
    deleteError.value = "";
};

const deleteSelectedAgent =
    async () => {
        if (!deletingAgent.value?.id) {
            return;
        }

        deleting.value = true;
        deleteError.value = "";

        try {
            const response =
                await agentService.deleteAgent(
                    deletingAgent.value.id
                );

            toast.success(
                response.data?.message ??
                    "Agent deleted successfully."
            );

            showDeleteModal.value =
                false;
            deletingAgent.value = null;

            await fetchAgents();
        } catch (error) {
            deleteError.value =
                error.response?.data
                    ?.errors?.agent?.[0] ??
                error.response?.data
                    ?.message ??
                "Unable to delete the agent.";

            toast.error(
                deleteError.value
            );
        } finally {
            deleting.value = false;
        }
    };

/*
|--------------------------------------------------------------------------
| Fetch agents
|--------------------------------------------------------------------------
*/

const fetchAgents = async () => {
    loading.value = true;

    try {
        const response =
            await agentService.getAgents({
                ...filters,
            });

        const result =
            response.data ?? {};

        agents.value = {
            ...result,
            data: Array.isArray(
                result.data
            )
                ? result.data
                : [],
        };

        pagination.current_page =
            result.current_page ?? 1;

        pagination.last_page =
            result.last_page ?? 1;

        pagination.total =
            result.total ?? 0;
    } catch (error) {
        console.error(
            "Failed to load agents:",
            error
        );

        agents.value = {
            data: [],
        };

        pagination.current_page = 1;
        pagination.last_page = 1;
        pagination.total = 0;

        toast.error(
            error.response?.data?.message ??
                "Failed to load agents."
        );
    } finally {
        loading.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const previousPage = async () => {
    if (
        pagination.current_page <= 1
    ) {
        return;
    }

    filters.page =
        pagination.current_page - 1;

    await fetchAgents();
};

const nextPage = async () => {
    if (
        pagination.current_page >=
        pagination.last_page
    ) {
        return;
    }

    filters.page =
        pagination.current_page + 1;

    await fetchAgents();
};

const goToPage = async (page) => {
    const pageNumber = Number(page);

    if (
        !Number.isInteger(
            pageNumber
        ) ||
        pageNumber < 1 ||
        pageNumber >
            pagination.last_page
    ) {
        return;
    }

    filters.page = pageNumber;

    await fetchAgents();
};

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const getAgentFullName = (
    agent
) => {
    if (!agent) {
        return "";
    }

    return [
        agent.first_name,
        agent.middle_name,
        agent.last_name,
        agent.suffix,
    ]
        .filter(Boolean)
        .join(" ");
};

const getMainAgentName = (
    agent
) => {
    const mainAgent =
        agent?.main_agent ??
        agent?.mainAgent;

    if (!mainAgent) {
        return "—";
    }

    return getAgentFullName(
        mainAgent
    );
};

const getSubAgentsCount = (
    agent
) => {
    const subAgents =
        agent?.sub_agents ??
        agent?.subAgents ??
        [];

    return Array.isArray(subAgents)
        ? subAgents.length
        : 0;
};

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    fetchAgents();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <PageHeader
                    title="Agent Management"
                    description="Manage main agents, sub-agents, contact details, status, and commission rates."
                />

                <button
                    v-if="canCreateAgent"
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="
                        savingMainAgent
                    "
                    @click="
                        openAgentModal
                    "
                >
                    Add Main Agent
                </button>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div
                    class="grid gap-3 lg:grid-cols-4"
                >
                    <div
                        class="lg:col-span-2"
                    >
                        <label
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                        >
                            Search
                        </label>

                        <input
                            v-model="
                                filters.search
                            "
                            type="text"
                            placeholder="Search code, name, contact, email..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                            @keyup.enter="
                                applyFilters
                            "
                        />
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                        >
                            Agent Type
                        </label>

                        <select
                            v-model="
                                filters.agent_type
                            "
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option
                                value=""
                            >
                                All
                            </option>

                            <option
                                value="main_agent"
                            >
                                Main Agent
                            </option>

                            <option
                                value="sub_agent"
                            >
                                Sub-Agent
                            </option>

                            <option
                                value="broker"
                            >
                                Broker
                            </option>

                            <option
                                value="referral"
                            >
                                Referral
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold uppercase text-slate-500"
                        >
                            Status
                        </label>

                        <select
                            v-model="
                                filters.status
                            "
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option
                                value=""
                            >
                                All
                            </option>

                            <option
                                value="active"
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                            >
                                Inactive
                            </option>
                        </select>
                    </div>
                </div>

                <div
                    class="mt-4 flex justify-end gap-2"
                >
                    <button
                        type="button"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="loading"
                        @click="
                            resetFilters
                        "
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="loading"
                        @click="
                            applyFilters
                        "
                    >
                        Apply Filters
                    </button>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="border-b px-5 py-4"
                >
                    <h3
                        class="font-semibold text-slate-900"
                    >
                        Agents
                    </h3>
                </div>

                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="min-w-full divide-y divide-slate-200"
                    >
                        <thead
                            class="bg-slate-50"
                        >
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500"
                                >
                                    Agent
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500"
                                >
                                    Type
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500"
                                >
                                    Main Agent
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500"
                                >
                                    Contact
                                </th>

                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500"
                                >
                                    Rate
                                </th>

                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold uppercase text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100"
                        >
                            <tr
                                v-if="loading"
                            >
                                <td
                                    colspan="7"
                                    class="px-4 py-8 text-center text-sm text-slate-500"
                                >
                                    Loading
                                    agents...
                                </td>
                            </tr>

                            <template
                                v-else
                            >
                                <tr
                                    v-for="agent in agents.data"
                                    :key="
                                        agent.id
                                    "
                                    class="hover:bg-slate-50"
                                >
                                    <td
                                        class="px-4 py-4"
                                    >
                                        <p
                                            class="font-semibold text-slate-900"
                                        >
                                            {{
                                                getAgentFullName(
                                                    agent
                                                ) ||
                                                "—"
                                            }}
                                        </p>

                                        <p
                                            class="text-xs text-slate-500"
                                        >
                                            {{
                                                agent.agent_code ||
                                                "—"
                                            }}
                                        </p>
                                    </td>

                                    <td
                                        class="px-4 py-4 text-sm capitalize"
                                    >
                                        {{
                                            agent.agent_type
                                                ?.replaceAll(
                                                    "_",
                                                    " "
                                                ) ||
                                            "—"
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-4 text-sm"
                                    >
                                        {{
                                            getMainAgentName(
                                                agent
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-4 text-sm"
                                    >
                                        <p>
                                            {{
                                                agent.contact_number ||
                                                "—"
                                            }}
                                        </p>

                                        <p
                                            class="text-xs text-slate-500"
                                        >
                                            {{
                                                agent.email ||
                                                "—"
                                            }}
                                        </p>
                                    </td>

                                    <td
                                        class="px-4 py-4 text-center font-semibold text-emerald-700"
                                    >
                                        {{
                                            agent.default_commission_rate ??
                                            0
                                        }}%
                                    </td>

                                    <td
                                        class="px-4 py-4 text-center"
                                    >
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                            :class="
                                                agent.status ===
                                                'active'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : 'bg-slate-100 text-slate-600'
                                            "
                                        >
                                            {{
                                                agent.status ||
                                                "—"
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-4 py-4"
                                    >
                                        <div
                                            class="flex justify-end gap-2"
                                        >
                                            <RouterLink
                                                :to="`/agent-management/agents/${agent.id}`"
                                                class="inline-flex items-center rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                                            >
                                                View
                                            </RouterLink>

                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-100"
                                                @click="
                                                    editAgent(
                                                        agent
                                                    )
                                                "
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100"
                                                @click="
                                                    confirmDeleteAgent(
                                                        agent
                                                    )
                                                "
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr
                                    v-if="
                                        agents
                                            .data
                                            .length ===
                                        0
                                    "
                                >
                                    <td
                                        colspan="7"
                                        class="px-4 py-8 text-center text-sm text-slate-500"
                                    >
                                        No agents
                                        found.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                v-if="
                    !loading &&
                    pagination.total > 0
                "
                :current-page="
                    pagination.current_page
                "
                :last-page="
                    pagination.last_page
                "
                :total="
                    pagination.total
                "
                @previous="
                    previousPage
                "
                @next="nextPage"
                @go-to-page="
                    goToPage
                "
            />

            <AgentAddFormModal
                :open="
                    showAgentModal
                "
                :loading="
                    editingAgent
                        ? updatingAgent
                        : savingMainAgent
                "
                :errors="
                    editingAgent
                        ? updateAgentErrors
                        : mainAgentFormErrors
                "
                :agent="
                    editingAgent
                "
                @close="
                    closeAgentModal
                "
                @submit="
                    submitAgentForm
                "
            />

            <SubAgentFormModal
                :open="
                    showSubAgentModal
                "
                :loading="
                    updatingSubAgent
                "
                :errors="
                    subAgentFormErrors
                "
                :agent="
                    editingSubAgent
                "
                :main-agents="
                    mainAgents
                "
                @close="
                    closeSubAgentModal
                "
                @submit="
                    updateSubAgent
                "
            />

            <Teleport to="body">
                <div
                    v-if="
                        showDeleteModal
                    "
                    class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 p-4"
                    @click.self="
                        closeDeleteModal
                    "
                >
                    <div
                        class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
                    >
                        <div
                            class="border-b border-slate-200 px-6 py-5"
                        >
                            <div
                                class="flex items-start gap-4"
                            >
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-xl font-bold text-red-600"
                                >
                                    !
                                </div>

                                <div>
                                    <h2
                                        class="text-lg font-bold text-slate-900"
                                    >
                                        Delete Agent
                                    </h2>

                                    <p
                                        class="mt-1 text-sm text-slate-500"
                                    >
                                        This action
                                        cannot be
                                        undone.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="px-6 py-5"
                        >
                            <p
                                class="text-sm leading-6 text-slate-600"
                            >
                                Are you sure
                                you want to
                                delete

                                <span
                                    class="font-semibold text-slate-900"
                                >
                                    {{
                                        getAgentFullName(
                                            deletingAgent
                                        )
                                    }}
                                </span>
                                ?
                            </p>

                            <div
                                v-if="
                                    getSubAgentsCount(
                                        deletingAgent
                                    ) > 0
                                "
                                class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3"
                            >
                                <p
                                    class="text-sm font-semibold text-amber-800"
                                >
                                    Deletion is
                                    blocked
                                </p>

                                <p
                                    class="mt-1 text-sm text-amber-700"
                                >
                                    This Main
                                    Agent has
                                    {{
                                        getSubAgentsCount(
                                            deletingAgent
                                        )
                                    }}
                                    existing
                                    Sub-Agent(s).
                                    Delete or
                                    reassign the
                                    Sub-Agents
                                    first.
                                </p>
                            </div>

                            <div
                                v-if="
                                    deleteError
                                "
                                class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3"
                            >
                                <p
                                    class="text-sm font-medium text-red-700"
                                >
                                    {{
                                        deleteError
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4"
                        >
                            <button
                                type="button"
                                class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="
                                    deleting
                                "
                                @click="
                                    closeDeleteModal
                                "
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="
                                    deleting ||
                                    getSubAgentsCount(
                                        deletingAgent
                                    ) > 0
                                "
                                @click="
                                    deleteSelectedAgent
                                "
                            >
                                {{
                                    deleting
                                        ? "Deleting..."
                                        : "Delete Agent"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </AppLayout>
</template>