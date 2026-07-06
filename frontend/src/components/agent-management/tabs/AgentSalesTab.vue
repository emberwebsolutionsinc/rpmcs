<script setup>
import {
    computed,
    ref,
    onMounted,
    onBeforeUnmount,
} from "vue";

const props = defineProps({
    sales: {
        type: Array,
        default: () => [],
    },
});

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const search = ref("");
const project = ref("");
const status = ref("");

/*
|--------------------------------------------------------------------------
| Table Sorting
|--------------------------------------------------------------------------
*/

const sortColumn = ref("sale_date");
const sortDirection = ref("desc");

const sortBy = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value =
            sortDirection.value === "asc"
                ? "desc"
                : "asc";
    } else {
        sortColumn.value = column;
        sortDirection.value = "asc";
    }
};

const compare = (a, b) => {
    if (a === null || a === undefined) a = "";
    if (b === null || b ===undefined) b = "";

    if (
        typeof a === "number" &&
        typeof b === "number"
    ) {
        return a - b;
    }

    return String(a).localeCompare(String(b));
};

const sortIcon = (column) => {
    if (sortColumn.value !== column) {
        return "↕";
    }

    return sortDirection.value === "asc"
        ? "▲"
        : "▼";
};

/*
|--------------------------------------------------------------------------
| Expanded Rows
|--------------------------------------------------------------------------
*/

const expandedRows = ref([]);

const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value =
            expandedRows.value.filter(
                (x) => x !== id
            );
    } else {
        expandedRows.value.push(id);
    }
};

const isExpanded = (id) =>
    expandedRows.value.includes(id);

/*
|--------------------------------------------------------------------------
| Action Menu
|--------------------------------------------------------------------------
*/

const activeMenu = ref(null);

const toggleMenu = (saleId) => {
    activeMenu.value =
        activeMenu.value === saleId
            ? null
            : saleId;
};

const closeMenu = () => {
    activeMenu.value = null;
};

const handleOutsideClick = () => {
    activeMenu.value = null;
};

onMounted(() => {
    document.addEventListener(
        "click",
        handleOutsideClick
    );
});

