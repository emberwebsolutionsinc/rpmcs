<script setup>
import { computed, onMounted, reactive, ref } from "vue";

import AppLayout from "@/layouts/AppLayout.vue";

import PageHeader from "@/components/common/PageHeader.vue";
import ModuleStatCard from "@/components/common/ModuleStatCard.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";
import EmptyState from "@/components/common/EmptyState.vue";
import Pagination from "@/components/common/Pagination.vue";
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";

import ProjectModal from "@/components/property-projects/ProjectModal.vue";
import ProjectTable from "@/components/property-projects/ProjectTable.vue";

import propertyProjectService from "@/services/propertyProjectService.js";
import toast from "@/utils/toast";
import { confirmDelete } from "@/utils/swal";

import {
    Building2,
    CheckCircle2,
    XCircle,
    MapPinned,
    Plus,
    Search,
} from "lucide-vue-next";

const projects = ref([]);
const loading = ref(false);
const processing = ref(false);

const showModal = ref(false);
const selectedProject = ref(null);

const filters = reactive({
    search: "",
    per_page: 10,
    page: 1,
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
});

const activeProjectsCount = computed(() => {
    return projects.value.filter(
        (project) => project.status === "active"
    ).length;
});

const inactiveProjectsCount = computed(() => {
    return projects.value.filter(
        (project) => project.status === "inactive"
    ).length;
});

const totalLotsCount = computed(() => {
    return projects.value.reduce(
        (total, project) => total + (project.lots_count ?? 0),
        0
    );
});

const fetchProjects = async () => {
    loading.value = true;

    try {
        const response = await propertyProjectService.getProjects(filters);

        projects.value = response.data.data ?? [];

        pagination.current_page = response.data.current_page ?? 1;
        pagination.last_page = response.data.last_page ?? 1;
        pagination.total = response.data.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load property projects.");
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    selectedProject.value = null;
    showModal.value = true;
};

const openEditModal = (project) => {
    selectedProject.value = project;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedProject.value = null;
};

const submitProject = async (form) => {
    processing.value = true;

    try {
        if (selectedProject.value) {
            await propertyProjectService.updateProject(
                selectedProject.value.id,
                form
            );

            toast.success("Property project updated successfully.");
        } else {
            await propertyProjectService.createProject(form);

            toast.success("Property project created successfully.");
        }

        closeModal();
        await fetchProjects();
    } catch (error) {
        console.error(error);

        if (error.response?.data?.errors) {
            const firstError = Object.values(error.response.data.errors)[0][0];
            toast.error(firstError);
            return;
        }

        toast.error(error.response?.data?.message || "Something went wrong.");
    } finally {
        processing.value = false;
    }
};

const deleteProject = async (project) => {
    const confirmed = await confirmDelete(
        `Delete ${project.project_name}?`
    );

    if (!confirmed) return;

    processing.value = true;

    try {
        await propertyProjectService.deleteProject(project.id);

        toast.success("Property project deleted successfully.");

        await fetchProjects();
    } catch (error) {
        console.error(error);

        toast.error(
            error.response?.data?.message ||
            "Failed to delete property project."
        );
    } finally {
        processing.value = false;
    }
};

const searchProjects = () => {
    filters.page = 1;
    fetchProjects();
};

const nextPage = () => {
    if (pagination.current_page < pagination.last_page) {
        filters.page++;
        fetchProjects();
    }
};

const previousPage = () => {
    if (pagination.current_page > 1) {
        filters.page--;
        fetchProjects();
    }
};

onMounted(() => {
    fetchProjects();
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Property Projects"
                description="Manage real estate projects, subdivisions, estates, and property developments."
            />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <ModuleStatCard
                    title="Total Projects"
                    :value="pagination.total"
                    :icon="Building2"
                    helper="All registered projects"
                />

                <ModuleStatCard
                    title="Active Projects"
                    :value="activeProjectsCount"
                    :icon="CheckCircle2"
                    helper="Currently active"
                />

                <ModuleStatCard
                    title="Inactive Projects"
                    :value="inactiveProjectsCount"
                    :icon="XCircle"
                    helper="Temporarily inactive"
                />

                <ModuleStatCard
                    title="Total Lots"
                    :value="totalLotsCount"
                    :icon="MapPinned"
                    helper="Lots under listed projects"
                />
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="relative w-full xl:max-w-lg">
                        <Search class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" />

                        <input
                            v-model="filters.search"
                            @keyup.enter="searchProjects"
                            type="text"
                            placeholder="Search project code, name, or developer..."
                            class="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select
                            v-model="filters.per_page"
                            @change="searchProjects"
                            class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option :value="10">10 rows</option>
                            <option :value="25">25 rows</option>
                            <option :value="50">50 rows</option>
                        </select>

                        <button
                            @click="searchProjects"
                            class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium hover:bg-slate-50"
                        >
                            Search
                        </button>

                        <button
                            @click="openCreateModal"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                        >
                            <Plus class="h-5 w-5" />
                            Add Project
                        </button>
                    </div>
                </div>
            </div>

            <TableSkeleton v-if="loading" />

            <ProjectTable
                v-else-if="projects.length > 0"
                :projects="projects"
                @edit="openEditModal"
                @delete="deleteProject"
            />

            <EmptyState
                v-else
                title="No Property Projects Yet"
                description="Create your first property project, subdivision, estate, or development."
                button-text="Add Project"
                :icon="Building2"
                @action="openCreateModal"
            />

            <Pagination
                v-if="!loading && pagination.total > 0"
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                :total="pagination.total"
                @previous="previousPage"
                @next="nextPage"
            />
        </div>

        <ProjectModal
            :show="showModal"
            :project="selectedProject"
            @close="closeModal"
            @submit="submitProject"
        />

        <LoadingOverlay
            :show="processing"
            title="Processing Request"
            description="Please wait while BPMCS updates the property project records."
        />
    </AppLayout>
</template>