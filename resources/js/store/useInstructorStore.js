import { ref } from "vue";
import { defineStore } from "pinia";

export const useInstructorStore = defineStore('useInstructorStore', ()=>{
    const landingPageTab = ref(true);
    const courseDetailTab = ref(false);
    const videoPlayerTab = ref(false);
    const courseEditTab = ref('edit');
    const courseModuleTab = ref('details');
    const readlessonPdfTab = ref('readlessonPdf');

    const selectedLesson = ref(null);
    const addNewLesson = ref(null);

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
        readlessonPdfTab,
        
        selectedLesson,
        addNewLesson,
        
        instructorCourses,
        analytics,
        selectedCourse,
        editCourseModule,
        courseId,
    }
});