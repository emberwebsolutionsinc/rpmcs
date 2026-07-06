<script setup>
const props = defineProps({
    agent: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "back",
    "view-ledger",
    "view-main-agent",
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

const initials = (person) => {
    if (!person) return "—";

    const first = person.first_name?.charAt(0) || "";
    const last = person.last_name?.charAt(0) || "";

    return `${first}${last}`.toUpperCase() || "A";
};

const agentTypeLabel = (type) => {
    if (!type) return "—";

    return type.replace("_", " ").toUpperCase();
};

const agentTypeClass = (type) => {
    if (type === "main_agent") {
        return "bg-emerald-100 text-emerald-700";
    }

    if (type === "sub_agent") {
        return "bg-blue-100 text-blue-700";
    }

    return "bg-slate-100 text-slate-700";
};

const statusClass = (status) => {
    if (status === "active") {
        return "bg-emerald-100 text-emerald-700";
    }

    if (status === "inactive") {
        return "bg-red-100 text-red-700";
    }

    if (status === "pending") {
        return "bg-amber-100 text-amber-700";
    }

    return "bg-slate-100 text-slate-600";
};
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-2xl font-bold text-emerald-700">
                    {{ initials(agent) }}
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                            {{ fullName(agent) }}
                        </h1>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                            :class="statusClass(agent.status)"
                        >
                            {{ agent.status || "—" }}
                        </span>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="agentTypeClass(agent.agent_type)"
                        >
                            {{ agentTypeLabel(agent.agent_type) }}
                        </span>

                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                            {{ agent.default_commission_rate || 0 }}% Commission
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Agent Code:
                        <span class="font-semibold text-slate-700">
                            {{ agent.agent_code || "—" }}
                        </span>
                    </p>

                    <div class="mt-3 flex flex-wrap gap-3 text-sm text-slate-600">
                        <span>
                            Contact:
                            <strong>{{ agent.contact_number || "—" }}</strong>
                        </span>

                        <span>
                            Email:
                            <strong>{{ agent.email || "—" }}</strong>
                        </span>

                        <span>
                            License:
                            <strong>{{ agent.license_number || "—" }}</strong>
                        </span>
                    </div>

                    <p
                        v-if="agent.main_agent"
                        class="mt-3 text-sm text-slate-500"
                    >
                        Main Agent:
                        <span class="font-semibold text-slate-700">
                            {{ fullName(agent.main_agent) }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    v-if="agent.main_agent"
                    type="button"
                    @click="emit('view-main-agent')"
                    class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                >
                    View Main Agent
                </button>

                <button
                    type="button"
                    @click="emit('view-ledger')"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                >
                    View Ledger
                </button>

                <button
                    type="button"
                    @click="emit('back')"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Back
                </button>
            </div>
        </div>
    </div>
</template>