onBeforeUnmount(() => {
    document.removeEventListener(
        "click",
        handleOutsideClick
    );
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const refresh = () => {
    window.location.reload();
};

const money = (value) =>
    Number(value || 0).toLocaleString(
        "en-PH",
        {
            style: "currency",
            currency: "PHP",
        }
    );

const date = (value) => {
    if (!value) return "—";

    return new Date(
        value
    ).toLocaleDateString("en-PH");
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
    "—";

const phaseName = (sale) =>
    sale.phase?.phase_name ??
    sale.lot?.block?.phase?.phase_name ??
    "—";

const blockNumber = (sale) =>
    sale.block?.block_no ??
    sale.lot?.block?.block_no ??
    "—";

const lotNumber = (sale) =>
    sale.lot?.lot_no ??
    "—";

/*
|--------------------------------------------------------------------------
| Collection Progress
|--------------------------------------------------------------------------
*/

const collectionPercentage = (
    sale
) => {

    const tcp = Number(
        sale.contract_price || 0
    );

    const collected = Number(
        sale.total_collection || 0
    );

    if (!tcp) return 0;

    return Math.min(
        Math.round(
            (collected / tcp) * 100
        ),
        100
    );

};

const collectionClass = (
    sale
) => {

    const percentage =
        collectionPercentage(sale);

    if (percentage >= 100)
        return "bg-emerald-500";

    if (percentage >= 70)
        return "bg-blue-500";

    if (percentage >= 40)
        return "bg-amber-500";

    return "bg-red-500";

};

/*
|--------------------------------------------------------------------------
| Commission
|--------------------------------------------------------------------------
*/

const commissionStatus = (
    sale
) => {

    const earned = Number(
        sale.commission_earned || 0
    );

    const paid = Number(
        sale.commission_paid || 0
    );

    if (earned <= 0)
        return "No Commission";

    if (paid <= 0)
        return "Unpaid";

    if (paid >= earned)
        return "Fully Paid";

    return "Partially Paid";

};

const commissionBadge = (
    sale
) => {

    switch (
        commissionStatus(sale)
    ) {

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

const reservationBadge = (
    sale
) => {

    switch (
        (
            sale.status || ""
        ).toLowerCase()
    ) {

        case "sold":
            return "bg-emerald-100 text-emerald-700";

        case "reserved":
            return "bg-blue-100 text-blue-700";

        case "cancelled":
            return "bg-red-100 text-red-700";

        default:
            return "bg-slate-100 text-slate-700";

    }

};

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const projects = computed(() => {

    return [
        ...new Set(
            props.sales
                .map(projectName)
                .filter(
                    (x) => x !== "—"
                )
        ),
    ];

});

const filteredSales = computed(() => {

    let data = props.sales.filter(
        (sale) => {

            const keyword =
                search.value
                    .trim()
                    .toLowerCase();

            const buyer =
                fullName(
                    sale.client
                ).toLowerCase();

            const reservation =
                (
                    sale.sale_no || ""
                ).toLowerCase();

            const lot =
                lotNumber(
                    sale
                ).toLowerCase();

            const matchesSearch =
                !keyword ||
                buyer.includes(
                    keyword
                ) ||
                reservation.includes(
                    keyword
                ) ||
                lot.includes(
                    keyword
                );

            const matchesProject =
                !project.value ||
                projectName(sale) ===
                    project.value;

            const matchesStatus =
                !status.value ||
                (
                    sale.status || ""
                ).toLowerCase() ===
                    status.value.toLowerCase();

            return (
                matchesSearch &&
                matchesProject &&
                matchesStatus
            );

        }
    );

    data.sort((a, b) => {

        let valueA;
        let valueB;

        switch (
            sortColumn.value
        ) {

            case "buyer":
                valueA =
                    fullName(
                        a.client
                    );
                valueB =
                    fullName(
                        b.client
                    );
                break;

            case "project":
                valueA =
                    projectName(a);
                valueB =
                    projectName(b);
                break;

            case "tcp":
                valueA = Number(
                    a.contract_price ||
                        0
                );
                valueB = Number(
                    b.contract_price ||
                        0
                );
                break;

            case "commission":
                valueA = Number(
                    a.commission_earned ||
                        0
                );
                valueB = Number(
                    b.commission_earned ||
                        0
                );
                break;

            case "status":
                valueA =
                    a.status || "";
                valueB =
                    b.status || "";
                break;

            default:
                valueA =
                    a.sale_date ||
                    "";
                valueB =
                    b.sale_date ||
                    "";

        }

        const result =
            compare(
                valueA,
                valueB
            );

        return sortDirection.value ===
            "asc"
            ? result
            : -result;

    });

    return data;

});

/*
|--------------------------------------------------------------------------
| Dashboard KPIs
|--------------------------------------------------------------------------
*/

const totalSales = computed(
    () => filteredSales.value.length
);

const grossSales = computed(() =>
    filteredSales.value.reduce(
        (sum, sale) =>
            sum +
            Number(
                sale.contract_price ||
                    0
            ),
        0
    )
);

const totalCommission =
    computed(() =>
        filteredSales.value.reduce(
            (sum, sale) =>
                sum +
                Number(
                    sale.commission_earned ||
                        0
                ),
            0
        )
    );

const averageSale =
    computed(() => {

        if (
            !filteredSales.value.length
        )
            return 0;

        return (
            grossSales.value /
            filteredSales.value
                .length
        );

    });

const largestSale =
    computed(() => {

        if (
            !filteredSales.value.length
        )
            return null;

        return filteredSales.value.reduce(
            (
                largest,
                current
            ) =>
                Number(
                    current.contract_price ||
                        0
                ) >
                Number(
                    largest.contract_price ||
                        0
                )
                    ? current
                    : largest
        );

    });

/*
|--------------------------------------------------------------------------
| Actions (Temporary)
|--------------------------------------------------------------------------
*/

const viewSale = (sale) =>
    console.log(sale);

const openBuyer = (sale) =>
    console.log(sale.client);

const openProperty = (sale) =>
    console.log(sale.lot);

const printReservation = (
    sale
) => console.log(sale);

const generateCommission = (
    sale
) => console.log(sale);
</script>
<template>
    <div class="space-y-6">

        <!-- ========================================== -->
        <!-- Toolbar -->
        <!-- ========================================== -->

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="grid gap-4 lg:grid-cols-4">

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase text-slate-500">
                        Search
                    </label>

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buyer, Reservation No., Lot..."
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
                        <option value="">
                            All Projects
                        </option>

                        <option
                            v-for="item in projects"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>

                    </select>

                </div>

                <div>

                    <label class="mb-2 block text-xs font-semibold uppercase text-slate-500">
                        Reservation Status
                    </label>

                    <select
                        v-model="status"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm"
                    >

                        <option value="">
                            All Status
                        </option>

                        <option value="reserved">
                            Reserved
                        </option>

                        <option value="sold">
                            Sold
                        </option>

                        <option value="cancelled">
                            Cancelled
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

        <!-- ========================================== -->
        <!-- KPI -->
        <!-- ========================================== -->

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >

                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Total Sales
                </p>

                <h2 class="mt-2 text-3xl font-bold text-slate-900">
                    {{ totalSales }}
                </h2>

            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >

                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Gross Sales
                </p>

                <h2 class="mt-2 text-xl font-bold text-emerald-700">
                    {{ money(grossSales) }}
                </h2>

            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >

                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Total Commission
                </p>

                <h2 class="mt-2 text-xl font-bold text-blue-700">
                    {{ money(totalCommission) }}
                </h2>

            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >

                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Average Sale
                </p>

                <h2 class="mt-2 text-xl font-bold text-purple-700">
                    {{ money(averageSale) }}
                </h2>

            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
            >

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

        <!-- ========================================== -->
        <!-- Table -->
        <!-- ========================================== -->

        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th
                                @click="sortBy('sale_date')"
                                class="cursor-pointer px-5 py-4 text-left text-xs font-semibold uppercase text-slate-500 hover:bg-slate-100"
                            >
                                Reservation {{ sortIcon("sale_date") }}
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
                                class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500"
                            >
                                Collection
                            </th>

                            <th
                                @click="sortBy('commission')"
                                class="cursor-pointer px-5 py-4 text-right text-xs font-semibold uppercase text-slate-500 hover:bg-slate-100"
                            >
                                Commission {{ sortIcon("commission") }}
                            </th>

                            <th
                                class="px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500"
                            >
                                Commission Status
                            </th>

                            <th
                                @click="sortBy('status')"
                                class="cursor-pointer px-5 py-4 text-center text-xs font-semibold uppercase text-slate-500 hover:bg-slate-100"
                            >
                                Reservation Status {{ sortIcon("status") }}
                            </th>

                            <th
                                class="px-5 py-4 text-right text-xs font-semibold uppercase text-slate-500"
                            >
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <template
    v-for="sale in filteredSales"
    :key="sale.id"
>
    <!-- ===================================================== -->
    <!-- Main Sales Row -->
    <!-- ===================================================== -->

    <tr class="transition duration-200 hover:bg-slate-50">

        <!-- Reservation -->

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

        <!-- Buyer -->

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

        <!-- Property -->

        <td class="px-5 py-5 align-top">

            <div class="space-y-1">

                <p class="font-semibold text-slate-900">
                    {{ projectName(sale) }}
                </p>

                <p class="text-xs text-slate-500">

                    Phase {{ phaseName(sale) }}

                    •

                    Block {{ blockNumber(sale) }}

                    •

                    Lot {{ lotNumber(sale) }}

                </p>

            </div>

        </td>

        <!-- TCP -->

        <td class="px-5 py-5 text-right align-top">

            <p class="font-bold text-slate-900">
                {{ money(sale.contract_price) }}
            </p>

        </td>

        <!-- Collection Progress -->

        <td class="px-5 py-5 align-top">

            <div class="w-44">

                <div
                    class="mb-2 flex justify-between text-xs text-slate-600"
                >

                    <span>

                        {{ collectionPercentage(sale) }}%

                    </span>

                    <span>

                        {{ money(sale.total_collection) }}

                    </span>

                </div>

                <div
                    class="h-2 overflow-hidden rounded-full bg-slate-200"
                >

                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :class="collectionClass(sale)"
                        :style="{
                            width:
                                collectionPercentage(sale) +
                                '%',
                        }"
                    />

                </div>

            </div>

        </td>

        <!-- Commission -->

        <td class="px-5 py-5 text-right align-top">

            <div>

                <p
                    class="font-bold text-emerald-700"
                >
                    {{ money(sale.commission_earned) }}
                </p>

                <p
                    class="mt-1 text-xs text-slate-500"
                >
                    Paid:
                    {{
                        money(sale.commission_paid)
                    }}
                </p>

            </div>

        </td>

        <!-- Commission Status -->

        <td class="px-5 py-5 text-center align-top">

            <span
                class="rounded-full px-3 py-1 text-xs font-semibold"
                :class="commissionBadge(sale)"
            >
                {{ commissionStatus(sale) }}
            </span>

        </td>

        <!-- Reservation Status -->

        <td class="px-5 py-5 text-center align-top">

            <span
                class="rounded-full px-3 py-1 text-xs font-semibold"
                :class="reservationBadge(sale)"
            >
                {{ sale.status || "—" }}
            </span>

        </td>

        <!-- Actions -->

        <td class="px-5 py-5 text-right align-top">

            <div
                class="relative inline-block"
            >

                <button
                    @click.stop="toggleMenu(sale.id)"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold hover:bg-slate-50"
                >
                    Actions ▼
                </button>

                <div
                    v-if="activeMenu === sale.id"
                    @click.stop
                    class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                >

                    <button
                        @click="toggleRow(sale.id)"
                        class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-100"
                    >
                        {{
                            isExpanded(sale.id)
                                ? "Hide Details"
                                : "View Details"
                        }}
                    </button>

                    <button
                        @click="viewSale(sale)"
                        class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-100"
                    >
                        👁 View Sale
                    </button>

                    <button
                        @click="openBuyer(sale)"
                        class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-100"
                    >
                        👤 View Buyer
                    </button>

                    <button
                        @click="openProperty(sale)"
                        class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-100"
                    >
                        🏠 View Property
                    </button>

                    <button
                        @click="printReservation(sale)"
                        class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-100"
                    >
                        🖨 Print Reservation
                    </button>

                    <button
                        @click="generateCommission(sale)"
                        class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-100"
                    >
                        💰 Generate Commission
                    </button>

                </div>

            </div>

        </td>

    </tr>
        <!-- ===================================================== -->
    <!-- Expanded CRM Details -->
    <!-- ===================================================== -->

    <tr
        v-if="isExpanded(sale.id)"
        class="bg-slate-50"
    >
        <td
            colspan="9"
            class="px-6 py-6"
        >

            <div class="grid gap-6 xl:grid-cols-2 2xl:grid-cols-4">

                <!-- ========================================== -->
                <!-- Buyer Information -->
                <!-- ========================================== -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="font-semibold text-slate-900">
                        Buyer Information
                    </h3>

                    <div class="mt-5 space-y-3 text-sm">

                        <div>
                            <p class="text-slate-500">
                                Full Name
                            </p>

                            <p class="font-semibold text-slate-900">
                                {{ fullName(sale.client) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Contact Number
                            </p>

                            <p class="font-semibold">
                                {{ sale.client?.contact_number || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Email
                            </p>

                            <p class="font-semibold">
                                {{ sale.client?.email || "—" }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Address
                            </p>

                            <p class="font-semibold">
                                {{ sale.client?.address || "—" }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- ========================================== -->
                <!-- Property -->
                <!-- ========================================== -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="font-semibold text-slate-900">
                        Property Information
                    </h3>

                    <div class="mt-5 space-y-3 text-sm">

                        <div>
                            <p class="text-slate-500">
                                Project
                            </p>

                            <p class="font-semibold">
                                {{ projectName(sale) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Phase
                            </p>

                            <p class="font-semibold">
                                {{ phaseName(sale) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Block
                            </p>

                            <p class="font-semibold">
                                {{ blockNumber(sale) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Lot
                            </p>

                            <p class="font-semibold">
                                {{ lotNumber(sale) }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- ========================================== -->
                <!-- Collection Summary -->
                <!-- ========================================== -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="font-semibold text-slate-900">
                        Collection Summary
                    </h3>

                    <div class="mt-5 space-y-3 text-sm">

                        <div class="flex justify-between">

                            <span>Total Contract Price</span>

                            <strong>
                                {{ money(sale.contract_price) }}
                            </strong>

                        </div>

                        <div class="flex justify-between">

                            <span>Total Collected</span>

                            <strong class="text-emerald-700">
                                {{ money(sale.total_collection) }}
                            </strong>

                        </div>

                        <div class="flex justify-between">

                            <span>Remaining Balance</span>

                            <strong class="text-red-700">
                                {{ money(sale.balance) }}
                            </strong>

                        </div>

                        <div>

                            <div class="mb-2 flex justify-between text-xs">

                                <span>
                                    Collection Progress
                                </span>

                                <span>
                                    {{ collectionPercentage(sale) }}%
                                </span>

                            </div>

                            <div class="h-3 overflow-hidden rounded-full bg-slate-200">

                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="collectionClass(sale)"
                                    :style="{
                                        width:
                                            collectionPercentage(sale) +
                                            '%',
                                    }"
                                />

                            </div>

                        </div>

                    </div>

                </div>

                <!-- ========================================== -->
                <!-- Commission Summary -->
                <!-- ========================================== -->

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="font-semibold text-slate-900">
                        Commission Summary
                    </h3>

                    <div class="mt-5 space-y-3 text-sm">

                        <div class="flex justify-between">

                            <span>Rate</span>

                            <strong>
                                {{ sale.commission_rate || 0 }}%
                            </strong>

                        </div>

                        <div class="flex justify-between">

                            <span>Earned</span>

                            <strong class="text-emerald-700">
                                {{ money(sale.commission_earned) }}
                            </strong>

                        </div>

                        <div class="flex justify-between">

                            <span>Paid</span>

                            <strong class="text-blue-700">
                                {{ money(sale.commission_paid) }}
                            </strong>

                        </div>

                        <div class="flex justify-between">

                            <span>Balance</span>

                            <strong class="text-red-700">
                                {{ money(sale.commission_balance) }}
                            </strong>

                        </div>

                        <div class="pt-3">

                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="commissionBadge(sale)"
                            >
                                {{ commissionStatus(sale) }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </td>

    </tr>

</template>

<!-- ===================================================== -->
<!-- Empty State -->
<!-- ===================================================== -->

<tr v-if="filteredSales.length === 0">

    <td
        colspan="9"
        class="px-10 py-20"
    >

        <div class="mx-auto max-w-lg text-center">

            <div
                class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-5xl"
            >
                📄
            </div>

            <h3
                class="mt-6 text-xl font-semibold text-slate-900"
            >
                No Sales Found
            </h3>

            <p
                class="mt-3 text-sm leading-6 text-slate-500"
            >
                There are currently no sales assigned to this
                agent that match the selected filters.
            </p>

            <button
                @click="
                    search = '';
                    project = '';
                    status = '';
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

        <!-- ========================================== -->
        <!-- Footer -->
        <!-- ========================================== -->

        <div
            class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm md:flex-row md:items-center md:justify-between"
        >

            <div class="text-sm text-slate-500">

                Showing

                <strong class="text-slate-900">

                    {{ filteredSales.length }}

                </strong>

                sale(s)

            </div>

            <div
                class="flex flex-wrap gap-2"
            >

                <button
                    disabled
                    class="rounded-lg border border-slate-300 bg-slate-100 px-4 py-2 text-sm text-slate-400"
                >
                    Previous
                </button>

                <button
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    1
                </button>

                <button
                    disabled
                    class="rounded-lg border border-slate-300 bg-slate-100 px-4 py-2 text-sm text-slate-400"
                >
                    Next
                </button>

            </div>

        </div>

    </div>

</template>