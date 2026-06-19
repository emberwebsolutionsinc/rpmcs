import api from "./api";

export default {
    getPhases(params = {}) {
        return api.get("/property-management/project-phases", {
            params,
        });
    },

    createPhase(data) {
        return api.post("/property-management/project-phases", data);
    },

    updatePhase(id, data) {
        return api.put(`/property-management/project-phases/${id}`, data);
    },

    deletePhase(id) {
        return api.delete(`/property-management/project-phases/${id}`);
    },
};