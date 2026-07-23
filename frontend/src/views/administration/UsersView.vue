<script setup>
import {
    computed,
    onMounted,
    reactive,
    ref,
} from "vue";

import api from "@/services/api";
import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import Pagination from "@/components/common/Pagination.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";

import UserFormModal from "@/views/administration/UserFormModal.vue";
import ResetPasswordModal from "@/views/administration/ResetPasswordModal.vue";

import userService from "@/services/administration/userService.js";
import toast from "@/utils/toast";

const loading = ref(false);
const saving = ref(false);
const resettingPassword = ref(false);
const updatingStatusId = ref(null);

const users = ref([]);
const roles = ref([]);
const statuses = ref([]);

const showUserModal = ref(false);
const showPasswordModal = ref(false);

const selectedUser = ref(null);

const serverErrors = ref({});
const passwordServerErrors = ref({});

const safelyParseJson = (value) => {
    try {
        return JSON.parse(value || "{}");
    } catch {
        return {};
    }
};

const storedUser = safelyParseJson(
    localStorage.getItem("user")
);

const authenticatedUser = reactive({
    id: storedUser?.user?.id ?? storedUser?.id ?? null,
    name:
        storedUser?.user?.name ??
        storedUser?.name ??
        "",
    email:
        storedUser?.user?.email ??
        storedUser?.email ??
        "",
    roles:
        storedUser?.user?.roles ??
        storedUser?.roles ??
        [],
    permissions:
        storedUser?.user?.permissions ??
        storedUser?.permissions ??
        [],
});

const filters = reactive({
    search: "",
    role: "",
    status: "",
    sort_by: "created_at",
    sort_direction: "desc",
    page: 1,
    per_page: 15,
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
});

const summary = reactive({
    total_users: 0,
    active_users: 0,
    inactive_users: 0,
});



const normalizedPermissions = computed(
    () => {
        const permissions =
            authenticatedUser.permissions ??
            [];

        return permissions
            .map((permission) => {
                const permissionName =
                    typeof permission ===
                    "string"
                        ? permission
                        : permission?.name;

                return String(
                    permissionName ?? ""
                )
                    .trim()
                    .toLowerCase();
            })
            .filter(Boolean);
    }
);

const hasPermission = (
    permission
) => {
    if (
        isSuperAdministrator.value
    ) {
        return true;
    }

    return normalizedPermissions.value.includes(
        String(permission)
            .trim()
            .toLowerCase()
    );
};

const normalizedRoles = computed(() => {
    const roles =
        authenticatedUser.roles ?? [];

    return roles
        .map((role) => {
            const roleName =
                typeof role === "string"
                    ? role
                    : role?.name;

            return String(
                roleName ?? ""
            )
                .trim()
                .toLowerCase();
        })
        .filter(Boolean);
});

const isSuperAdministrator = computed(
    () =>
        normalizedRoles.value.includes(
            "super administrator"
        )
);


const canCreateUsers = computed(() =>
    hasPermission(
        "administration.users.create"
    )
);

const canUpdateUsers = computed(() =>
    hasPermission(
        "administration.users.edit"
    )
);

const canActivateUsers = computed(() =>
    hasPermission(
        "administration.users.activate"
    )
);

const canDeactivateUsers = computed(() =>
    hasPermission(
        "administration.users.deactivate"
    )
);

const canChangeUserStatus = (user) =>
    user?.is_active
        ? canDeactivateUsers.value
        : canActivateUsers.value;

const canResetPassword = computed(
    () =>
        hasPermission(
            "administration.users.reset-password"
        )
);

const normalizeUserCollection = (
    response
) => {
    if (Array.isArray(response)) {
        return {
            users: response,
            meta: {},
        };
    }

    if (
        Array.isArray(response?.data)
    ) {
        return {
            users: response.data,
            meta:
                response.meta ??
                response,
        };
    }

    if (
        Array.isArray(
            response?.data?.data
        )
    ) {
        return {
            users:
                response.data.data,
            meta:
                response.data.meta ??
                response.data,
        };
    }

    return {
        users: [],
        meta: {},
    };
};

