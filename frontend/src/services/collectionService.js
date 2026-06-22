import api from "./api";

export default {
    getCollections(params = {}) {
        return api.get("/sales-management/collections", {
            params,
        });
    },

    getSummary() {
        return api.get("/sales-management/collections-summary");
    },

    getCollection(id) {
        return api.get(`/sales-management/collections/${id}`);
    },

    createCollection(data) {
        return api.post("/sales-management/collections", data);
    },

voidCollection(id, data = {}) {
    return api.patch(`/sales-management/collections/${id}/void`, data);
},

    printOR(id) {
        return api.get(`/sales-management/collections/${id}/print-or`, {
            responseType: "blob",
        });
    },
};