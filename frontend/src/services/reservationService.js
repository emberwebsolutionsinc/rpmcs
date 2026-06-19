import api from "./api";

export default {
    getReservations(params = {}) {
        return api.get("/sales-management/reservations", {
            params,
        });
    },

    getReservation(id) {
        return api.get(`/sales-management/reservations/${id}`);
    },

    createReservation(data) {
        return api.post("/sales-management/reservations", data);
    },

    updateReservation(id, data) {
        return api.put(`/sales-management/reservations/${id}`, data);
    },

    deleteReservation(id) {
        return api.delete(`/sales-management/reservations/${id}`);
    },

    cancelReservation(id) {
        return api.patch(`/sales-management/reservations/${id}/cancel`);
    },
};