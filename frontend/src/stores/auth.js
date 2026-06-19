import { defineStore } from "pinia";
import api from "@/services/api";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: JSON.parse(localStorage.getItem("user")) || null,
        token: localStorage.getItem("token") || null,
        loading: false,
        errors: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(credentials) {
            this.loading = true;
            this.errors = null;

            try {
                const response = await api.post("/login", credentials);

                this.token = response.data.token;
                this.user = response.data.user;

                localStorage.setItem("token", this.token);
                localStorage.setItem("user", JSON.stringify(this.user));

                return response.data;
            } catch (error) {
                this.errors =
                    error.response?.data?.message ||
                    "Login failed. Please try again.";

                throw error;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await api.post("/logout");
            } catch (error) {
                console.error(error);
            } finally {
                this.token = null;
                this.user = null;

                localStorage.removeItem("token");
                localStorage.removeItem("user");
            }
        },
    },
});