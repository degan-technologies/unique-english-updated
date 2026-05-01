import Axios from "axios";
import { computed, ref } from "vue";
import { defineStore } from "pinia";

export const UseStudentStore = defineStore("UseStudentStore", () => {
    const landingPageTab = ref();
    const courseDetailTab = ref("courseDetail");
    const videoPlayerTab = ref("mylesson");
    const bookOverviewTab = ref("bookOverview");
    const bookReadingTab = ref("bookReading");
    const liveSchedulTab = ref("liveSchedul");
    const myCourseTab = ref("myCourse");
    const TestTab = ref("testTab");
    const profile = ref("profile");
    const freeCourses = ref("freeCourses");
    const blogTab = ref("blog");

    const courses = ref(null);
    const selectedCourseSlug = ref(null);

    const books = ref(null);
    const selectedbookslug = ref(null);
    const completedLessons = ref(new Set());

    async function fetchCourses(force = false) {
        if (courses.value && !force) {
            return;
        }

        const response = await Axios.get("/api/all-couses");
        courses.value = response.data.data;
    }

    async function fetchBooks(force = false) {
        if (books.value && !force) {
            return;
        }

        const response = await Axios.get("/api/all-books");
        books.value = response.data.data;
    }

    return {
        landingPageTab,
        courseDetailTab,
        videoPlayerTab,
        bookOverviewTab,
        bookReadingTab,
        liveSchedulTab,
        myCourseTab,
        profile,
        freeCourses,
        blogTab,

        courses,
        fetchCourses,
        selectedCourseSlug,

        books,
        fetchBooks,
        selectedbookslug,

        completedLessons,
        TestTab,
    };
});
