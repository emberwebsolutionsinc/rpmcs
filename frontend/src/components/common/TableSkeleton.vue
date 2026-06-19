<template>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-4 border-b border-slate-200">
            <div class="h-5 w-48 bg-slate-200 rounded animate-pulse"></div>
        </div>

        <div class="divide-y divide-slate-100">
            <div
                v-for="row in 6"
                :key="row"
                class="grid grid-cols-6 gap-4 p-5"
            >
                <div class="col-span-2 flex items-center gap-3">
                    <div class="h-12 w-12 rounded-xl bg-slate-200 animate-pulse"></div>

                    <div class="space-y-2">
                        <div class="h-4 w-40 bg-slate-200 rounded animate-pulse"></div>
                        <div class="h-3 w-24 bg-slate-200 rounded animate-pulse"></div>
                    </div>
                </div>

                <div class="h-4 w-32 bg-slate-200 rounded animate-pulse"></div>

                <div class="h-4 w-40 bg-slate-200 rounded animate-pulse"></div>

                <div class="h-6 w-24 bg-slate-200 rounded-full animate-pulse"></div>

                <div class="flex justify-end">
                    <div class="h-9 w-24 bg-slate-200 rounded-lg animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>
</template>