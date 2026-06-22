import api from "./api";

export default {
    getCollectionReport(params = {}) {
        return api.get("/reports/collections", {
            params,
        });
    },
};