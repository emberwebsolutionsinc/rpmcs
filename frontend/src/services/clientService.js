import api from "./api";

export default {
    getClients(params = {}) {
        return api.get("/client-management/clients", {
            params,
        });
    },

    getClient(id) {
        return api.get(`/client-management/clients/${id}`);
    },

    createClient(payload) {
        return api.post("/client-management/clients", payload);
    },

    updateClient(id, payload) {
        return api.put(`/client-management/clients/${id}`, payload);
    },

    deleteClient(id) {
        return api.delete(`/client-management/clients/${id}`);
    },

    getClientDocuments(clientId) {
    return api.get(
        `/client-management/clients/${clientId}/documents`
    );
    },

    uploadClientDocument(
        clientId,
        formData,
        onUploadProgress = null
    ) {
        return api.post(
            `/client-management/clients/${clientId}/documents`,
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
                onUploadProgress,
            }
        );
    },

    previewClientDocument(clientId, documentId) {
        return api.get(
            `/client-management/clients/${clientId}/documents/${documentId}/preview`,
            {
                responseType: "blob",
            }
        );
    },

    downloadClientDocument(clientId, documentId) {
        return api.get(
            `/client-management/clients/${clientId}/documents/${documentId}/download`,
            {
                responseType: "blob",
            }
        );
    },

    deleteClientDocument(clientId, documentId) {
        return api.delete(
            `/client-management/clients/${clientId}/documents/${documentId}`
        );
    },
};