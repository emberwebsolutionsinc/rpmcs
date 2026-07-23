import axios from "axios";
import router from "@/router";

const api = axios.create({
    baseURL:
        import.meta.env.VITE_API_URL ||
        "http://127.0.0.1:8000/api/v1",

    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },

    /*
    |--------------------------------------------------------------------------
    | Request timeout
    |--------------------------------------------------------------------------
    |
    | Prevent requests from waiting indefinitely when the API server is
    | unavailable or experiencing a connection problem.
    |
    */
    timeout: 30000,
});

/*
|--------------------------------------------------------------------------
| Redirect-state flags
|--------------------------------------------------------------------------
|
| These flags prevent multiple simultaneous failed requests from triggering
| repeated navigation attempts.
|
*/
let isRedirectingToLogin = false;
let isRedirectingToForbidden = false;

/*
|--------------------------------------------------------------------------
| Request interceptor
|--------------------------------------------------------------------------
|
| Attach the stored Sanctum bearer token to every API request.
|
| The token may exist in:
|
| - localStorage when "Remember Me" is enabled
| - sessionStorage for temporary login sessions
|
*/
api.interceptors.request.use(
    (config) => {
        const token =
            localStorage.getItem("token") ||
            sessionStorage.getItem("token");

        /*
         * Axios may initialize headers differently depending on its version.
         * Ensure that the headers object exists before assigning values.
         */
        config.headers =
            config.headers || {};

        if (token) {
            config.headers.Authorization =
                `Bearer ${token}`;
        } else {
            delete config.headers.Authorization;
        }

        /*
         * Ensure Laravel returns JSON errors instead of HTML error pages.
         */
        config.headers.Accept =
            "application/json";

        return config;
    },

    (error) => {
        console.error(
            "API request configuration failed:",
            error
        );

        return Promise.reject(error);
    }
);

/*
|--------------------------------------------------------------------------
| Clear authentication data
|--------------------------------------------------------------------------
|
| Remove all locally stored authentication information.
|
| This should only be called when:
|
| - The token is invalid
| - The token has expired
| - The token was revoked
| - The account was deactivated
| - The authenticated account no longer exists
|
*/
const clearAuthentication = () => {
    const authenticationKeys = [
        "token",
        "user",
        "roles",
        "permissions",
    ];

    authenticationKeys.forEach((key) => {
        localStorage.removeItem(key);
        sessionStorage.removeItem(key);
    });

    /*
     * Remove any Authorization header previously added to the Axios defaults.
     */
    delete api.defaults.headers
        .common.Authorization;
};

/*
|--------------------------------------------------------------------------
| Redirect user to login
|--------------------------------------------------------------------------
|
| Clears the current authentication state and redirects the user to the login
| page.
|
| The reason is placed in the URL query so the Login page can display the
| correct message.
|
| Examples:
|
| /login?reason=session-expired
| /login?reason=account-deactivated
| /login?reason=account-not-found
|
*/
const redirectToLogin = async (
    reason = "session-expired"
) => {
    /*
     * Avoid duplicate redirects when several requests fail simultaneously.
     */
    if (isRedirectingToLogin) {
        return;
    }

    isRedirectingToLogin = true;

    clearAuthentication();

    try {
        const currentRoute =
            router.currentRoute.value;

        /*
         * Do not redirect again when the user is already on the login page.
         */
        if (currentRoute.name !== "login") {
            await router.replace({
                name: "login",

                query: {
                    reason,
                },
            });
        }
    } catch (redirectError) {
        console.error(
            "Failed to redirect to login:",
            redirectError
        );

        /*
         * Fallback when Vue Router navigation unexpectedly fails.
         */
        window.location.href =
            `/login?reason=${encodeURIComponent(
                reason
            )}`;
    } finally {
        isRedirectingToLogin = false;
    }
};

/*
|--------------------------------------------------------------------------
| Redirect user to forbidden page
|--------------------------------------------------------------------------
|
| Redirect authenticated users to the static 403 page when they are logged in
| but do not have permission to access the requested page or resource.
|
*/
const redirectToForbidden = async (
    error = null
) => {
    if (isRedirectingToForbidden) {
        return;
    }

    isRedirectingToForbidden = true;

    try {
        const currentRoute =
            router.currentRoute.value;

        /*
         * Prevent an infinite redirect loop when the 403 page itself triggers
         * a forbidden API response.
         */
        if (
            currentRoute.name !==
            "forbidden"
        ) {
            await router.replace({
                name: "forbidden",

                query: {
                    from:
                        currentRoute
                            .fullPath ||
                        undefined,
                },

                /*
                 * Store limited information in navigation state.
                 *
                 * Do not expose sensitive backend error details here.
                 */
                state: {
                    message:
                        error?.response
                            ?.data
                            ?.message ||
                        "You do not have permission to access this resource.",
                },
            });
        }
    } catch (redirectError) {
        console.error(
            "Failed to redirect to forbidden page:",
            redirectError
        );

        window.location.href = "/403";
    } finally {
        isRedirectingToForbidden =
            false;
    }
};

