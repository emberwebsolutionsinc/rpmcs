import api from "./api";

export default {
    getSummary() {
        return api.get("/dashboard-summary");
    },
};