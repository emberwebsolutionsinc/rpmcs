import api from "./api";

export default {

    getProjects(params = {}) {
        return api.get(
            "/property-management/property-projects",
            { params }
        );
    },

    getProject(id) {
        return api.get(
            `/property-management/property-projects/${id}`
        );
    },

    createProject(data) {
        return api.post(
            "/property-management/property-projects",
            data
        );
    },

    updateProject(id, data) {
        return api.put(
            `/property-management/property-projects/${id}`,
            data
        );
    },

    deleteProject(id) {
        return api.delete(
            `/property-management/property-projects/${id}`
        );
    },
};