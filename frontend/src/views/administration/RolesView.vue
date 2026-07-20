```vue
<script setup>
import {
    onMounted,
    reactive,
    ref,
} from "vue";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";

import RoleFormModal from "@/views/administration/RoleFormModal.vue";

import roleService from "@/services/roleService";
import permissionService from "@/services/permissionService";
import toast from "@/utils/toast";

const loading = ref(false);
const saving = ref(false);
const deletingId = ref(null);

const roles = ref([]);
const groupedPermissions = ref([]);

const showRoleModal = ref(false);
const selectedRole = ref(null);
const serverErrors = ref({});

const showDeleteModal = ref(false);
const roleToDelete = ref(null);

const filters = reactive({
    search: "",
    page: 1,
    per_page: 10,
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
});

const summary = reactive({
    total_roles: 0,
    roles_with_users: 0,
    total_permissions: 0,
});

const fetchRoles = async () => {
    loading.value = true;

    try {
        const response =
            await roleService.getRoles(filters);

        roles.value =
            response.data.data ?? [];

        Object.assign(
            summary,
            response.data.summary ?? {}
        );

        pagination.current_page =
            response.data.current_page ?? 1;

        pagination.last_page =
            response.data.last_page ?? 1;

        pagination.total =
            response.data.total ?? 0;
    } catch (error) {
        toast.error(
            error?.response?.data?.message ||
                "Failed to load roles."
        );
    } finally {
        loading.value = false;
    }
};

const fetchPermissions = async () => {
    try {
        const response =
            await permissionService.getPermissions();

        groupedPermissions.value =
            response.data.grouped_permissions ?? [];
    } catch (error) {
        toast.error(
            error?.response?.data?.message ||
                "Failed to load permissions."
        );
    }
};

const refreshPage = async () => {
    await Promise.all([
        fetchRoles(),
        fetchPermissions(),
    ]);
};

const openCreateModal = () => {
    selectedRole.value = null;
    serverErrors.value = {};
    showRoleModal.value = true;
};

const openEditModal = async (role) => {
    serverErrors.value = {};

    try {
        const response =
            await roleService.getRole(role.id);

        selectedRole.value =
            response.data.data ?? role;

        showRoleModal.value = true;
    } catch (error) {
        toast.error(
            error?.response?.data?.message ||
                "Failed to load role."
        );
    }
};

const resetRoleModal = () => {
    showRoleModal.value = false;
    selectedRole.value = null;
    serverErrors.value = {};
};

const closeRoleModal = () => {
    if (saving.value) {
        return;
    }

    resetRoleModal();
};

const saveRole = async (payload) => {
    if (saving.value) {
        return;
    }

    saving.value = true;
    serverErrors.value = {};

    try {
        let response;

        if (selectedRole.value?.id) {
            response =
                await roleService.updateRole(
                    selectedRole.value.id,
                    payload
                );

            toast.success(
                response.data.message ||
                    "Role updated successfully."
            );
        } else {
            response =
                await roleService.createRole(
                    payload
                );

            toast.success(
                response.data.message ||
                    "Role created successfully."
            );
        }

        resetRoleModal();

        await fetchRoles();
    } catch (error) {
        if (error?.response?.status === 422) {
            serverErrors.value =
                error.response.data.errors ?? {};

            toast.error(
                error.response.data.message ||
                    "Please review the role information."
            );

            return;
        }

        toast.error(
            error?.response?.data?.message ||
                "Failed to save role."
        );
    } finally {
        saving.value = false;
    }
};

const openDeleteModal = (role) => {
    if (!role?.id) {
        toast.error("Role ID not found.");
        return;
    }

    if (isProtectedRole(role)) {
        toast.error(
            "The super-admin role cannot be deleted."
        );
        return;
    }

    roleToDelete.value = role;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    if (deletingId.value) {
        return;
    }

    showDeleteModal.value = false;
    roleToDelete.value = null;
};

const confirmDeleteRole = async () => {
    if (
        !roleToDelete.value?.id ||
        deletingId.value
    ) {
        return;
    }

    deletingId.value =
        roleToDelete.value.id;

    try {
        const response =
            await roleService.deleteRole(
                roleToDelete.value.id
            );

        toast.success(
            response.data.message ||
                "Role deleted successfully."
        );

        showDeleteModal.value = false;
        roleToDelete.value = null;

        await fetchRoles();
    } catch (error) {
        toast.error(
            error?.response?.data?.message ||
                "Failed to delete role."
        );
    } finally {
        deletingId.value = null;
    }
};

const searchRoles = async () => {
    filters.page = 1;
    await fetchRoles();
};

const previousPage = async () => {
    if (pagination.current_page <= 1) {
        return;
    }

    filters.page =
        pagination.current_page - 1;

    await fetchRoles();
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

    await fetchRoles();
};

const goToPage = async (page) => {
    if (
        page < 1 ||
        page > pagination.last_page ||
        page === pagination.current_page
    ) {
        return;
    }

    filters.page = page;

    await fetchRoles();
};

const isProtectedRole = (role) => {
    return role?.name === "super-admin";
};

onMounted(refreshPage);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
            >
                <PageHeader
                    title="Roles"
                    description="Manage system roles and their assigned permissions."
                />

                <button
                    type="button"
                    class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
                    @click="openCreateModal"
                >
                    Add Role
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-xs font-semibold uppercase text-slate-500"
                    >
                        Total Roles
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-slate-900"
                    >
                        {{ summary.total_roles || 0 }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-xs font-semibold uppercase text-slate-500"
                    >
                        Roles With Users
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-emerald-700"
                    >
                        {{ summary.roles_with_users || 0 }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-xs font-semibold uppercase text-slate-500"
                    >
                        Total Permissions
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-blue-700"
                    >
                        {{ summary.total_permissions || 0 }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search role..."
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm sm:max-w-md"
                        @keyup.enter="searchRoles"
                    />

                    <div class="flex gap-2">
                        <select
                            v-model.number="filters.per_page"
                            class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                            @change="searchRoles"
                        >
                            <option :value="10">
                                10 rows
                            </option>

                            <option :value="25">
                                25 rows
                            </option>

                            <option :value="50">
                                50 rows
                            </option>
                        </select>

                        <button
                            type="button"
                            class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
                            @click="searchRoles"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <TableSkeleton v-if="loading" />

            <div
                v-else
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-slate-200"
                    >
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500"
                                >
                                    Role
                                </th>

                                <th
                                    class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500"
                                >
                                    Users
                                </th>

                                <th
                                    class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500"
                                >
                                    Permissions
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold uppercase text-slate-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100"
                        >
                            <tr
                                v-for="role in roles"
                                :key="role.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4">
                                    <p
                                        class="font-semibold capitalize text-slate-900"
                                    >
                                        {{
                                            role.name
                                                ?.replaceAll(
                                                    "-",
                                                    " "
                                                )
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        Guard:
                                        {{ role.guard_name }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4 text-center font-semibold text-slate-900"
                                >
                                    {{
                                        role.users_count ||
                                        0
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-center"
                                >
                                    <span
                                        class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700"
                                    >
                                        {{
                                            role.permissions
                                                ?.length || 0
                                        }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <button
                                            type="button"
                                            :disabled="
                                                isProtectedRole(
                                                    role
                                                )
                                            "
                                            class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            @click="
                                                openEditModal(
                                                    role
                                                )
                                            "
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            :disabled="
                                                isProtectedRole(
                                                    role
                                                ) ||
                                                deletingId ===
                                                    role.id
                                            "
                                            class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-40"
                                            @click="
                                                openDeleteModal(
                                                    role
                                                )
                                            "
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="roles.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-14 text-center text-slate-500"
                                >
                                    No roles found.
                                </td>
                            </tr>
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
                :total="pagination.total"
                @previous="previousPage"
                @next="nextPage"
                @go-to-page="goToPage"
            />
        </div>

        <RoleFormModal
            v-model:show="showRoleModal"
            :role="selectedRole"
            :grouped-permissions="
                groupedPermissions
            "
            :saving="saving"
            :server-errors="serverErrors"
            @close="closeRoleModal"
            @submit="saveRole"
        />

        <Teleport to="body">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 p-4"
                @click.self="closeDeleteModal"
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
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-xl text-red-600"
                            >
                                !
                            </div>

                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-900"
                                >
                                    Delete Role
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    This action cannot be
                                    undone.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-5">
                        <p
                            class="text-sm leading-6 text-slate-600"
                        >
                            Are you sure you want to
                            delete the role
                            <span
                                class="font-semibold text-slate-900"
                            >
                                “{{
                                    roleToDelete?.name
                                        ?.replaceAll(
                                            "-",
                                            " "
                                        )
                                }}”
                            </span>
                            ?
                        </p>

                        <p
                            v-if="
                                roleToDelete?.users_count >
                                0
                            "
                            class="mt-3 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800"
                        >
                            This role is currently
                            assigned to
                            {{
                                roleToDelete.users_count
                            }}
                            user(s) and may not be
                            deleted.
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4"
                    >
                        <button
                            type="button"
                            :disabled="
                                Boolean(deletingId)
                            "
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="closeDeleteModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            :disabled="
                                Boolean(deletingId) ||
                                roleToDelete?.users_count >
                                    0
                            "
                            class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="confirmDeleteRole"
                        >
                            {{
                                deletingId
                                    ? "Deleting..."
                                    : "Delete Role"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>