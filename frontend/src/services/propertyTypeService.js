import api from "./api";

export default {
    getPropertyTypes(params = {}) {
        return api.get("/property-types", {
            params,
        });
    },
};