/*
|--------------------------------------------------------------------------
| Response interceptor
|--------------------------------------------------------------------------
|
| Global API error handling:
|
| 401 Unauthorized
| - Missing token
| - Invalid token
| - Expired token
| - Revoked token
|
| 403 ACCOUNT_INACTIVE
| - The logged-in user's account was deactivated
|
| 403 Forbidden
| - The user is authenticated but lacks the required permission
|
| 404 Not Found
| - The requested API resource does not exist
|
| 419 Page Expired
| - CSRF/session expiration
|
| 422 Validation Error
| - Validation errors are returned to the component
|
| 429 Too Many Requests
| - Rate limit exceeded
|
| 500+ Server Errors
| - Unexpected backend failure
|
*/
api.interceptors.response.use(
    /*
     * Successful responses pass through unchanged.
     */
    (response) => response,

    async (error) => {
        /*
        |--------------------------------------------------------------------------
        | Network or connection error
        |--------------------------------------------------------------------------
        |
        | When error.response does not exist, the request did not receive an
        | HTTP response.
        |
        | Possible causes:
        |
        | - API server is offline
        | - CORS blocked the request
        | - DNS failure
        | - Internet connection problem
        | - Request timed out
        |
        */
        if (!error.response) {
            if (
                error.code ===
                "ECONNABORTED"
            ) {
                console.error(
                    "API request timed out."
                );
            } else {
                console.error(
                    "Unable to connect to the API server.",
                    error
                );
            }

            return Promise.reject(error);
        }

        const status =
            error.response.status;

        const responseData =
            error.response.data || {};

        const code =
            responseData.code;

        const message =
            responseData.message;

        /*
        |--------------------------------------------------------------------------
        | 403 - Account inactive
        |--------------------------------------------------------------------------
        |
        | This check must be performed before the general 403 handler.
        |
        | An inactive account should be logged out, not redirected to the
        | forbidden page.
        |
        */
        if (
            status === 403 &&
            code === "ACCOUNT_INACTIVE"
        ) {
            console.warn(
                "The authenticated account has been deactivated."
            );

            await redirectToLogin(
                "account-deactivated"
            );

            return Promise.reject(error);
        }

        /*
        |--------------------------------------------------------------------------
        | 401 - Unauthenticated
        |--------------------------------------------------------------------------
        |
        | The token is missing, expired, invalid, or revoked.
        |
        | This also handles custom authentication error codes returned by the
        | Laravel backend.
        |
        */
        if (
            status === 401 ||
            code === "UNAUTHENTICATED" ||
            code === "ACCOUNT_NOT_FOUND"
        ) {
            let reason =
                "session-expired";

            if (
                code ===
                "ACCOUNT_NOT_FOUND"
            ) {
                reason =
                    "account-not-found";
            }

            console.warn(
                message ||
                "The authentication session is no longer valid."
            );

            await redirectToLogin(
                reason
            );

            return Promise.reject(error);
        }

        /*
        |--------------------------------------------------------------------------
        | 403 - Forbidden
        |--------------------------------------------------------------------------
        |
        | The user is authenticated but does not have the permission required
        | by the requested API endpoint.
        |
        | Do not clear the authentication token because the login session is
        | still valid.
        |
        */
        if (status === 403) {
            console.warn(
                message ||
                "Access to the requested resource is forbidden."
            );

            await redirectToForbidden(
                error
            );

            return Promise.reject(error);
        }

        /*
        |--------------------------------------------------------------------------
        | 404 - Resource not found
        |--------------------------------------------------------------------------
        |
        | Do not globally redirect every API 404 to the application's 404 page.
        |
        | A request such as GET /users/999 may fail because only that record
        | does not exist. The component should decide how to display it.
        |
        */
        if (status === 404) {
            console.warn(
                message ||
                "The requested resource was not found."
            );

            return Promise.reject(error);
        }

        /*
        |--------------------------------------------------------------------------
        | 419 - Session or CSRF expired
        |--------------------------------------------------------------------------
        |
        | This is common when cookie-based Sanctum authentication is used.
        |
        | Although RPMCS currently uses bearer tokens, handling 419 keeps the
        | API client compatible with future cookie-based authentication.
        |
        */
        if (status === 419) {
            console.warn(
                message ||
                "The session has expired."
            );

            await redirectToLogin(
                "session-expired"
            );

            return Promise.reject(error);
        }

        /*
        |--------------------------------------------------------------------------
        | 422 - Validation error
        |--------------------------------------------------------------------------
        |
        | Do not redirect or show a global error here.
        |
        | Return the error to the form component so it can display individual
        | field validation messages.
        |
        | Example:
        |
        | error.response.data.errors.email
        | error.response.data.errors.password
        |
        */
        if (status === 422) {
            console.warn(
                "API validation failed:",
                responseData.errors ||
                    responseData
            );

            return Promise.reject(error);
        }

        /*
        |--------------------------------------------------------------------------
        | 429 - Too many requests
        |--------------------------------------------------------------------------
        |
        | The backend rate limiter rejected the request.
        |
        */
        if (status === 429) {
            console.warn(
                message ||
                "Too many requests. Please try again later."
            );

            return Promise.reject(error);
        }

        /*
        |--------------------------------------------------------------------------
        | 500 to 599 - Server errors
        |--------------------------------------------------------------------------
        |
        | These represent unexpected backend errors or temporary service
        | failures.
        |
        */
        if (
            status >= 500 &&
            status <= 599
        ) {
            console.error(
                `Server error ${status}:`,
                responseData
            );

            return Promise.reject(error);
        }

        /*
        |--------------------------------------------------------------------------
        | Other HTTP errors
        |--------------------------------------------------------------------------
        |
        | Log any response that does not match the handlers above.
        |
        */
        console.error(
            `Unhandled API error ${status}:`,
            responseData
        );

        return Promise.reject(error);
    }
);

export default api;