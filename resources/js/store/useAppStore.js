import Axios from "axios";
import { computed, ref } from "vue";
import { defineStore } from "pinia";
import Cookies from "js-cookie";  // Import js-cookie

Axios.defaults.withCredentials = true;

export const useAppStore = defineStore('useAppStore', () => {
    const authUser = ref(null);
    const frontLang = ref({});
    const logoImage = ref("/images/logo.png");
    const facebook = ref("/socialMediaIcons/face.png");
    const lang = ref('en');
    const google = ref("/socialMediaIcons/google.png");
    const commission = ref(commission);
    const profileUpdated = ref(false);

    //hero section 

    const hero = ref({});

    // Use js-cookie to store token and login status
    const authToken = ref(Cookies.get('authToken') || '');
    const loggedIn = ref(Cookies.get('loggedin') === 'true');  // Cookies store boolean as string

    const otpPhoneNumber = ref(null);

    const firstName = computed(() => authUser.value?.first_name);
    const middleName = computed(() => authUser.value?.middle_name);
    const lastName = computed(() => authUser.value?.last_name);

    const unreadNotifications = ref([]);

    const isLoggedIn = computed(() => loggedIn.value == true && authToken.value != '');



    // set authToken and store it in cookies
    function setAuthToken(token) { 

        // Store token in Pinia state
        authToken.value = token;

        // Store token in Cookies (7 days expiration)
        Cookies.set('authToken', token, {
            expires: 7,             // Cookie expires in 7 days
            secure: true,           // Send cookie only over HTTPS (set to false for local dev)
            sameSite: 'Strict'      // Prevent CSRF attacks by limiting cross-site usage
        });

        // Set Authorization header for future Axios requests
        Axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }


    // change login status and store it in cookies
    function changeLoginStatus(status) {
        loggedIn.value = status;
        Cookies.set('loggedin', status.toString(), { expires: 7, secure: true, sameSite: 'Strict' });
        if (status == false) {
            setAuthToken('');
        } else {
            fetchUserInfo();
            fetchUnreadNotifications();
        }  
    }

    // Fetch front languages
    function fetchFrontLanguages() {
        Axios
            .get(`/language/${lang.value}`)
            .then(response => frontLang.value = response.data);
    }

    // Fetch user info
    function fetchUserInfo() {
        Axios.defaults.headers.common['Authorization'] = `Bearer ${authToken.value}`;
        Axios
            .get('/api/current')
            .then(response => {
                authUser.value = response.data;
            })
            .catch(error => changeLoginStatus(false))
    }

    function logout() {
        Cookies.remove('authToken');
        Cookies.remove('loggedin');
        authToken.value = '';
        loggedIn.value = false;
    }
    // Function to fetch unread notifications from the backend
    async function fetchUnreadNotifications() {
        try {
            Axios.defaults.headers.common['Authorization'] = `Bearer ${authToken.value}`;
            const response = await Axios.get('/api/notifications/unread');
            unreadNotifications.value = response.data;
        } catch (error) {
            console.error('Error fetching unread notifications:', error);
        }
    }

    function markNotificationAsRead(id) {
        return Axios.post(`api/notifications/${id}/read`)

    }

    function getHeroSection() {
        Axios
        .get('/api/hero-section')
        .then(res => {
            hero.value = {...res.data.data}
            logoImage.value = hero.value.logo;
            return;
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
        fetchUnreadNotifications,
        markNotificationAsRead,

        hero,
        getHeroSection,
    };
});