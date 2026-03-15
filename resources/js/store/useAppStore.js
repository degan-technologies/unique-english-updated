/**
 * useAppStore.js
 *
 * Security model:
 *  - The Passport Bearer token lives ONLY in an HttpOnly cookie set by the
 *    server.  JavaScript never reads or writes it.
 *  - Axios is configured with `withCredentials: true` (see axiosSetup.js) so
 *    the browser sends the HttpOnly cookie automatically on every request.
 *  - `loggedIn` is a plain boolean stored in a regular JS-readable cookie
 *    solely for UX (show/hide nav items etc.).  The server is always the
 *    source of truth for authentication.
 */

import Axios from "axios";
import Cookies from "js-cookie";
import { defineStore } from "pinia";
import { computed, ref } from "vue";

export const useAppStore = defineStore("useAppStore", () => {
    // ─── State ────────────────────────────────────────────────────────────────
    const authUser = ref(null);
    const frontLang = ref({});
    const logoImage = ref("/images/logo.png");
    const facebook = ref("/socialMediaIcons/face.png");
    const lang = ref("en");
    const google = ref("/socialMediaIcons/google.png");
    const commission = ref(null);
    const profileUpdated = ref(false);
    const exploreCourses = ref(false);
    const selectedComponentId = ref(null);

    /** Email shown in the OTP verification modal */
    const otpEmail = ref("");
    const isEmailVerification = ref(false);

    /** Simple boolean persisted in a JS-readable cookie for UX only.
     *  Actual auth is always validated by the server via the HttpOnly cookie. */
    const loggedIn = ref(Cookies.get("loggedin") === "true");

    // ─── Token expiry timer ──────────────────────────────────────────────────
    // Holds the setTimeout handle that fires when the Passport token expires.
    // Declared inside the store so it is garbage-collected with the store.
    let expiryTimer = null;

    const hero = ref({
        title: "",
        description: "",
        logo: null,
        banner: null,
        background_image: null,
        selectedLogo: null,
        selectedbanner: null,
        selectedBackground: null,
    });

    const otpPhoneNumber = ref(null);
    const notifications = ref([]);
    const unreadNotifications = ref(0);

    // ─── Computed ─────────────────────────────────────────────────────────────
    const firstName = computed(() => authUser.value?.first_name);
    const middleName = computed(() => authUser.value?.middle_name);
    const lastName = computed(() => authUser.value?.last_name);

    /**
     * isLoggedIn — derived from the UX boolean cookie.
     * The Axios interceptor (axiosSetup.js) will flip this to false when the
     * server returns 401 (expired / revoked token).
     */
    const isLoggedIn = computed(() => loggedIn.value === true);

    // ─── Auth helpers ─────────────────────────────────────────────────────────

    /**
     * changeLoginStatus
     * Called after a successful login (OTP verified / direct login).
     * Does NOT touch the token — that HttpOnly cookie is owned by the server.
     */
    function changeLoginStatus(status) {
        loggedIn.value = status;
        Cookies.set("loggedin", String(status), {
            expires: 7,
            secure: true,
            sameSite: "Strict",
        });

        if (status) {
            fetchUserInfo();
            fetchUnreadNotifications();
        } else {
            authUser.value = null;
        }
    }

    /**
     * logout
     * Standard logout: ask the server to revoke the Passport token, then clear
     * client state.  The server also expires the HttpOnly authToken cookie.
     */
    async function logout() {
        try {
            await Axios.post("/api/logout");
        } catch {
            // Ignore network errors — clear state regardless
        } finally {
            _clearClientAuth();
        }
    }

    /**
     * forceLogout
     * Called by the 401 Axios interceptor or the idle-timeout composable.
     * Skips the server call (token already invalid) and clears state.
     *
     * @param {string} [reason]  Optional message shown after redirect.
     */
    function forceLogout(reason = "") {
        _clearClientAuth(reason);
    }

    /** Wipe all client-side auth state and redirect to root. */
    function _clearClientAuth(reason = "") {
        // Capture whether the user was actually logged in BEFORE clearing state.
        // This prevents an infinite reload loop when _clearClientAuth is called
        // from the 401 interceptor on page load (where the user was never logged in).
        const wasLoggedIn = loggedIn.value;

        // Cancel any pending expiry timer and remove the stored expiry.
        if (expiryTimer) {
            clearTimeout(expiryTimer);
            expiryTimer = null;
        }
        sessionStorage.removeItem("tokenExpiresAt");

        loggedIn.value = false;
        authUser.value = null;
        Cookies.remove("loggedin");
        otpEmail.value = "";

        if (reason) {
            sessionStorage.setItem("logoutReason", reason);
        }

        // Only force a full reload when the user was genuinely logged in.
        // This guarantees all Vue state is wiped and the login modal appears,
        // even if the expiry timer fires while the user is already on '/'.
        if (wasLoggedIn) {
            window.location.replace("/");
        }
    }

    /**
     * setTokenExpiry
     * Called after every successful login/OTP-verify with the `expires_at`
     * ISO string returned by the server.  schedules a proactive客户端 logout so
     * the user is kicked out as soon as the Passport token expires — even if
     * they are sitting idle and no API call would trigger the 401 interceptor.
     *
     * @param {string} expiresAt  ISO 8601 timestamp from the server response
     */
    function setTokenExpiry(expiresAt) {
        if (!expiresAt) return;
        sessionStorage.setItem("tokenExpiresAt", expiresAt);
        _startExpiryTimer(expiresAt);
    }

    /** Internal: compute remaining ms and arm the logout setTimeout. */
    function _startExpiryTimer(expiresAt) {
        if (expiryTimer) {
            clearTimeout(expiryTimer);
            expiryTimer = null;
        }

        const remainingMs = new Date(expiresAt).getTime() - Date.now();

        if (remainingMs <= 0) {
            // Token already expired (e.g. page refreshed after timeout)
            if (isLoggedIn.value) {
                forceLogout("Your session has expired. Please log in again.");
            }
            return;
        }

        expiryTimer = setTimeout(() => {
            if (isLoggedIn.value) {
                forceLogout("Your session has expired. Please log in again.");
            }
        }, remainingMs);
    }

    // ─── User info ────────────────────────────────────────────────────────────

    /**
     * fetchUserInfo
     * No Authorization header is set here — the InjectBearerTokenFromCookie
     * middleware on the server promotes the HttpOnly cookie automatically.
     */
    function fetchUserInfo() {
        Axios.get("/api/current")
            .then((response) => {
                authUser.value = response.data;
                // Do NOT set otpEmail here — that ref is only for the OTP modal
                // (unverified email flow). Setting it here would wrongly trigger
                // the VerifyOtp modal for every successfully logged-in user.
                loggedIn.value = true;
                Cookies.set("loggedin", "true", {
                    expires: 7,
                    secure: true,
                    sameSite: "Strict",
                });
            })
            .catch(() => {
                _clearClientAuth();
            });
    }

    // ─── Language ─────────────────────────────────────────────────────────────

    function fetchFrontLanguages() {
        Axios.get(`/language/${lang.value}`).then(
            (response) => (frontLang.value = response.data),
        );
    }

    // ─── Notifications ────────────────────────────────────────────────────────

    async function fetchUnreadNotifications() {
        try {
            const response = await Axios.get("/api/get-notifications");
            unreadNotifications.value = response.data.unReadNotifications;
            notifications.value = response.data.data;
        } catch {
            // Silently fail
        }
    }

    function markNotificationAsRead(id) {
        Axios.post(`api/read-notification/${id}`).then((res) => {
            unreadNotifications.value = res.data.unReadNotifications;
        });
    }

    // ─── Hero section ─────────────────────────────────────────────────────────

    function getHeroSection() {
        const toStorage = (path) =>
            path ? (path.startsWith("http") ? path : `/storage/${path}`) : null;

        Axios.get("/api/hero-section")
            .then((res) => {
                const d = res.data.data;
                hero.value = {
                    title: d.title || "",
                    description: d.description || "",
                    logo: toStorage(d.logo),
                    banner: toStorage(d.banner),
                    background_image: toStorage(d.background_image),
                    selectedLogo: null,
                    selectedbanner: null,
                    selectedBackground: null,
                };
                if (d.logo) logoImage.value = toStorage(d.logo);
            })
            .catch(() => {
                hero.value = {
                    title: "",
                    description: "",
                    logo: null,
                    banner: null,
                    background_image: null,
                    selectedLogo: null,
                    selectedbanner: null,
                    selectedBackground: null,
                };
            });
    }

    // ─── OTP email ────────────────────────────────────────────────────────────

    function setOtpEmail(email, isForVerification = false) {
        otpEmail.value = email;
        isEmailVerification.value = isForVerification;
    }

    function clearOtpEmail() {
        otpEmail.value = "";
        isEmailVerification.value = false;
    }

    // ─── Init: restore expiry timer across page refreshes ────────────────────
    {
        const storedExpiry = sessionStorage.getItem("tokenExpiresAt");
        if (storedExpiry && loggedIn.value === true) {
            _startExpiryTimer(storedExpiry);
        }
    }

    // ─── Scroll helper ────────────────────────────────────────────────────────

    function scrollToSection(sectionId) {
        setTimeout(() => {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        }, 100);
    }

    // ─── Expose ───────────────────────────────────────────────────────────────
    return {
        logoImage,
        isLoggedIn,
        loggedIn,

        changeLoginStatus,
        logout,
        forceLogout,
        setTokenExpiry,

        authUser,
        firstName,
        middleName,
        lastName,
        fetchUserInfo,

        lang,
        frontLang,
        fetchFrontLanguages,

        facebook,
        google,
        commission,
        profileUpdated,

        unreadNotifications,
        notifications,
        fetchUnreadNotifications,
        markNotificationAsRead,

        hero,
        getHeroSection,
        exploreCourses,
        selectedComponentId,

        otpEmail,
        isEmailVerification,
        setOtpEmail,
        clearOtpEmail,
        scrollToSection,
    };
});
