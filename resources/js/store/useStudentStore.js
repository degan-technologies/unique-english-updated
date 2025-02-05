import Axios from 'axios';
import { computed, ref } from "vue";
import { defineStore } from "pinia";

export const UseStudentStore = defineStore('UseStudentStore', ()=>{
    const landingPageTab = ref();
    const courseDetailTab = ref('courseDetail');
    const videoPlayerTab = ref('mylesson');
    
    const courses = ref(null);
    const selectedCourseSlug = ref(null); 

   async function fetchCourses() {
       await Axios
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