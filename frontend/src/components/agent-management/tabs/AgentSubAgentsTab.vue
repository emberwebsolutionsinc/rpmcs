<script setup>
const props = defineProps({
    agent: {
        type: Object,
        required: true,
    },
    subAgents: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    "view-sub-agent",
    "add-sub-agent",
]);

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

const isSubAgent = () => {
    return props.agent?.agent_type === "sub_agent";
};

const viewSubAgent = (subAgent) => {
    emit("view-sub-agent", subAgent);
};

const addSubAgent = () => {
    emit("add-sub-agent", props.agent);
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="font-semibold text-slate-900">
                    Sub-Agents
                </h3>

                <p class="text-sm text-slate-500">
                    Sub-agents assigned under this main agent.
                </p>
            </div>

            <button
                v-if="!isSubAgent()"
                type="button"
                @click="addSubAgent"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
            >
                Add Sub-Agent
            </button>
        </div>

        <div
            v-if="isSubAgent()"
            class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"
        >
            This agent is already a sub-agent under
            <strong>{{ fullName(agent.main_agent) }}</strong>.
            Sub-agents cannot have their own sub-agents.
        </div>

        <div
            v-else
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

                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="subAgent in subAgents"
                        :key="subAgent.id"
                        class="hover:bg-slate-50"
                    >
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">
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

                        <td class="px-4 py-4 text-right">
                            <button
                                type="button"
                                @click="viewSubAgent(subAgent)"
                                class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100"
                            >
                                View
                            </button>
                        </td>
                    </tr>

                    <tr v-if="subAgents.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-sm text-slate-500"
                        >
                            No sub-agents found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>