const updateSummary = (
    response = {}
) => {
    const responseSummary =
        response.summary ??
        response.data?.summary ??
        {};

    summary.total_users =
        responseSummary.total_users ??
        pagination.total;

    summary.active_users =
        responseSummary.active_users ??
        users.value.filter(
            (user) =>
                Boolean(user.is_active)
        ).length;

    summary.inactive_users =
        responseSummary.inactive_users ??
        users.value.filter(
            (user) =>
                !Boolean(user.is_active)
        ).length;
};

const fetchUsers = async () => {
    loading.value = true;

    try {
        const response =
            await userService.getUsers(
                filters
            );

        const normalized =
            normalizeUserCollection(
                response
            );

        users.value =
            normalized.users;

        const meta =
            normalized.meta ?? {};

        pagination.current_page =
            meta.current_page ??
            response.current_page ??
            1;

        pagination.last_page =
            meta.last_page ??
            response.last_page ??
            1;

        pagination.total =
            meta.total ??
            response.total ??
            users.value.length;

        updateSummary(response);
    } catch (error) {
        console.error(
            "Failed to load users:",
            error
        );

        toast.error(
            error?.response?.data
                ?.message ||
                error?.message ||
                "Failed to load users."
        );
    } finally {
        loading.value = false;
    }
};

const fetchOptions = async () => {
    try {
        const response =
            await userService.getOptions();

        const options =
            response?.data ??
            response ??
            {};

        roles.value =
            Array.isArray(
                options.roles
            )
                ? options.roles
                : [];

        statuses.value =
            Array.isArray(
                options.statuses
            )
                ? options.statuses
                : [];
    } catch (error) {
        console.error(
            "Failed to load user options:",
            error
        );

        toast.error(
            error?.response?.data
                ?.message ||
                error?.message ||
                "Failed to load user options."
        );
    }
};

const fetchAuthenticatedUser = async () => {
    try {
        const response = await api.get(
            "/me"
        );

        /*
         * Supports all common response formats:
         *
         * { user: {...} }
         * { data: { user: {...} } }
         * { data: {...} }
         * {...}
         */
        const responseData =
            response?.data ?? {};

        const user =
            responseData?.user ??
            responseData?.data?.user ??
            responseData?.data ??
            responseData;

        if (!user?.id) {
            throw new Error(
                "Invalid authenticated-user response."
            );
        }

        Object.assign(
            authenticatedUser,
            {
                id: user.id,
                name: user.name ?? "",
                email: user.email ?? "",
                roles: Array.isArray(
                    user.roles
                )
                    ? user.roles
                    : [],
                permissions:
                    Array.isArray(
                        user.permissions
                    )
                        ? user.permissions
                        : [],
            }
        );

        localStorage.setItem(
            "user",
            JSON.stringify({
                id: authenticatedUser.id,
                name:
                    authenticatedUser.name,
                email:
                    authenticatedUser.email,
                roles:
                    authenticatedUser.roles,
                permissions:
                    authenticatedUser.permissions,
            })
        );

        console.log(
            "Authenticated user:",
            authenticatedUser
        );

        console.log(
            "Roles:",
            normalizedRoles.value
        );

        console.log(
            "Permissions:",
            normalizedPermissions.value
        );

        console.log(
            "Super Administrator:",
            isSuperAdministrator.value
        );
    } catch (error) {
        console.error(
            "Failed to load authenticated user:",
            error
        );

        toast.error(
            error?.response?.data
                ?.message ||
                "Failed to load your permissions."
        );
    }
};

const refreshPage = async () => {
    /*
     * Load the authenticated account first,
     * because button visibility depends on it.
     */
    await fetchAuthenticatedUser();

    await Promise.all([
        fetchUsers(),
        fetchOptions(),
    ]);
};

const searchUsers = async () => {
    filters.page = 1;

    await fetchUsers();
};

const resetFilters = async () => {
    filters.search = "";
    filters.role = "";
    filters.status = "";
    filters.sort_by =
        "created_at";
    filters.sort_direction =
        "desc";
    filters.page = 1;
    filters.per_page = 15;

    await fetchUsers();
};

const openCreateModal = () => {
    selectedUser.value = null;
    serverErrors.value = {};
    showUserModal.value = true;
};

