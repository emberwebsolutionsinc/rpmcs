import api from "./api";

export default {
    getSales(params = {}) {
        return api.get("/sales-management/sales", {
            params,
        });
    },

    getSummary() {
        return api.get("/sales-management/sales-summary");
    },

    getSale(id) {
        return api.get(`/sales-management/sales/${id}`);
    },

    createSale(data) {
        return api.post("/sales-management/sales", data);
    },

    updateSale(id, data) {
        return api.put(`/sales-management/sales/${id}`, data);
    },

    cancelSale(id) {
        return api.patch(`/sales-management/sales/${id}/cancel`);
    },

    deleteSale(id) {
        return api.delete(`/sales-management/sales/${id}`);
    },
};