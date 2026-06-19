import api from "./api";

export default {
    getBlocks(params = {}) {
        return api.get("/property-management/project-blocks", {
            params,
        });
    },

    createBlock(data) {
        return api.post("/property-management/project-blocks", data);
    },

    updateBlock(id, data) {
        return api.put(`/property-management/project-blocks/${id}`, data);
    },

    deleteBlock(id) {
        return api.delete(`/property-management/project-blocks/${id}`);
    },
};