const openEditModal = async (
    user
) => {
    if (!user?.id) {
        toast.error(
            "User ID was not found."
        );

        return;
    }

    serverErrors.value = {};

    try {
        const response =
            await userService.getUser(
                user.id
            );

        const loadedUser =
            response?.data ??
            response?.user ??
            response;

        if (!loadedUser?.id) {
            throw new Error(
                "Invalid user information returned by the server."
            );
        }

        selectedUser.value =
            loadedUser;

        showUserModal.value = true;
    } catch (error) {
        console.error(
            "Failed to load user information:",
            error
        );

        toast.error(
            error?.response?.data
                ?.message ||
                error?.message ||
                "Failed to load user information."
        );
    }
};

const closeUserModal = () => {
    if (saving.value) {
        return;
    }

    showUserModal.value = false;
    selectedUser.value = null;
    serverErrors.value = {};
};

const saveUser = async (
    payload
) => {
    if (saving.value) {
        return;
    }

    saving.value = true;
    serverErrors.value = {};

    try {
        const normalizedPayload = {
            name:
                payload.name?.trim() ??
                "",

            email:
                payload.email?.trim() ??
                "",

            roles: Array.isArray(
                payload.roles
            )
                ? payload.roles.map(
                      (roleId) =>
                          Number(
                              roleId
                          )
                  )
                : [],

            is_active: Boolean(
                payload.is_active
            ),
        };

        if (!selectedUser.value?.id) {
            normalizedPayload.password =
                payload.password ?? "";

            normalizedPayload.password_confirmation =
                payload.password_confirmation ?? "";
        }

        let response;

        if (
            selectedUser.value?.id
        ) {
            response =
                await userService.updateUser(
                    selectedUser.value
                        .id,
                    normalizedPayload
                );

            toast.success(
                response?.message ||
                    "User updated successfully."
            );
        } else {
            response =
                await userService.createUser(
                    normalizedPayload
                );

            toast.success(
                response?.message ||
                    "User created successfully."
            );
        }

        showUserModal.value =
            false;

        selectedUser.value =
            null;

        await fetchUsers();
    } catch (error) {
        console.error(
            "Save user error:",
            error
        );

        console.error(
            "Save user response:",
            error?.response?.data
        );

        if (
            error?.response?.status ===
            422
        ) {
            serverErrors.value =
                error.response.data
                    .errors ?? {};

            toast.error(
                error.response.data
                    .message ||
                    "Please review the user information."
            );

            return;
        }

        toast.error(
            error?.response?.data
                ?.message ||
                error?.message ||
                "Failed to save user."
        );
    } finally {
        saving.value = false;
    }
};

const openResetPasswordModal = (
    user
) => {
    if (!user?.id) {
        toast.error(
            "User ID was not found."
        );

        return;
    }

    selectedUser.value = {
        ...user,
    };

    passwordServerErrors.value =
        {};

    showPasswordModal.value =
        true;
};

const closePasswordModal = () => {
    if (
        resettingPassword.value
    ) {
        return;
    }

    showPasswordModal.value =
        false;

    selectedUser.value = null;

    passwordServerErrors.value =
        {};
};

const resetUserPassword = async (
    payload
) => {
    const userId =
        selectedUser.value?.id;

    if (
        resettingPassword.value ||
        !userId
    ) {
        return;
    }

    resettingPassword.value =
        true;

    passwordServerErrors.value =
        {};

    try {
        const response =
            await userService.resetUserPassword(
                userId,
                payload
            );

        toast.success(
            response?.message ||
                "Password reset successfully."
        );

        showPasswordModal.value =
            false;

        selectedUser.value = null;
    } catch (error) {
        console.error(
            "Failed to reset password:",
            error
        );

        if (
            error?.response?.status ===
            422
        ) {
            passwordServerErrors.value =
                error.response.data
                    .errors ?? {};

            toast.error(
                error.response.data
                    .message ||
                    "Please review the password."
            );

            return;
        }

        toast.error(
            error?.response?.data
                ?.message ||
                error?.message ||
                "Failed to reset password."
        );
    } finally {
        resettingPassword.value =
            false;
    }
};

