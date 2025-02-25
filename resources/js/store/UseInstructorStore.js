import { ref } from "vue";
import { defineStore } from "pinia";

export const useInstructorStore = defineStore('useInstructorStore', ()=>{
    const landingPageTab = ref(true);
    const courseDetailTab = ref(false);
    const videoPlayerTab = ref(false);

    return {
        landingPageTab,
        courseDetailTab,
        videoPlayerTab,
    }
});