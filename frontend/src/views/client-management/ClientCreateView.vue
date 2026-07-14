<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";

import ClientForm from "@/views/client-management/ClientForm.vue";

import clientService from "@/services/clientService";
import toast from "@/utils/toast";

const router = useRouter();

const saving = ref(false);
const serverErrors = ref({});

const cancel = () => {
    router.push({
        name: "clients",
    });
};

const saveClient = async (payload) => {
    saving.value = true;
    serverErrors.value = {};

    try {
        const response =
            await clientService.createClient(payload);

        const createdClient =
            response.data.data ?? null;

        toast.success(
            response.data.message ||
                "Client created successfully."
        );

        if (createdClient?.id) {
            router.push({
                name: "client-details",
                params: {
                    id: createdClient.id,
                },
            });

            return;
        }

        router.push({
            name: "clients",
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

        toast.error(
            error?.response?.data?.message ||
                "Failed to create client."
        );
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <PageHeader
                title="Add Client"
                description="Create a new buyer or client profile."
            />

            <ClientForm
                mode="create"
                :saving="saving"
                :server-errors="serverErrors"
                @submit="saveClient"
                @cancel="cancel"
            />
        </div>
    </AppLayout>
</template>