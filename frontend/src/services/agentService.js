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

        updateAgent(id, payload) {
            return api.put(`/agent-management/agents/${id}`, payload);
        },

        deleteAgent(id) {
            return api.delete(`/agent-management/agents/${id}`);
        },

        getAgentDocuments(agentId) {
            return api.get(`/agent-management/agents/${agentId}/documents`);
        },

        uploadAgentDocument(agentId, formData, onUploadProgress = null) {
            return api.post(
                `/agent-management/agents/${agentId}/documents`,
                formData,
                {
                    onUploadProgress,
                }
            );
        },

        downloadAgentDocument(agentId, documentId) {
            return api.get(
                `/agent-management/agents/${agentId}/documents/${documentId}/download`,
                {
                    responseType: "blob",
                }
            );
        },

        deleteAgentDocument(agentId, documentId) {
            return api.delete(
                `/agent-management/agents/${agentId}/documents/${documentId}`
            );
        },

        previewAgentDocument(agentId, documentId) {
            return api.get(
                `/agent-management/agents/${agentId}/documents/${documentId}/preview`,
                {
                    responseType: "blob",
                }
            );
        },

    };