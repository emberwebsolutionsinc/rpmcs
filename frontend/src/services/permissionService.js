import api from "./api";

export default {
    getPermissions(params = {}) {
        return api.get(
            "/administration/permissions",
            {
                params,
            }
        );
    },
};