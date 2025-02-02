import Axios from 'axios';
import { computed, ref } from "vue";
import { defineStore } from "pinia";

export const UseStudentStore = defineStore('UseStudentStore', ()=>{
    const landingPageTab = ref(true);
    const courseDetailTab = ref(false);
    const videoPlayerTab = ref(false);

    const courses = ref(null);
    const selectedCourseSlug = ref(null); 

    function fetchCourses() {
        Axios
            .get('/api/courses/course')
            .then(res => courses.value = res.data.data)
    }

    return {
        landingPageTab,
        courseDetailTab,
        videoPlayerTab,

        courses,
        fetchCourses,
        selectedCourseSlug,
    }
});