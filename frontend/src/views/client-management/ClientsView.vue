<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";

import AppLayout from "@/layouts/AppLayout.vue";
import PageHeader from "@/components/common/PageHeader.vue";
import TableSkeleton from "@/components/common/TableSkeleton.vue";
import Pagination from "@/components/common/Pagination.vue";

import clientService from "@/services/clientService";
import toast from "@/utils/toast";

const router = useRouter();

const loading = ref(false);
const clients = ref([]);

const filters = reactive({
    search: "",
    status: "",
    per_page: 10,
    page: 1,
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
});

const summary = reactive({
    total_clients: 0,
    active_clients: 0,
    clients_with_sales: 0,
    total_contract_price: 0,
});

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

const fullName = (person) => {
    if (!person) return "—";

    return [
        person.first_name,
        person.middle_name,
        person.last_name,
        person.suffix,
    ]
        .filter(Boolean)
        .join(" ");
};

const clientContractPrice = (client) => {
    return Number(
        client?.total_contract_price ??
            client?.sales_sum_contract_price ??
            client?.contract_price_total ??
            0
    );
};

const clientSalesCount = (client) => {
    return Number(
        client?.sales_count ??
            client?.total_sales ??
            0
    );
};

const fetchClients = async () => {
    loading.value = true;

    try {
        const response = await clientService.getClients(filters);

        clients.value = response.data.data ?? [];

        summary.total_clients =
            response.data.summary?.total_clients ?? 0;

        summary.active_clients =
            response.data.summary?.active_clients ?? 0;

        summary.clients_with_sales =
            response.data.summary?.clients_with_sales ?? 0;

        summary.total_contract_price =
            response.data.summary?.total_contract_price ?? 0;

        pagination.current_page = response.data.current_page ?? 1;
        pagination.last_page = response.data.last_page ?? 1;
        pagination.total = response.data.total ?? 0;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load clients.");
    } finally {
        loading.value = false;
    }
};

const searchClients = async () => {
    filters.page = 1;
    await fetchClients();
};

const previousPage = async () => {
    if (pagination.current_page <= 1) return;

    filters.page = pagination.current_page - 1;
    await fetchClients();
};

const nextPage = async () => {
    if (pagination.current_page >= pagination.last_page) return;

    filters.page = pagination.current_page + 1;
    await fetchClients();
};

const goToPage = async (page) => {
    if (page === pagination.current_page) return;

    filters.page = page;
    await fetchClients();
};

const viewClient = (client) => {
    if (!client?.id) {
        toast.error("Client ID not found.");
        return;
    }

    router.push(`/client-management/clients/${client.id}`);
};

const createClient = () => {
    router.push("/client-management/clients/create");
};

onMounted(fetchClients);
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <PageHeader
                    title="Client Management"
                    description="Manage buyers, client profiles, sales, collections, and documents."
                />

                <button
                    type="button"
                    @click="createClient"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                >
                    Add Client
                </button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Total Clients
                    </p>

                    <h2 class="mt-2 text-3xl font-bold text-slate-900">
                        {{ summary.total_clients }}
                    </h2>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Active Clients
                    </p>

                    <h2 class="mt-2 text-3xl font-bold text-emerald-700">
                        {{ summary.active_clients }}
                    </h2>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Clients With Sales
                    </p>

                    <h2 class="mt-2 text-3xl font-bold text-blue-700">
                        {{ summary.clients_with_sales }}
                    </h2>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Total Contract Price
                    </p>

                    <h2 class="mt-2 text-xl font-bold text-purple-700">
                        {{ money(summary.total_contract_price) }}
                    </h2>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase text-slate-500">
                            Search
                        </label>

                        <input
                            v-model="filters.search"
                            @keyup.enter="searchClients"
                            type="text"
                            placeholder="Search name, email, contact..."
                            class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase text-slate-500">
                            Status
                        </label>

                        <select
                            v-model="filters.status"
                            @change="searchClients"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex items-end justify-end gap-2">
                        <select
                            v-model="filters.per_page"
                            @change="searchClients"
                            class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option :value="10">10 rows</option>
                            <option :value="25">25 rows</option>
                            <option :value="50">50 rows</option>
                        </select>

                        <button
                            type="button"
                            @click="searchClients"
                            class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
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
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                                    Client
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                                    Contact
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500">
                                    Address
                                </th>

                                <th class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500">
                                    Sales
                                </th>

                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase text-slate-500">
                                    Contract Price
                                </th>

                                <th class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500">
                                    Status
                                </th>

                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase text-slate-500">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="client in clients"
                                :key="client.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ fullName(client) }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ client.client_code || "—" }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm text-slate-900">
                                        {{ client.contact_number || "—" }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ client.email || "—" }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-700">
                                    {{ client.address || "—" }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-slate-900">
                                    {{ clientSalesCount(client) }}
                                </td>

                                <td class="px-5 py-4 text-right font-semibold text-emerald-700">
                                    {{ money(clientContractPrice(client)) }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                        :class="
                                            (client.status || 'active') === 'active'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-100 text-slate-700'
                                        "
                                    >
                                        {{ client.status || "active" }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <button
                                        type="button"
                                        @click="viewClient(client)"
                                        class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="clients.length === 0">
                                <td
                                    colspan="7"
                                    class="px-10 py-20 text-center text-slate-500"
                                >
                                    No clients found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                v-if="!loading && pagination.total > 0"
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                :total="pagination.total"
                @previous="previousPage"
                @next="nextPage"
                @go-to-page="goToPage"
            />
        </div>
    </AppLayout>
</template>