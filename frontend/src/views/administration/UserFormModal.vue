<script setup>
import {
    computed,
    reactive,
    ref,
    watch,
} from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    user: {
        type: Object,
        default: null,
    },

    roles: {
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

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = reactive({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    roles: [],
    is_active: true,
});

const isEditing = computed(() => {
    return Boolean(props.user?.id);
});

const modalTitle = computed(() => {
    return isEditing.value
        ? "Edit User"
        : "Add User";
});

const submitLabel = computed(() => {
    if (props.saving) {
        return isEditing.value
            ? "Updating..."
            : "Creating...";
    }

    return isEditing.value
        ? "Update User"
        : "Create User";
});

const normalizeBoolean = (value) => {
    return (
        value === true ||
        value === 1 ||
        value === "1"
    );
};

const normalizeRoles = (assignedRoles) => {
    if (!Array.isArray(assignedRoles)) {
        return [];
    }

    return assignedRoles
        .map((role) => {
            if (typeof role === "number") {
                return role;
            }

            if (typeof role === "string") {
                const matchedRole =
                    props.roles.find(
                        (availableRole) =>
                            availableRole.name === role
                    );

                return matchedRole?.id;
            }

            return role?.id;
        })
        .filter(
            (roleId) =>
                roleId !== null &&
                roleId !== undefined
        )
        .map((roleId) => Number(roleId));
};

const resetForm = () => {
    form.name = props.user?.name ?? "";
    form.email = props.user?.email ?? "";
    form.password = "";
    form.password_confirmation = "";
    form.roles = normalizeRoles(
        props.user?.roles
    );

    form.is_active = props.user
        ? normalizeBoolean(
              props.user.is_active
          )
        : true;

    showPassword.value = false;
    showPasswordConfirmation.value = false;
};

const fieldError = (field) => {
    return (
        props.serverErrors?.[
            field
        ]?.[0] ?? ""
    );
};

const closeModal = () => {
    if (props.saving) {
        return;
    }

    emit("update:show", false);
    emit("close");
};

const toggleActiveStatus = () => {
    if (props.saving) {
        return;
    }

    form.is_active =
        !form.is_active;
};

const submitForm = () => {
    if (props.saving) {
        return;
    }

    const payload = {
        name: form.name.trim(),
        email: form.email.trim(),
        roles: form.roles.map(
            (roleId) => Number(roleId)
        ),
        is_active: Boolean(
            form.is_active
        ),
    };

    if (!isEditing.value) {
        payload.password =
            form.password;

        payload.password_confirmation =
            form.password_confirmation;
    }

    emit("submit", payload);
};

watch(
    () => props.show,
    (show) => {
        if (show) {
            resetForm();
        }
    }
);

watch(
    [
        () => props.user,
        () => props.roles,
    ],
    () => {
        if (props.show) {
            resetForm();
        }
    },
    {
        deep: true,
    }
);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4 py-6"
            >
                <div
                    class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl"
                >
                    <div
                        class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-5"
                    >
                        <div>
                            <h2
                                class="text-lg font-bold text-slate-900"
                            >
                                {{ modalTitle }}
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{
                                    isEditing
                                        ? "Update the user information, assigned roles, and account status."
                                        : "Create a new user, set a password, and assign the appropriate roles."
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="saving"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="closeModal"
                        >
                            ✕
                        </button>
                    </div>

                    <form
                        class="space-y-5 p-6"
                        @submit.prevent="submitForm"
                    >
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                Full Name
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                placeholder="Enter full name"
                            />

                            <p
                                v-if="fieldError('name')"
                                class="mt-1.5 text-xs font-medium text-red-600"
                            >
                                {{ fieldError("name") }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-sm font-semibold text-slate-700"
                            >
                                Email Address
                            </label>

                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                placeholder="Enter email address"
                            />

                            <p
                                v-if="fieldError('email')"
                                class="mt-1.5 text-xs font-medium text-red-600"
                            >
                                {{ fieldError("email") }}
                            </p>
                        </div>

                        <div
                            v-if="!isEditing"
                            class="grid gap-5 sm:grid-cols-2"
                        >
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-semibold text-slate-700"
                                >
                                    Password
                                </label>

                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="
                                            showPassword
                                                ? 'text'
                                                : 'password'
                                        "
                                        required
                                        minlength="8"
                                        autocomplete="new-password"
                                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 pr-16 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                        placeholder="Minimum 8 characters"
                                    />

                                    <button
                                        type="button"
                                        class="absolute inset-y-0 right-0 px-4 text-xs font-semibold text-slate-600 hover:text-emerald-700"
                                        @click="
                                            showPassword =
                                                !showPassword
                                        "
                                    >
                                        {{
                                            showPassword
                                                ? "Hide"
                                                : "Show"
                                        }}
                                    </button>
                                </div>

                                <p
                                    v-if="fieldError('password')"
                                    class="mt-1.5 text-xs font-medium text-red-600"
                                >
                                    {{ fieldError("password") }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-semibold text-slate-700"
                                >
                                    Confirm Password
                                </label>

                                <div class="relative">
                                    <input
                                        v-model="
                                            form.password_confirmation
                                        "
                                        :type="
                                            showPasswordConfirmation
                                                ? 'text'
                                                : 'password'
                                        "
                                        required
                                        minlength="8"
                                        autocomplete="new-password"
                                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 pr-16 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                        placeholder="Repeat password"
                                    />

                                    <button
                                        type="button"
                                        class="absolute inset-y-0 right-0 px-4 text-xs font-semibold text-slate-600 hover:text-emerald-700"
                                        @click="
                                            showPasswordConfirmation =
                                                !showPasswordConfirmation
                                        "
                                    >
                                        {{
                                            showPasswordConfirmation
                                                ? "Hide"
                                                : "Show"
                                        }}
                                    </button>
                                </div>

                                <p
                                    v-if="
                                        fieldError(
                                            'password_confirmation'
                                        )
                                    "
                                    class="mt-1.5 text-xs font-medium text-red-600"
                                >
                                    {{
                                        fieldError(
                                            "password_confirmation"
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Assigned Roles
                            </label>

                            <div
                                class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2"
                            >
                                <label
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 transition hover:border-emerald-300"
                                >
                                    <input
                                        v-model="form.roles"
                                        type="checkbox"
                                        :value="Number(role.id)"
                                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    />

                                    <span
                                        class="text-sm font-medium capitalize text-slate-700"
                                    >
                                        {{
                                            role.name?.replaceAll(
                                                "-",
                                                " "
                                            )
                                        }}
                                    </span>
                                </label>
                            </div>

                            <p
                                v-if="fieldError('roles')"
                                class="mt-1.5 text-xs font-medium text-red-600"
                            >
                                {{ fieldError("roles") }}
                            </p>

                            <p
                                v-else-if="fieldError('roles.0')"
                                class="mt-1.5 text-xs font-medium text-red-600"
                            >
                                {{ fieldError("roles.0") }}
                            </p>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-4"
                        >
                            <div>
                                <p
                                    class="text-sm font-semibold text-slate-800"
                                >
                                    Active Account
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    {{
                                        form.is_active
                                            ? "This user can access and log into the system."
                                            : "This user is inactive and should not be allowed to log in."
                                    }}
                                </p>
                            </div>

                            <button
                                type="button"
                                role="switch"
                                :aria-checked="form.is_active"
                                :disabled="saving"
                                class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition disabled:cursor-not-allowed disabled:opacity-50"
                                :class="
                                    form.is_active
                                        ? 'bg-emerald-600'
                                        : 'bg-slate-300'
                                "
                                @click="toggleActiveStatus"
                            >
                                <span
                                    class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform"
                                    :class="
                                        form.is_active
                                            ? 'translate-x-6'
                                            : 'translate-x-1'
                                    "
                                />
                            </button>
                        </div>

                        <p
                            v-if="fieldError('is_active')"
                            class="text-xs font-medium text-red-600"
                        >
                            {{ fieldError("is_active") }}
                        </p>

                        <div
                            class="flex justify-end gap-3 border-t border-slate-200 pt-5"
                        >
                            <button
                                type="button"
                                :disabled="saving"
                                class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                                @click="closeModal"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                :disabled="saving"
                                class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ submitLabel }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
