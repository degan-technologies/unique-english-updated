import Axios from "axios";
import Cookies from "js-cookie";
import { defineStore } from "pinia";
import { computed, ref } from "vue";

Axios.defaults.withCredentials = true;

export const useSessionStore = defineStore("useSessionStore", () => {
    const authUser = ref(null);
    const authToken = ref(Cookies.get("authToken") || "");
    const otpEmail = ref("");
    const isEmailVerification = ref(false);
    const notifications = ref([]);
    const unreadNotifications = ref(0);
    const hasInitialized = ref(false);

    if (authToken.value) {
        Axios.defaults.headers.common["Authorization"] = `Bearer ${authToken.value}`;
    }

    const isLoggedIn = computed(() => authToken.value !== "");

    const readNotifications = computed(() =>
        notifications.value.filter((notification) => notification.read_at),
    );

    function setAuthToken(token) {
        authToken.value = token || "";

        if (authToken.value) {
            Cookies.set("authToken", authToken.value, {
                expires: 7,
                secure: true,
                sameSite: "Strict",
            });
            Cookies.set("loggedin", "true", {
                expires: 7,
                secure: true,
                sameSite: "Strict",
            });
            Axios.defaults.headers.common["Authorization"] = `Bearer ${authToken.value}`;
        } else {
            Cookies.remove("authToken");
            Cookies.remove("loggedin");
            delete Axios.defaults.headers.common["Authorization"];
        }
    }

    function logout() {
        setAuthToken("");
        authUser.value = null;
        notifications.value = [];
        unreadNotifications.value = 0;
        otpEmail.value = "";
        isEmailVerification.value = false;
        hasInitialized.value = false;
    }

    function changeLoginStatus(status) {
        Cookies.set("loggedin", status.toString(), {
            expires: 7,
            secure: true,
            sameSite: "Strict",
        });

        if (!status) {
            logout();
            return;
        }

        initializeSession(true);
    }

    function setOtpEmail(email, isForVerification = false) {
        otpEmail.value = email;
        isEmailVerification.value = isForVerification;
    }

    function clearOtpEmail() {
        otpEmail.value = "";
        isEmailVerification.value = false;
    }

    function fetchUserInfo() {
        if (!authToken.value) {
            return Promise.resolve();
        }

        Axios.defaults.headers.common["Authorization"] = `Bearer ${authToken.value}`;
        return Axios.get("/api/current")
            .then((response) => {
                authUser.value = response.data;
                otpEmail.value = response.data.email;
            })
            .catch(() => changeLoginStatus(false));
    }

    async function fetchUnreadNotifications() {
        if (!authToken.value) {
            return;
        }

        try {
            Axios.defaults.headers.common["Authorization"] = `Bearer ${authToken.value}`;
            const response = await Axios.get("/api/get-notifications");
            unreadNotifications.value = response.data.unReadNotifications;
            notifications.value = response.data.data;
        } catch (error) {
        }
    }

    function markNotificationAsRead(id) {
        return Axios.post(`api/read-notification/${id}`).then((res) => {
            unreadNotifications.value = res.data.unReadNotifications;
            const index = notifications.value.findIndex(
                (notification) => notification.id === id,
            );
            if (index !== -1) {
                notifications.value[index] = res.data.data;
            }
        });
    }

    function markNotificationAsUnread(id) {
        const index = notifications.value.findIndex(
            (notification) => notification.id === id,
        );
        if (index === -1) {
            return;
        }

        notifications.value[index] = {
            ...notifications.value[index],
            read_at: null,
        };

        unreadNotifications.value = notifications.value.filter(
            (notification) => !notification.read_at,
        ).length;
    }

    async function initializeSession(force = false) {
        if (hasInitialized.value && !force) {
            return;
        }

        hasInitialized.value = true;

        if (!authToken.value) {
            return;
        }

        await Promise.all([fetchUserInfo(), fetchUnreadNotifications()]);
    }

    return {
        authUser,
        authToken,
        otpEmail,
        isEmailVerification,
        notifications,
        unreadNotifications,
        readNotifications,
        isLoggedIn,
        setAuthToken,
        changeLoginStatus,
        logout,
        setOtpEmail,
        clearOtpEmail,
        fetchUserInfo,
        fetchUnreadNotifications,
        markNotificationAsRead,
        markNotificationAsUnread,
        initializeSession,
    };
});
