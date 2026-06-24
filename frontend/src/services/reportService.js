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

    exportCollectionReportExcel(params = {}) {
        return api.get("/reports/collections/export-excel", {
            params,
            responseType: "blob",
        });
    },

    exportCollectionReportPdf(params = {}) {
        return api.get("/reports/collections/export-pdf", {
            params,
            responseType: "blob",
        });
    },

    getSalesReport(params = {}) {
        return api.get("/reports/sales", {
            params,
        });
    },

    exportSalesReportExcel(params = {}) {
        return api.get("/reports/sales/export-excel", {
            params,
            responseType: "blob",
        });
    },

    exportSalesReportPdf(params = {}) {
        return api.get("/reports/sales/export-pdf", {
            params,
            responseType: "blob",
        });
    },

    getAgingReport(params = {}) {
        return api.get("/reports/aging", {
            params,
        });
    },

    exportAgingReportExcel(params = {}) {
        return api.get("/reports/aging/export-excel", {
            params,
            responseType: "blob",
        });
    },

    exportAgingReportPdf(params = {}) {
        return api.get("/reports/aging/export-pdf", {
            params,
            responseType: "blob",
        });
    },

    getAgentCommissionReport(params = {}) {
    return api.get("/reports/agent-commissions", {
        params,
    });
    },

    exportAgentCommissionReportExcel(params = {}) {
        return api.get("/reports/agent-commissions/export-excel", {
            params,
            responseType: "blob",
        });
    },

    exportAgentCommissionReportPdf(params = {}) {
        return api.get("/reports/agent-commissions/export-pdf", {
            params,
            responseType: "blob",
        });
    },
};