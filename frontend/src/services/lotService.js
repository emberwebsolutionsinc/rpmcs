import api from "./api";

export default {
    getLots(params = {}) {
        return api.get("/property-management/lots", {
            params,
        });
    },

    createLot(data) {
        return api.post("/property-management/lots", data);
    },

    updateLot(id, data) {
        return api.put(`/property-management/lots/${id}`, data);
    },

    deleteLot(id) {
        return api.delete(`/property-management/lots/${id}`);
    },
};