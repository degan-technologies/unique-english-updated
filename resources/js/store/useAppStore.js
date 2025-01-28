import Axios from "axios";
import { computed, ref } from "vue";
import { defineStore } from "pinia";
import { useLocalStorage } from "@vueuse/core";

export const useAppStore = defineStore('useAppStore', () => {
    const authUser = ref(null);
    const frontLang = ref({});
    const logoImage = ref("/images/logo.png");
    const lang = ref('en');

    const authToken = useLocalStorage('authToken', '');
    const loggedIn = useLocalStorage('loggedin', false);

    const firstName = computed(() => authUser.value?.first_name);
    const middleName = computed(() => authUser.value?.middle_name);
    const lastName = computed(() => authUser.value?.last_name);

    const isLoggedIn = computed(() => loggedIn.value == true && authToken.value != '');
    
    // set authToken
    function setAuthToken(token) {
        authToken.value = token;
        Axios.defaults.headers.common['Authorization'] = `Bearer ${authToken.value}`;
    }
 
    // change login status
    // either true or false
    function changeLoginStatus(status) {
        loggedIn.value = status;
        if(status == false) setAuthToken('');
        else {
            fetchUserInfo();
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
                projects.value = authUser.value.projects; 
            })
            .catch(error => changeLoginStatus(false))
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
    };
});