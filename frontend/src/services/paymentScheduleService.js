import api from "./api";

export default {
    getSchedules(params = {}) {
        return api.get("/sales-management/payment-schedules", {
            params,
        });
    },

    generateSchedule(data) {
        return api.post("/sales-management/payment-schedules/generate", data);
    },

    getSchedule(id) {
        return api.get(`/sales-management/payment-schedules/${id}`);
    },

    deleteSchedule(id) {
        return api.delete(`/sales-management/payment-schedules/${id}`);
    },
};