const toggleUserStatus = async (
    user
) => {
    if (
        Number(user.id) ===
        Number(
            authenticatedUser.id
        )
    ) {
        toast.error(
            "You cannot deactivate your own account."
        );

        return;
    }

    if (
        updatingStatusId.value
    ) {
        return;
    }

    updatingStatusId.value =
        user.id;

    try {
        const response =
            await userService.updateUserStatus(
                user.id,
                !Boolean(
                    user.is_active
                )
            );

        toast.success(
            response?.message ||
                "User status updated successfully."
        );

        await fetchUsers();
    } catch (error) {
        console.error(
            "Failed to update user status:",
            error
        );

        toast.error(
            error?.response?.data
                ?.message ||
                error?.message ||
                "Failed to update user status."
        );
    } finally {
        updatingStatusId.value =
            null;
    }
};

const previousPage = async () => {
    if (
        pagination.current_page <=
        1
    ) {
        return;
    }

    filters.page =
        pagination.current_page -
        1;

    await fetchUsers();
};

const nextPage = async () => {
    if (
        pagination.current_page >=
        pagination.last_page
    ) {
        return;
    }

    filters.page =
        pagination.current_page +
        1;

    await fetchUsers();
};

const goToPage = async (
    page
) => {
    if (
        page < 1 ||
        page >
            pagination.last_page ||
        page ===
            pagination.current_page
    ) {
        return;
    }

    filters.page = page;

    await fetchUsers();
};

const displayRoles = (user) => {
    if (
        !Array.isArray(
            user.roles
        ) ||
        user.roles.length === 0
    ) {
        return "No role";
    }

    return user.roles
        .map((role) => {
            if (
                typeof role ===
                "string"
            ) {
                return role.replaceAll(
                    "-",
                    " "
                );
            }

            return role?.name?.replaceAll(
                "-",
                " "
            );
        })
        .filter(Boolean)
        .join(", ");
};

