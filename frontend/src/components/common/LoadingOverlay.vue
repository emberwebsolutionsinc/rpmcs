<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    title: {
        type: String,
        default: "Processing...",
    },

    description: {
        type: String,
        default: "Please wait while we process your request.",
    },
});
</script>

<template>
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
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-sm rounded-2xl bg-white p-8 shadow-2xl"
            >
                <div class="flex flex-col items-center">
                    <!-- Spinner -->
                    <div class="relative h-16 w-16">
                        <div
                            class="absolute inset-0 rounded-full border-4 border-slate-200"
                        ></div>

                        <div
                            class="absolute inset-0 rounded-full border-4 border-emerald-600 border-t-transparent animate-spin"
                        ></div>
                    </div>

                    <h3
                        class="mt-5 text-lg font-semibold text-slate-900"
                    >
                        {{ title }}
                    </h3>

                    <p
                        class="mt-2 text-center text-sm text-slate-500"
                    >
                        {{ description }}
                    </p>
                </div>
            </div>
        </div>
    </Transition>
</template>