import api from "../api.js";

const USER_BASE_URL =
    "/administration/users";

function normalizeParams(params = {}) {
    const normalizedParams = {};

    Object.entries(params).forEach(
        ([key, value]) => {
            if (
                value === null ||
                value === undefined ||
                value === ""
            ) {
                return;
            }

            normalizedParams[key] =
                typeof value === "string"
                    ? value.trim()
                    : value;
        }
    );

    return normalizedParams;
}

async function getUsers(params = {}) {
    const response = await api.get(
        USER_BASE_URL,
        {
            params:
                normalizeParams(params),
        }
    );

    return response.data;
}

async function getOptions() {
    const response = await api.get(
        `${USER_BASE_URL}/options`
    );

    return response.data;
}

async function getUser(userId) {
    if (!userId) {
        throw new Error(
            "User ID is required."
        );
    }

    const response = await api.get(
        `${USER_BASE_URL}/${userId}`
    );

    return response.data;
}

async function createUser(payload) {
    const response = await api.post(
        USER_BASE_URL,
        payload
    );

    return response.data;
}

async function updateUser(
    userId,
    payload
) {
    if (!userId) {
        throw new Error(
            "User ID is required."
        );
    }

    const response = await api.put(
        `${USER_BASE_URL}/${userId}`,
        payload
    );

    return response.data;
}

async function updateUserStatus(
    userId,
    isActive
) {
    if (!userId) {
        throw new Error(
            "User ID is required."
        );
    }

    const response = await api.patch(
        `${USER_BASE_URL}/${userId}/status`,
        {
            is_active:
                Boolean(isActive),
        }
    );

    return response.data;
}

async function resetUserPassword(
    userId,
    payload
) {
    if (!userId) {
        throw new Error(
            "User ID is required."
        );
    }

    const response = await api.patch(
        `${USER_BASE_URL}/${userId}/reset-password`,
        payload
    );

    return response.data;
}

const userService = {
    getUsers,
    getOptions,
    getUser,
    createUser,
    updateUser,
    updateUserStatus,
    resetUserPassword,
};

export default userService;