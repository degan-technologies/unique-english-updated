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

    const courses = ref(null);
    const selectedCourseSlug = ref(null);

    const books = ref(null);
    const selectedbookslug = ref(null);
    const completedLessons = ref(new Set());

    async function fetchCourses() {
        await Axios.get("/api/all-couses").then(
            (res) => (courses.value = res.data.data)
        );
    }

    async function fetchBooks() {
        await Axios.get("/api/all-books").then(
            (res) => (books.value = res.data.data)
        );
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
