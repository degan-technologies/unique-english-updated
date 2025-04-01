import { ref } from "vue";
import { defineStore } from "pinia";

export const useAuthStore = defineStore('useAuthStore', ()=>{
    const showLoginForm = ref(false);
    const showRegistrationForm = ref(false);

    return {
        showLoginForm,
        showRegistrationForm,
    }
})