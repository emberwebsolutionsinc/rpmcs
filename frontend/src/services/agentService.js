import api from "./api";

export default {
    getAgents(params = {}) {
        return api.get("/agent-management/agents", {
            params,
        });
    },

    getAgent(id) {
        return api.get(`/agent-management/agents/${id}`);
    },

    createAgent(payload) {
    return api.post("/agent-management/agents", payload);
    },
};