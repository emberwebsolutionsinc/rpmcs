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
        return api.get(
            "/reports/collections/export-pdf",
            {
                params,
                responseType: "blob",
            }
        );
    },
    getSalesReport(params = {}) {
        return api.get("/reports/sales", {
            params,
        });
    },
};