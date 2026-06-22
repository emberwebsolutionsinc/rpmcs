import api from "./api";

export default {
    getDashboard() {
        return api.get("/reports/dashboard");
    },

    getCollectionReport(params = {}) {
        return api.get("/reports/collections", {
            params,
        });
    },
};