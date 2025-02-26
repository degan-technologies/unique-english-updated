import { ref } from "vue";
import { defineStore } from "pinia";

export const useInstructorStore = defineStore('useInstructorStore', ()=>{
    const landingPageTab = ref(true);
    const courseDetailTab = ref(false);
    const videoPlayerTab = ref(false);
    const courseEditTab = ref('edit');
    const courseModuleTab = ref('details');

    const analytics = ref({
        total: 0,
        newToday: 0,
    });

    const instructorCourses = ref([]);
    const selectedCourse = ref(null);
    const editCourseModule = ref(null); 
    const courseId = ref(null);

    return {
        landingPageTab,
        courseDetailTab,
        videoPlayerTab,
        courseEditTab,
        courseModuleTab,
        
        instructorCourses,
        analytics,
        selectedCourse,
        editCourseModule,
        courseId,
    }
});