<script setup>
import {
    computed,
    onMounted,
    ref,
    watch,
} from "vue";

import {
    useRoute,
    useRouter,
} from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";

import ClientForm from "@/views/client-management/ClientForm.vue";

import clientService from "@/services/clientService";
import toast from "@/utils/toast";

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const saving = ref(false);

const client = ref(null);
const serverErrors = ref({});

const clientId = computed(() => route.params.id);

const loadClient = async () => {
    if (!clientId.value) {
        toast.error("Client ID is missing.");
        return;
    }

    loading.value = true;
    serverErrors.value = {};

    try {
        const response = await clientService.getClient(
            clientId.value
        );

        client.value =
            response.data.data ?? null;

        if (!client.value) {
            toast.error("Client not found.");
        }
    } catch (error) {
        console.error(error);

        if (error?.response?.status === 404) {
            toast.error("Client not found.");
            return;
        }

        toast.error(
            error?.response?.data?.message ||
                "Failed to load client information."
        );
    } finally {
        loading.value = false;
    }
};

const cancel = () => {
    router.push({
        name: "client-details",
        params: {
            id: clientId.value,
        },
    });
};

const updateClient = async (payload) => {
    if (!clientId.value) {
        toast.error("Client ID is missing.");
        return;
    }

    saving.value = true;
    serverErrors.value = {};

    try {
        const response = await clientService.updateClient(
            clientId.value,
            payload
        );

        toast.success(
            response.data.message ||
                "Client updated successfully."
        );

        router.push({
            name: "client-details",
            params: {
                id: clientId.value,
            },
        });
    } catch (error) {
        console.error(error);

        if (error?.response?.status === 422) {
            serverErrors.value =
                error.response.data.errors ?? {};

            toast.error(
                error.response.data.message ||
                    "Please review the highlighted fields."
            );

            return;
        }

        if (error?.response?.status === 404) {
            toast.error("Client not found.");
            return;
        }

        toast.error(
            error?.response?.data?.message ||
                "Failed to update client."
        );
    } finally {
        saving.value = false;
    }
};

onMounted(loadClient);

watch(
    () => route.params.id,
    async (newId, oldId) => {
        if (!newId || newId === oldId) return;

        client.value = null;

        await loadClient();
    }
);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Edit Client"
                description="Update the client profile, contact details, and employment information."
            />

            <TableSkeleton v-if="loading" />

            <ClientForm
                v-else-if="client"
                :model-value="client"
                mode="edit"
                :saving="saving"
                :server-errors="serverErrors"
                @submit="updateClient"
                @cancel="cancel"
            />

            <div
                v-else
                class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500 shadow-sm"
            >
                Client not found.
            </div>
        </div>
    </AppLayout>
</template>