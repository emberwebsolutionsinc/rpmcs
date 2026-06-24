import api from "./api";

export default {
    getPayments(params = {}) {
        return api.get("/agent-commission-payments", {
            params,
        });
    },

    recordPayment(payload) {
        return api.post("/agent-commission-payments", payload);
    },

    deletePayment(id) {
        return api.delete(`/agent-commission-payments/${id}`);
    },
};