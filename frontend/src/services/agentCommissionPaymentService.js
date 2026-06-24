import api from "./api";

export default {
    getPayments(params = {}) {
        return api.get("/sales-management/agent-commission-payments", {
            params,
        });
    },

    recordPayment(payload) {
        return api.post("/sales-management/agent-commission-payments", payload);
    },

    deletePayment(id, payload = {}) {
        return api.delete(`/sales-management/agent-commission-payments/${id}`, {
            data: payload,
        });
    },

    printVoucher(id) {
        return api.get(
            `/sales-management/agent-commission-payments/${id}/voucher`,
            {
                responseType: "blob",
            }
        );
    },
};