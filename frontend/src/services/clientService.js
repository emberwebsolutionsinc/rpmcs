import api from "./api";

export default {
    getClients(params = {}) {
        return api.get("/client-management/clients", {
            params,
        });
    },
};