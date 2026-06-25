<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import ModuleStatCard from "@/components/common/ModuleStatCard.vue";
import StatusBadge from "@/components/common/StatusBadge.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";

import ProjectOverviewTab from "@/components/project-details/ProjectOverviewTab.vue";
import ProjectPhasesTab from "@/components/project-details/ProjectPhasesTab.vue";
import ProjectBlocksTab from "@/components/project-details/ProjectBlocksTab.vue";
import ProjectLotsTab from "@/components/project-details/ProjectLotsTab.vue";

import propertyProjectService from "@/services/propertyProjectService.js";
import toast from "@/utils/toast";

import {
    ArrowLeft,
    Layers3,
    Grid2X2,
    MapPinned,
    CheckCircle2,
} from "lucide-vue-next";

const route = useRoute();
const router = useRouter();

const project = ref(null);
const loading = ref(false);
const activeTab = ref("overview");

const tabs = [
    { key: "overview", label: "Overview" },
    { key: "phases", label: "Phases" },
    { key: "blocks", label: "Blocks" },
    { key: "lots", label: "Lots" },
];

const projectId = computed(() => route.params.id);

const phasesCount = computed(() => project.value?.phases?.length ?? 0);
const blocksCount = computed(() => project.value?.blocks?.length ?? 0);
const lotsCount = computed(() => project.value?.lots?.length ?? 0);

const availableLotsCount = computed(() => {
    return (
        project.value?.lots?.filter((lot) => lot.status === "available")
            .length ?? 0
    );
});

const loadProject = async () => {
    loading.value = true;

    try {
        const response = await propertyProjectService.getProject(
            projectId.value
        );

        project.value = response.data.data;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load project details.");
    } finally {
        loading.value = false;
    }
};

const goBackToProjects = () => {
    router.push("/property-management/projects");
};

onMounted(() => {
    loadProject();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-start gap-3">
                <button
                    @click="goBackToProjects"
                    class="mt-1 rounded-lg border border-slate-300 bg-white p-2 text-slate-700 hover:bg-slate-50"
                    title="Back to Projects"
                >
                    <ArrowLeft class="h-5 w-5" />
                </button>

                <div v-if="project">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                            {{ project.project_name }}
                        </h1>

                        <StatusBadge :value="project.status" />
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Project Code: {{ project.project_code }}
                    </p>
                </div>

                <div v-else>
                    <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                        Project Details
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Loading project information...
                    </p>
                </div>
            </div>

            <TableSkeleton v-if="loading" />

            <template v-else-if="project">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <ModuleStatCard
                        title="Phases"
                        :value="phasesCount"
                        :icon="Layers3"
                        helper="Total phases inside this project"
                    />

                    <ModuleStatCard
                        title="Blocks"
                        :value="blocksCount"
                        :icon="Grid2X2"
                        helper="Total blocks inside this project"
                    />

                    <ModuleStatCard
                        title="Lots"
                        :value="lotsCount"
                        :icon="MapPinned"
                        helper="Total lots inside this project"
                    />

                    <ModuleStatCard
                        title="Available"
                        :value="availableLotsCount"
                        :icon="CheckCircle2"
                        helper="Lots available for sale"
                    />
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6">
                        <nav class="flex gap-6 overflow-x-auto">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                @click="activeTab = tab.key"
                                class="border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap"
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
                        <ProjectOverviewTab
                            v-if="activeTab === 'overview'"
                            :project="project"
                        />

                        <ProjectPhasesTab
                            v-else-if="activeTab === 'phases'"
                            :project="project"
                            @refresh="loadProject"
                        />

                        <ProjectBlocksTab
                            v-else-if="activeTab === 'blocks'"
                            :project="project"
                            @refresh="loadProject"
                        />

                        <ProjectLotsTab
                            v-else-if="activeTab === 'lots'"
                            :project="project"
                            @refresh="loadProject"
                        />
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>