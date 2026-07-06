<script setup>
const props = defineProps({
    agent: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
    subAgents: {
        type: Array,
        default: () => [],
    },
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

const fieldValue = (value) => {
    return value || "—";
};
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900">
                    General Information
                </h3>

                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Agent Code</span>
                        <span class="font-semibold text-slate-900">
                            {{ fieldValue(agent.agent_code) }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Full Name</span>
                        <span class="font-semibold text-slate-900">
                            {{ fullName(agent) }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Agent Type</span>
                        <span class="font-semibold capitalize text-slate-900">
                            {{ agent.agent_type?.replace("_", " ") || "—" }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Status</span>
                        <span class="font-semibold capitalize text-slate-900">
                            {{ fieldValue(agent.status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900">
                    Contact Information
                </h3>

                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Contact Number</span>
                        <span class="font-semibold text-slate-900">
                            {{ fieldValue(agent.contact_number) }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Email</span>
                        <span class="font-semibold text-slate-900">
                            {{ fieldValue(agent.email) }}
                        </span>
                    </div>

                    <div class="border-b pb-2">
                        <span class="text-slate-500">Address</span>
                        <p class="mt-1 font-semibold text-slate-900">
                            {{ fieldValue(agent.address) }}
                        </p>
                    </div>

                    <div>
                        <span class="text-slate-500">License Number</span>
                        <p class="mt-1 font-semibold text-slate-900">
                            {{ fieldValue(agent.license_number) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900">
                    Commission Configuration
                </h3>

                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Default Rate</span>
                        <span class="font-bold text-emerald-700">
                            {{ agent.default_commission_rate || 0 }}%
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Commission Earned</span>
                        <span class="font-semibold text-emerald-700">
                            {{ summary.total_commission_earned || 0 }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Commission Paid</span>
                        <span class="font-semibold text-blue-700">
                            {{ summary.total_commission_paid || 0 }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Commission Balance</span>
                        <span class="font-bold text-red-700">
                            {{ summary.total_commission_balance || 0 }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-900">
                    Organization
                </h3>

                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Main Agent</span>
                        <span class="font-semibold text-slate-900">
                            {{ fullName(agent.main_agent) }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-slate-500">Sub-Agent Count</span>
                        <span class="font-semibold text-slate-900">
                            {{ summary.sub_agents_count || subAgents.length || 0 }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Can Have Sub-Agents?</span>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="
                                agent.agent_type === 'sub_agent'
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-emerald-100 text-emerald-700'
                            "
                        >
                            {{ agent.agent_type === "sub_agent" ? "No" : "Yes" }}
                        </span>
                    </div>

                    <div
                        v-if="agent.agent_type === 'sub_agent'"
                        class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800"
                    >
                        This agent is a sub-agent and cannot have another sub-agent under them.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>