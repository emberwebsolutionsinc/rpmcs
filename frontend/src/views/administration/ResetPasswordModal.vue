<script setup>
import {
    computed,
    reactive,
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
    password: "",
    password_confirmation: "",
});

const passwordsMatch = computed(() => {
    if (
        !form.password ||
        !form.password_confirmation
    ) {
        return true;
    }

    return (
        form.password ===
        form.password_confirmation
    );
});

const resetForm = () => {
    form.password = "";
    form.password_confirmation = "";
};

const closeModal = () => {
    if (props.saving) {
        return;
    }

    resetForm();

    emit("update:show", false);
    emit("close");
};

const submitForm = () => {
    if (
        props.saving ||
        !passwordsMatch.value
    ) {
        return;
    }

    emit("submit", {
        password: form.password,
        password_confirmation:
            form.password_confirmation,
    });
};

const fieldError = (field) => {
    return props.serverErrors?.[field]?.[0];
};

watch(
    () => props.show,
    (show) => {
        if (show) {
            resetForm();
        }
    }
);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 p-4"
            @click.self="closeModal"
        >
            <div
                class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <form @submit.prevent="submitForm">
                    <div
                        class="border-b border-slate-200 px-6 py-5"
                    >
                        <div
                            class="flex items-start gap-4"
                        >
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xl font-bold text-violet-600"
                            >
                                🔒
                            </div>

                            <div>
                                <h2
                                    class="text-lg font-bold text-slate-900"
                                >
                                    Reset Password
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Set a new password for
                                    <span
                                        class="font-semibold text-slate-700"
                                    >
                                        {{
                                            user?.name
                                        }}
                                    </span>
                                    .
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5 px-6 py-5">
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                New Password
                            </label>

                            <input
                                v-model="
                                    form.password
                                "
                                type="password"
                                autocomplete="new-password"
                                class="w-full rounded-lg border px-4 py-2.5 text-sm outline-none focus:ring-2"
                                :class="
                                    fieldError(
                                        'password'
                                    )
                                        ? 'border-red-300 focus:border-red-500 focus:ring-red-100'
                                        : 'border-slate-300 focus:border-violet-500 focus:ring-violet-100'
                                "
                                placeholder="Enter new password"
                            />

                            <p
                                v-if="
                                    fieldError(
                                        'password'
                                    )
                                "
                                class="mt-1.5 text-xs font-medium text-red-600"
                            >
                                {{
                                    fieldError(
                                        "password"
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Confirm Password
                            </label>

                            <input
                                v-model="
                                    form.password_confirmation
                                "
                                type="password"
                                autocomplete="new-password"
                                class="w-full rounded-lg border px-4 py-2.5 text-sm outline-none focus:ring-2"
                                :class="
                                    !passwordsMatch
                                        ? 'border-red-300 focus:border-red-500 focus:ring-red-100'
                                        : 'border-slate-300 focus:border-violet-500 focus:ring-violet-100'
                                "
                                placeholder="Confirm new password"
                            />

                            <p
                                v-if="
                                    !passwordsMatch
                                "
                                class="mt-1.5 text-xs font-medium text-red-600"
                            >
                                Password confirmation does
                                not match.
                            </p>
                        </div>

                        <div
                            class="rounded-lg bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800"
                        >
                            The user will need to use the
                            new password the next time they
                            log in.
                        </div>
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4"
                    >
                        <button
                            type="button"
                            :disabled="saving"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="
                                saving ||
                                !passwordsMatch ||
                                !form.password ||
                                !form.password_confirmation
                            "
                            class="rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                saving
                                    ? "Resetting..."
                                    : "Reset Password"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>