const formatDate = (date) => {
    if (!date) {
        return "—";
    }

    const parsedDate =
        new Date(date);

    if (
        Number.isNaN(
            parsedDate.getTime()
        )
    ) {
        return date;
    }

    return new Intl.DateTimeFormat(
        "en-PH",
        {
            year: "numeric",
            month: "short",
            day: "2-digit",
        }
    ).format(parsedDate);
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
                    title="Users"
                    description="Manage system users, assigned roles, account status, and passwords."
                />

                <button
                    v-if="canCreateUsers"
                    type="button"
                    class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="openCreateModal"
                >
                    Add User
                </button>
            </div>

            <div
                class="grid gap-4 md:grid-cols-3"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-xs font-semibold uppercase text-slate-500"
                    >
                        Total Users
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-slate-900"
                    >
                        {{
                            summary.total_users ||
                            0
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-xs font-semibold uppercase text-slate-500"
                    >
                        Active Users
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-emerald-700"
                    >
                        {{
                            summary.active_users ||
                            0
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-xs font-semibold uppercase text-slate-500"
                    >
                        Inactive Users
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-red-700"
                    >
                        {{
                            summary.inactive_users ||
                            0
                        }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div
                    class="grid gap-3 lg:grid-cols-[minmax(220px,1fr)_180px_160px_130px_auto]"
                >
                    <input
                        v-model="
                            filters.search
                        "
                        type="text"
                        placeholder="Search name or email..."
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        @keyup.enter="
                            searchUsers
                        "
                    />

                    <select
                        v-model="
                            filters.role
                        "
                        class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        @change="
                            searchUsers
                        "
                    >
                        <option value="">
                            All roles
                        </option>

                        <option
                            v-for="role in roles"
                            :key="role.id"
                            :value="
                                role.name
                            "
                        >
                            {{
                                role.name?.replaceAll(
                                    "-",
                                    " "
                                )
                            }}
                        </option>
                    </select>

                    <select
                        v-model="
                            filters.status
                        "
                        class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        @change="
                            searchUsers
                        "
                    >
                        <option value="">
                            All statuses
                        </option>

                        <option
                            v-for="status in statuses"
                            :key="
                                status.value
                            "
                            :value="
                                status.value
                            "
                        >
                            {{
                                status.label
                            }}
                        </option>
                    </select>

                    <select
                        v-model.number="
                            filters.per_page
                        "
                        class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-emerald-500"
                        @change="
                            searchUsers
                        "
                    >
                        <option
                            :value="15"
                        >
                            15 rows
                        </option>

                        <option
                            :value="25"
                        >
                            25 rows
                        </option>

                        <option
                            :value="50"
                        >
                            50 rows
                        </option>
                    </select>

                    <div
                        class="flex gap-2"
                    >
                        <button
                            type="button"
                            class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            @click="
                                searchUsers
                            "
                        >
                            Search
                        </button>

                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                            @click="
                                resetFilters
                            "
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <TableSkeleton
                v-if="loading"
            />

            <div
                v-else
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
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
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500"
                                >
                                    User
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500"
                                >
                                    Role
                                </th>

                                <th
                                    class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500"
                                >
                                    Created
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
                                v-for="user in users"
                                :key="
                                    user.id
                                "
                                class="transition hover:bg-slate-50"
                            >
                                <td
                                    class="px-5 py-4"
                                >
                                    <div
                                        class="flex items-center gap-3"
                                    >
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold uppercase text-emerald-700"
                                        >
                                            {{
                                                user.name?.charAt(
                                                    0
                                                ) ||
                                                "U"
                                            }}
                                        </div>

                                        <div>
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{
                                                    user.name
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                {{
                                                    user.email
                                                }}
                                            </p>

                                            <p
                                                v-if="
                                                    Number(
                                                        user.id
                                                    ) ===
                                                    Number(
                                                        authenticatedUser.id
                                                    )
                                                "
                                                class="mt-1 text-xs font-semibold text-emerald-700"
                                            >
                                                Current account
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm capitalize text-slate-700"
                                >
                                    {{
                                        displayRoles(
                                            user
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-center"
                                >
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            user.is_active
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-red-50 text-red-700'
                                        "
                                    >
                                        {{
                                            user.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-600"
                                >
                                    {{
                                        formatDate(
                                            user.created_at
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <div
                                        class="flex flex-wrap justify-end gap-2"
                                    >
                                        <button
                                            v-if="
                                                canUpdateUsers
                                            "
                                            type="button"
                                            class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                                            @click="
                                                openEditModal(
                                                    user
                                                )
                                            "
                                        >
                                            Edit
                                        </button>

                                        <button
                                            v-if="
                                                canChangeUserStatus(
                                                    user
                                                )
                                            "
                                            type="button"
                                            :disabled="
                                                Number(
                                                    user.id
                                                ) ===
                                                    Number(
                                                        authenticatedUser.id
                                                    ) ||
                                                updatingStatusId ===
                                                    user.id
                                            "
                                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-40"
                                            :class="
                                                user.is_active
                                                    ? 'bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                    : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                            "
                                            @click="
                                                toggleUserStatus(
                                                    user
                                                )
                                            "
                                        >
                                            {{
                                                updatingStatusId ===
                                                user.id
                                                    ? "Updating..."
                                                    : user.is_active
                                                      ? "Deactivate"
                                                      : "Activate"
                                            }}
                                        </button>

                                        <button
                                            v-if="
                                                canResetPassword
                                            "
                                            type="button"
                                            class="rounded-lg bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-100"
                                            @click="
                                                openResetPasswordModal(
                                                    user
                                                )
                                            "
                                        >
                                            Reset Password
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    users.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="px-6 py-14 text-center text-slate-500"
                                >
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                v-if="
                    !loading &&
                    pagination.total >
                        0
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
        </div>

        <UserFormModal
            v-model:show="
                showUserModal
            "
            :user="selectedUser"
            :roles="roles"
            :saving="saving"
            :server-errors="
                serverErrors
            "
            @close="
                closeUserModal
            "
            @submit="saveUser"
        />

        <ResetPasswordModal
            v-model:show="
                showPasswordModal
            "
            :user="selectedUser"
            :saving="
                resettingPassword
            "
            :server-errors="
                passwordServerErrors
            "
            @close="
                closePasswordModal
            "
            @submit="
                resetUserPassword
            "
        />
    </AppLayout>
</template>