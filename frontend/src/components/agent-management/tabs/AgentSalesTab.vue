<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const props = defineProps({
    sales: {
        type: Array,
        default: () => [],
    },
});

const search = ref("");
const project = ref("");

const sortColumn = ref("sale_date");
const sortDirection = ref("desc");

const sortBy = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value =
            sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortColumn.value = column;
        sortDirection.value = "asc";
    }
};

const compare = (a, b) => {
    if (a === null || a === undefined) a = "";
    if (b === null || b === undefined) b = "";

    if (typeof a === "number" && typeof b === "number") {
        return a - b;
    }

    return String(a).localeCompare(String(b));
};

const sortIcon = (column) => {
    if (sortColumn.value !== column) return "↕";

    return sortDirection.value === "asc" ? "▲" : "▼";
};

const refresh = () => {
    window.location.reload();
};

const money = (value) =>
    Number(value || 0).toLocaleString("en-PH", {
        style: "currency",
        currency: "PHP",
    });

const date = (value) => {
    if (!value) return "—";

    return new Date(value).toLocaleDateString("en-PH");
};

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

const projectName = (sale) =>
    sale.project?.project_name ??
    sale.lot?.project?.project_name ??
    sale.lot?.block?.phase?.project?.project_name ??
    "—";

const phaseName = (sale) =>
    sale.phase?.phase_name ??
    sale.lot?.block?.phase?.phase_name ??
    "—";

const blockNumber = (sale) =>
    sale.block?.block_no ??
    sale.lot?.block?.block_no ??
    "—";

const lotNumber = (sale) => sale.lot?.lot_no ?? "—";

const commissionStatus = (sale) => {
    const earned = Number(sale.commission_earned || 0);
    const paid = Number(sale.commission_paid || 0);

    if (earned <= 0) return "No Commission";
    if (paid <= 0) return "Unpaid";
    if (paid >= earned) return "Fully Paid";

    return "Partially Paid";
};

const commissionBadge = (sale) => {
    switch (commissionStatus(sale)) {
        case "Fully Paid":
            return "bg-emerald-100 text-emerald-700";
        case "Partially Paid":
            return "bg-amber-100 text-amber-700";
        case "Unpaid":
            return "bg-red-100 text-red-700";
        default:
            return "bg-slate-100 text-slate-700";
    }
};

const projects = computed(() => [
    ...new Set(
        props.sales
            .map(projectName)
            .filter((x) => x !== "—")
    ),
]);

const filteredSales = computed(() => {
    let data = props.sales.filter((sale) => {
        const keyword = search.value.trim().toLowerCase();
        const buyer = fullName(sale.client).toLowerCase();
        const saleNo = (sale.sale_no || "").toLowerCase();
        const lot = lotNumber(sale).toLowerCase();

        const matchesSearch =
            !keyword ||
            buyer.includes(keyword) ||
            saleNo.includes(keyword) ||
            lot.includes(keyword);

        const matchesProject =
            !project.value || projectName(sale) === project.value;

        return matchesSearch && matchesProject;
    });

    data.sort((a, b) => {
        let valueA;
        let valueB;

        switch (sortColumn.value) {
            case "buyer":
                valueA = fullName(a.client);
                valueB = fullName(b.client);
                break;

            case "project":
                valueA = projectName(a);
                valueB = projectName(b);
                break;

            case "tcp":
                valueA = Number(a.contract_price || 0);
                valueB = Number(b.contract_price || 0);
                break;

            case "commission":
                valueA = Number(a.commission_earned || 0);
                valueB = Number(b.commission_earned || 0);
                break;

            default:
                valueA = a.sale_date || "";
                valueB = b.sale_date || "";
        }

        const result = compare(valueA, valueB);

        return sortDirection.value === "asc" ? result : -result;
    });

    return data;
});

const totalSales = computed(() => filteredSales.value.length);

const grossSales = computed(() =>
    filteredSales.value.reduce(
        (sum, sale) => sum + Number(sale.contract_price || 0),
        0
    )
);

const totalCommission = computed(() =>
    filteredSales.value.reduce(
        (sum, sale) => sum + Number(sale.commission_earned || 0),
        0
    )
);

const averageSale = computed(() => {
    if (!filteredSales.value.length) return 0;

    return grossSales.value / filteredSales.value.length;
});

const largestSale = computed(() => {
    if (!filteredSales.value.length) return null;

    return filteredSales.value.reduce((largest, current) =>
        Number(current.contract_price || 0) >
        Number(largest.contract_price || 0)
            ? current
            : largest
    );
});

