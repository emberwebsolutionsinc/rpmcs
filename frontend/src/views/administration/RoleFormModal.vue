<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    role: {
        type: Object,
        default: null,
    },

    groupedPermissions: {
        type: Array,
        default: () => [],
    },

    saving: {
        type: Boolean,
        default: false,
    },

    serverErrors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits([
    "update:show",
    "close",
    "submit",
]);

const form = reactive({
    name: "",
    permissions: [],
});

const isEdit = computed(() => Boolean(props.role?.id));

const title = computed(() => {
    return isEdit.value
        ? "Edit Role"
        : "Add Role";
});

const submitLabel = computed(() => {
    if (props.saving) {
        return isEdit.value
            ? "Saving Changes..."
            : "Creating Role...";
    }

    return isEdit.value
        ? "Save Changes"
        : "Create Role";
});

const resetForm = () => {
    form.name = "";
    form.permissions = [];
};

const populateForm = () => {
    form.name = props.role?.name ?? "";

    form.permissions =
        props.role?.permissions?.map(
            (permission) => permission.id
        ) ?? [];
};

watch(
    () => props.show,
    (visible) => {
        if (visible) {
            populateForm();
        } else {
            resetForm();
        }
    },
    {
        immediate: true,
    }
);

watch(
    () => props.role,
    () => {
        if (props.show) {
            populateForm();
        }
    },
    {
        deep: true,
    }
);

const errorMessage = (field) => {
    const error = props.serverErrors?.[field];

    if (Array.isArray(error)) {
        return error[0] ?? "";
    }

    return error ?? "";
};

const isPermissionSelected = (permissionId) => {
    return form.permissions.includes(permissionId);
};

const togglePermission = (permissionId) => {
    if (isPermissionSelected(permissionId)) {
        form.permissions = form.permissions.filter(
            (id) => id !== permissionId
        );

        return;
    }

    form.permissions = [
        ...form.permissions,
        permissionId,
    ];
};

const isModuleSelected = (group) => {
    const permissionIds =
        group.permissions.map(
            (permission) => permission.id
        );

    return (
        permissionIds.length > 0 &&
        permissionIds.every((id) =>
            form.permissions.includes(id)
        )
    );
};

const toggleModule = (group) => {
    const permissionIds =
        group.permissions.map(
            (permission) => permission.id
        );

    if (isModuleSelected(group)) {
        form.permissions =
            form.permissions.filter(
                (id) => !permissionIds.includes(id)
            );

        return;
    }

    form.permissions = [
        ...new Set([
            ...form.permissions,
            ...permissionIds,
        ]),
    ];
};

const submit = () => {
    if (props.saving) {
        return;
    }

    emit("submit", {
        name: form.name.trim(),
        permissions: [...form.permissions],
    });
};

const close = () => {
    if (props.saving) {
        return;
    }

    emit("update:show", false);
    emit("close");
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4"
            @click.self="close"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <div
                    class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5"
                >
                    <div>
                        <h2
                            class="text-lg font-bold text-slate-900"
                        >
                            {{ title }}
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Configure the role name and assigned
                            permissions.
                        </p>
                    </div>

                    <button
                        type="button"
                        :disabled="saving"
                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="close"
                    >
                        ✕
                    </button>
                </div>

                <div
                    class="flex-1 overflow-y-auto px-6 py-5"
                >
                    <div class="space-y-6">
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-slate-700"
                            >
                                Role Name

                                <span class="text-red-500">
                                    *
                                </span>
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Example: manager"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                @keyup.enter="submit"
                            />

                            <p
                                v-if="errorMessage('name')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ errorMessage("name") }}
                            </p>
                        </div>

                        <div>
                            <div class="mb-3">
                                <h3
                                    class="font-semibold text-slate-900"
                                >
                                    Permissions
                                </h3>

                                <p
                                    class="text-sm text-slate-500"
                                >
                                    Select the permissions assigned
                                    to this role.
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div
                                    v-for="group in groupedPermissions"
                                    :key="group.module"
                                    class="rounded-xl border border-slate-200"
                                >
                                    <div
                                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3"
                                    >
                                        <label
                                            class="flex cursor-pointer items-center gap-3"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    isModuleSelected(
                                                        group
                                                    )
                                                "
                                                class="h-4 w-4 rounded border-slate-300 text-emerald-600"
                                                @change="
                                                    toggleModule(
                                                        group
                                                    )
                                                "
                                            />

                                            <span
                                                class="font-semibold capitalize text-slate-900"
                                            >
                                                {{
                                                    group.module
                                                        ?.replaceAll(
                                                            ".",
                                                            " "
                                                        )
                                                        ?.replaceAll(
                                                            "-",
                                                            " "
                                                        )
                                                }}
                                            </span>
                                        </label>

                                        <span
                                            class="text-xs text-slate-500"
                                        >
                                            {{
                                                group.permissions
                                                    .length
                                            }}
                                            permissions
                                        </span>
                                    </div>

                                    <div
                                        class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3"
                                    >
                                        <label
                                            v-for="permission in group.permissions"
                                            :key="permission.id"
                                            class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    isPermissionSelected(
                                                        permission.id
                                                    )
                                                "
                                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600"
                                                @change="
                                                    togglePermission(
                                                        permission.id
                                                    )
                                                "
                                            />

                                            <div>
                                                <p
                                                    class="text-sm font-medium text-slate-900"
                                                >
                                                    {{
                                                        permission.action
                                                            ?.replaceAll(
                                                                ".",
                                                                " "
                                                            )
                                                            ?.replaceAll(
                                                                "-",
                                                                " "
                                                            )
                                                    }}
                                                </p>

                                                <p
                                                    class="mt-1 text-xs text-slate-500"
                                                >
                                                    {{
                                                        permission.name
                                                    }}
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        groupedPermissions.length ===
                                        0
                                    "
                                    class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500"
                                >
                                    No permissions found.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4"
                >
                    <button
                        type="button"
                        :disabled="saving"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="close"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="saving"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="submit"
                    >
                        {{ submitLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>