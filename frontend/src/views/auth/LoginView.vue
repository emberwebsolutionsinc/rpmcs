<script setup>
import AuthLayout from "@/layouts/AuthLayout.vue";
import { reactive } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { Building2, Lock, Mail } from "lucide-vue-next";

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
    email: "",
    password: "",
});


const submitLogin = async () => {
    try {
        await authStore.login(form);
        router.push("/dashboard");
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <AuthLayout>
        <div class="min-h-screen grid lg:grid-cols-2 bg-slate-100">
            <div class="hidden lg:flex flex-col justify-between bg-slate-950 text-white p-12">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-xl bg-blue-600 flex items-center justify-center">
                            <Building2 class="h-7 w-7" />
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold">
                                RPMCS
                            </h1>
                            <p class="text-slate-400 text-sm">
                                Realty Property Management and Collection System
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-4xl font-bold leading-tight">
                        Manage properties, clients, collections, and commissions in one system.
                    </h2>

                    <p class="text-slate-400 mt-6 max-w-lg">
                        Enterprise-grade platform for real estate inventory, payment monitoring,
                        receipt tracking, and agent commission management.
                    </p>
                </div>

                <p class="text-slate-500 text-sm">
                    © 2026 RPMCS. All rights reserved.
                </p>
            </div>

            <div class="flex items-center justify-center p-6">
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
                    <div class="text-center mb-8">
                        <div class="mx-auto h-14 w-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center mb-4">
                            <Building2 class="h-8 w-8" />
                        </div>

                        <h1 class="text-2xl font-bold">
                            Sign in to RPMCS
                        </h1>

                        <p class="text-slate-500 mt-1">
                            Enter your credentials to continue
                        </p>
                    </div>

                    <form @submit.prevent="submitLogin" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Email Address
                            </label>

                            <div class="relative">
                                <Mail class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" />

                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="admin@rpmcs.test"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Password
                            </label>

                            <div class="relative">
                                <Lock class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" />

                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="password"
                                    required
                                />
                            </div>
                        </div>

                        <p v-if="authStore.errors" class="text-sm text-red-600">
                            {{ authStore.errors }}
                        </p>

                        <button
                            type="submit"
                            :disabled="authStore.loading"
                            class="w-full rounded-lg bg-blue-600 py-2.5 text-white font-semibold hover:bg-blue-700 disabled:opacity-60"
                        >
                            {{ authStore.loading ? "Signing in..." : "Sign In" }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>