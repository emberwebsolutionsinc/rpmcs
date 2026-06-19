<script setup>
import { reactive, watch } from "vue";

const props = defineProps({
    show: Boolean,
    project: Object,
});

const emit = defineEmits([
    "close",
    "submit",
]);

const form = reactive({
    project_code: "",
    project_name: "",
    developer_name: "",
    province: "",
    municipality: "",
    barangay: "",
    street: "",
    status: "active",
});

watch(
    () => props.project,
    (project) => {
        if (!project) return;

        Object.assign(form, {
            project_code: project.project_code ?? "",
            project_name: project.project_name ?? "",
            developer_name: project.developer_name ?? "",
            province: project.province ?? "",
            municipality: project.municipality ?? "",
            barangay: project.barangay ?? "",
            street: project.street ?? "",
            status: project.status ?? "active",
        });
    },
    { immediate: true }
);

const submit = () => {
    emit("submit", { ...form });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white rounded-2xl w-full max-w-2xl p-6"
        >
            <h2
                class="text-xl font-bold mb-5"
            >
                Property Project
            </h2>

            <div
                class="grid md:grid-cols-2 gap-4"
            >
                <input
                    v-model="form.project_code"
                    placeholder="Project Code"
                    class="border rounded-lg px-3 py-2"
                />

                <input
                    v-model="form.project_name"
                    placeholder="Project Name"
                    class="border rounded-lg px-3 py-2"
                />

                <input
                    v-model="form.developer_name"
                    placeholder="Developer"
                    class="border rounded-lg px-3 py-2"
                />

                <input
                    v-model="form.province"
                    placeholder="Province"
                    class="border rounded-lg px-3 py-2"
                />

                <input
                    v-model="form.municipality"
                    placeholder="Municipality"
                    class="border rounded-lg px-3 py-2"
                />

                <input
                    v-model="form.barangay"
                    placeholder="Barangay"
                    class="border rounded-lg px-3 py-2"
                />

                <input
                    v-model="form.street"
                    placeholder="Street"
                    class="border rounded-lg px-3 py-2"
                />

                <select
                    v-model="form.status"
                    class="border rounded-lg px-3 py-2"
                >
                    <option value="active">
                        Active
                    </option>

                    <option value="inactive">
                        Inactive
                    </option>
                </select>
            </div>

            <div
                class="flex justify-end gap-3 mt-6"
            >
                <button
                    @click="emit('close')"
                    class="px-4 py-2 border rounded-lg"
                >
                    Cancel
                </button>

                <button
                    @click="submit"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg"
                >
                    Save
                </button>
            </div>
        </div>
    </div>
</template>