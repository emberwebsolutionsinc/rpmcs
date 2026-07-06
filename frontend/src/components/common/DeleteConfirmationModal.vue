<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    title: {
        type: String,
        default: "Delete Record",
    },

    message: {
        type: String,
        default: "Are you sure you want to delete this record?",
    },

    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "close",
    "confirm",
]);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4"
            >
                <Transition
                    enter-active-class="transition duration-200"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-150"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div
                        class="w-full max-w-md rounded-2xl bg-white shadow-2xl"
                    >
                        <div class="border-b px-6 py-5">
                            <div
                                class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100"
                            >
                                <svg
                                    class="h-8 w-8 text-red-600"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M6.938 4h10.124c1.54 0 2.502 1.667 1.732 3L13.732 18c-.77 1.333-2.694 1.333-3.464 0L5.206 7c-.77-1.333.192-3 1.732-3z"
                                    />
                                </svg>
                            </div>

                            <h2
                                class="text-xl font-bold text-slate-900"
                            >
                                {{ title }}
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500"
                            >
                                {{ message }}
                            </p>
                        </div>

                        <div
                            class="flex justify-end gap-3 px-6 py-5"
                        >
                            <button
                                @click="$emit('close')"
                                :disabled="loading"
                                class="rounded-lg border border-slate-300 px-5 py-2.5 font-medium hover:bg-slate-50"
                            >
                                Cancel
                            </button>

                            <button
                                @click="$emit('confirm')"
                                :disabled="loading"
                                class="rounded-lg bg-red-600 px-5 py-2.5 font-medium text-white hover:bg-red-700"
                            >
                                {{
                                    loading
                                        ? "Deleting..."
                                        : "Delete"
                                }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>