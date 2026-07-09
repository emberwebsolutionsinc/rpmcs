import api from "./api";

export default {
    getClients(params = {}) {
        return api.get("/client-management/clients", {
            params,
        });
    },

    getClient(id) {
        return api.get(`/client-management/clients/${id}`);
    },

    createClient(payload) {
        return api.post("/client-management/clients", payload);
    },

    updateClient(id, payload) {
        return api.put(`/client-management/clients/${id}`, payload);
    },

    deleteClient(id) {
        return api.delete(`/client-management/clients/${id}`);
    },
};