const openSale = (sale) => {
    const saleId = sale.sale_id || sale.id;

    if (!saleId) return;

    router.push(`/agent-management/sales/${saleId}`);
};
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 lg:grid-cols-3">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase text-slate-500">
                        Search
                    </label>

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buyer, Sale No., Lot..."
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase text-slate-500">
                        Project
                    </label>

                    <select
                        v-model="project"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm"
                    >
                        <option value="">All Projects</option>

                        <option
                            v-for="item in projects"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>
                    </select>
                </div>

                <div class="flex items-end justify-end gap-2">
                    <button
                        @click="refresh"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Total Sales
                </p>
                <h2 class="mt-2 text-3xl font-bold text-slate-900">
                    {{ totalSales }}
                </h2>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Gross Sales
                </p>
                <h2 class="mt-2 text-xl font-bold text-emerald-700">
                    {{ money(grossSales) }}
                </h2>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Total Commission
                </p>
                <h2 class="mt-2 text-xl font-bold text-blue-700">
                    {{ money(totalCommission) }}
                </h2>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Average Sale
                </p>
                <h2 class="mt-2 text-xl font-bold text-purple-700">
                    {{ money(averageSale) }}
                </h2>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Largest Sale
                </p>
                <h2 class="mt-2 text-lg font-bold text-red-700">
                    {{
                        largestSale
                            ? money(largestSale.contract_price)
                            : "—"
                    }}
                </h2>

                <p
                    v-if="largestSale"
                    class="mt-2 text-xs text-slate-500"
                >
                    {{ largestSale.sale_no }}
                </p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                @click="sortBy('sale_date')"
                                class="cursor-pointer px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500 hover:bg-slate-100"
                            >
                                Sale {{ sortIcon("sale_date") }}
                            </th>

                            <th
                                @click="sortBy('buyer')"
                                class="cursor-pointer px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500 hover:bg-slate-100"
                            >
                                Buyer {{ sortIcon("buyer") }}
                            </th>

                            <th
                                @click="sortBy('project')"
                                class="cursor-pointer px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500 hover:bg-slate-100"
                            >
                                Property {{ sortIcon("project") }}
                            </th>

                            <th
                                @click="sortBy('tcp')"
                                class="cursor-pointer px-5 py-4 text-right text-xs font-semibold uppercase text-slate-500 hover:bg-slate-100"
                            >
                                TCP {{ sortIcon("tcp") }}
                            </th>

                            <th
                                @click="sortBy('commission')"
                                class="cursor-pointer px-5 py-4 text-right text-xs font-semibold uppercase text-slate-500 hover:bg-slate-100"
                            >
                                Commission {{ sortIcon("commission") }}
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500">
                                Commission Status
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="sale in filteredSales"
                            :key="sale.id"
                            @click="openSale(sale)"
                            class="cursor-pointer transition duration-200 hover:bg-emerald-50"
                        >
                            <td class="px-5 py-5 align-top">
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-900">
                                        {{ sale.sale_no || "—" }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ date(sale.sale_date) }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-5 align-top">
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-900">
                                        {{ fullName(sale.client) }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ sale.client?.contact_number || "No Contact" }}
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        {{ sale.client?.email || "No Email" }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-5 align-top">
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-900">
                                        {{ projectName(sale) }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        Phase {{ phaseName(sale) }}
                                        • Block {{ blockNumber(sale) }}
                                        • Lot {{ lotNumber(sale) }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-5 py-5 text-right align-top">
                                <p class="font-bold text-slate-900">
                                    {{ money(sale.contract_price) }}
                                </p>
                            </td>

                            <td class="px-5 py-5 text-right align-top">
                                <p class="font-bold text-emerald-700">
                                    {{ money(sale.commission_earned) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Paid: {{ money(sale.commission_paid) }}
                                </p>
                            </td>

                            <td class="px-5 py-5 text-center align-top">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="commissionBadge(sale)"
                                >
                                    {{ commissionStatus(sale) }}
                                </span>
                            </td>
                        </tr>

                        <tr v-if="filteredSales.length === 0">
                            <td
                                colspan="6"
                                class="px-10 py-20"
                            >
                                <div class="mx-auto max-w-lg text-center">
                                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-5xl">
                                        📄
                                    </div>

                                    <h3 class="mt-6 text-xl font-semibold text-slate-900">
                                        No Sales Found
                                    </h3>

                                    <p class="mt-3 text-sm leading-6 text-slate-500">
                                        There are currently no sales assigned to this
                                        agent that match the selected filters.
                                    </p>

                                    <button
                                        @click.stop="
                                            search = '';
                                            project = '';
                                        "
                                        class="mt-6 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                    >
                                        Clear Filters
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm md:flex-row md:items-center md:justify-between">
            <div class="text-sm text-slate-500">
                Showing
                <strong class="text-slate-900">
                    {{ filteredSales.length }}
                </strong>
                sale(s)
            </div>
        </div>
    </div>
</template>