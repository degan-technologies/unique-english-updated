import Axios from "axios";
import Cookies from "js-cookie"; // Import js-cookie
import { defineStore } from "pinia";
import { computed, ref } from "vue";

Axios.defaults.withCredentials = true;

export const useAppStore = defineStore("useAppStore", () => {
    const authUser = ref(null);
    const frontLang = ref({});
    const logoImage = ref("/images/logo.png");
    const facebook = ref("/socialMediaIcons/face.png");
    const lang = ref("en");
    const google = ref("/socialMediaIcons/google.png");
    const commission = ref(commission);
    const profileUpdated = ref(false);
    const exploreCourses = ref(false);
    const selectedComponentId = ref(null);
    const otpEmail = ref("");

    //hero section
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

    // Use js-cookie to store token and login status
    const authToken = ref(Cookies.get("authToken") || "");
    const loggedIn = ref(Cookies.get("loggedin") === "true");

    const otpPhoneNumber = ref(null);

    const firstName = computed(() => authUser.value?.first_name);
    const middleName = computed(() => authUser.value?.middle_name);
    const lastName = computed(() => authUser.value?.last_name);

    const notifications = ref([]);
    const unreadNotifications = ref(0);

    const isLoggedIn = computed(
        () => loggedIn.value == true && authToken.value != "",
    );

    function setAuthToken(token) {
        authToken.value = token;

        Cookies.set("authToken", token, {
            expires: 7,
            secure: true,
            sameSite: "Strict",
        });

        Axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }

    function changeLoginStatus(status) {
        loggedIn.value = status;
        Cookies.set("loggedin", status.toString(), {
            expires: 7,
            secure: true,
            sameSite: "Strict",
        });
        if (status == false) {
            setAuthToken("");
        } else {
            fetchUserInfo();
            fetchUnreadNotifications();
        }
    }

    // Fetch front languages
    function fetchFrontLanguages() {
        Axios.get(`/language/${lang.value}`).then(
            (response) => (frontLang.value = response.data),
        );
    }

    // Fetch user info
    function fetchUserInfo() {
        Axios.defaults.headers.common["Authorization"] =
            `Bearer ${authToken.value}`;
        Axios.get("/api/current")
            .then((response) => {
                authUser.value = response.data;
                // otpEmail.value = response.data.email;
            })
            .catch((error) => changeLoginStatus(false));
    }

    function logout() {
        Cookies.remove("authToken");
        Cookies.remove("loggedin");
        authToken.value = "";
        loggedIn.value = false;
    }
    // Function to fetch unread notifications from the backend
    async function fetchUnreadNotifications() {
        try {
            Axios.defaults.headers.common["Authorization"] =
                `Bearer ${authToken.value}`;
            const response = await Axios.get("/api/get-notifications");
            unreadNotifications.value = response.data.unReadNotifications;
            notifications.value = response.data.data;
        } catch (error) {}
    }

    function markNotificationAsRead(id) {
        Axios.post(`api/read-notification/${id}`).then((res) => {
            unreadNotifications.value = res.data.unReadNotifications;
        });
        return;
    }

    function getHeroSection() {
        Axios.get("/api/hero-section")
            .then((res) => {
                const heroData = res.data.data;
                hero.value = {
                    title: heroData.title || "",
                    description: heroData.description || "",
                    logo: heroData.logo
                        ? heroData.logo.startsWith("http")
                            ? heroData.logo
                            : `/storage/${heroData.logo}`
                        : null,
                    banner: heroData.banner
                        ? heroData.banner.startsWith("http")
                            ? heroData.banner
                            : `/storage/${heroData.banner}`
                        : null,
                    background_image: heroData.background_image
                        ? heroData.background_image.startsWith("http")
                            ? heroData.background_image
                            : `/storage/${heroData.background_image}`
                        : null,
                    selectedLogo: null,
                    selectedbanner: null,
                    selectedBackground: null,
                };

                // Update logo image for navbar
                if (heroData.logo) {
                    logoImage.value = heroData.logo.startsWith("http")
                        ? heroData.logo
                        : `/storage/${heroData.logo}`;
                }
            })
            .catch((error) => {
                console.error("Error fetching hero section:", error);
                // Initialize with empty values on error
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

    return {
        logoImage,
        isLoggedIn,
        setAuthToken,
        changeLoginStatus,
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
        logout,

        authToken,
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
    };
});
