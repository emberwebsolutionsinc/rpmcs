import api from "./api";

export default {
    getAgents(params = {}) {
        return api.get("/agent-management/agents", {
            params,
        });
    },
};