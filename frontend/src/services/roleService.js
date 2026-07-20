import api from "./api";

export default {
    getRoles(params = {}) {
        return api.get(
            "/administration/roles",
            {
                params,
            }
        );
    },

    getRole(id) {
        return api.get(
            `/administration/roles/${id}`
        );
    },

    createRole(payload) {
        return api.post(
            "/administration/roles",
            payload
        );
    },

    updateRole(id, payload) {
        return api.put(
            `/administration/roles/${id}`,
            payload
        );
    },

    deleteRole(id) {
        return api.delete(
            `/administration/roles/${id}`
        );
    },
};