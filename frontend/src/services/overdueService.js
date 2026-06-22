import api from "./api";

export default {
    getSummary() {
        return api.get("/sales-management/overdue-accounts-summary");
    },

    getOverdueAccounts(params = {}) {
        return api.get("/sales-management/overdue-accounts", {
            params,
        });
    },


    getTopDelinquents() {
    return api.get(
        "/sales-management/top-delinquents"
    );
},
};