/**
 * axiosSetup.js
 *
 * Centralised Axios configuration for the application.
 *
 * Security principles:
 *  - withCredentials: true  → the HttpOnly authToken cookie is sent to the
 *    server automatically on every request; JS never reads the cookie value.
 *  - NO Authorization header is ever set from JavaScript.  The
 *    InjectBearerTokenFromCookie middleware on the Laravel side promotes the
 *    cookie to a Bearer header before Passport's guard runs.
 *  - 401 interceptor → auto-logout on expired/revoked token so stale sessions
 *    are cleaned up immediately.
 *
 * Import this file ONCE in app.js before the Vue app is mounted.
 */

import Axios from "axios";

// ─── Base configuration ────────────────────────────────────────────────────
Axios.defaults.withCredentials = true; // Send HttpOnly cookie on every request
Axios.defaults.headers.common["Accept"] = "application/json";
Axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
// Do NOT set Authorization here — the backend middleware handles it from the
// HttpOnly cookie.

// ─── Response interceptor ─────────────────────────────────────────────────
Axios.interceptors.response.use(
    // Pass successful responses straight through
    (response) => response,

    // Handle errors
    async (error) => {
        const status = error.response?.status;

        if (status === 401) {
            /**
             * 401 Unauthorized:
             *   - The Passport token expired, was revoked, or the cookie was
             *     cleared.
             *   - Import the store lazily to avoid circular dependency issues.
             */
            const { useAppStore } = await import("@/store/useAppStore");
            const appStore = useAppStore();

            // Only act if the store thinks the user is logged in (prevents
            // an infinite loop if the logout request itself returns 401).
            if (appStore.loggedIn) {
                await appStore.forceLogout();
            }
        }

        return Promise.reject(error);
    },
);

export default Axios;
