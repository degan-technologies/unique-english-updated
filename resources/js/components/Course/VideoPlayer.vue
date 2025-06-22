<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";
import { UseStudentStore } from "@/store/UseStudentStore";
import {
    onMounted,
    ref,
    watch,
    computed,
    onBeforeUnmount,
    watchEffect,
} from "vue"; 

// Components
import QA from "@/components/Exam/QA.vue";
import Spinner from "@/components/Layout/Spinner.vue";
import TextEditor from "@/components/Layout/TextEditor.vue";
import CourseList from "@/components/Course/CourseList.vue";
import QuizReader from "@/components/Course/QuizReader.vue";
import ReviewList from "@/components/Course/ReviewList.vue";
import certificate from "@/components/Course/certificate.vue";
import LessonPdfReader from "@/components/Course/LessonPdfReader.vue";
import LessonVideoPlayer from "@/components/Course/LessonVideoPlayer.vue";
import LessonImageViewer from "@/components/Course/LessonImageViewer.vue";

// Constants
const LESSON_TYPE = "lesson";
const QUIZ_TYPE = "quiz";
const TAB_TYPES = {
    QA: "qa",
    NOTE: "note",
    REVIEW: "review"
};

// Stores and routing
const studentStore = UseStudentStore();
const route = useRoute();
const router = useRouter();
const { selectedCourseSlug, courses, completedLessons } = storeToRefs(studentStore);

// Refs
const overallProgress = ref(0);
const selectedCourse = ref(null);
const selectedModules = ref(null); 
const selectedLesson = ref(null);
const selectedQuiz = ref(null);
const video = ref(null);
const qaSections = ref([]);
const certify = ref(false);
const startLoading = ref(true); 
const collapsModuleId = ref(null);
 
const downloadCertificate = ref(false);

// Content and tabs
const contentType = ref({
    type: LESSON_TYPE,
    id: null,
    lessonId: null,
    quizeId: null,
});
const activeTab = ref(TAB_TYPES.QA);
 

// Initialize course slug from route
selectedCourseSlug.value = route.query.slug; 

// Methods
const handleDownloadCertificate = () => {
    downloadCertificate.value = !downloadCertificate.value;
};

const setActiveTab = (tab) => {
    activeTab.value = tab;
};
 

function openedLesson(lesson) {
    selectedQuiz.value = null; 
    selectedLesson.value = lesson;

    contentType.value = {
        type: LESSON_TYPE, 
        lessonId: lesson.id,
        quizeId: null
    };
}

function openedQuiz(qMetaData) { 
    selectedLesson.value = null;
    selectedQuiz.value = qMetaData;

    contentType.value = {
        type: QUIZ_TYPE, 
        quizeId: qMetaData.id,
        lessonId: null
    };
}


function getCourseModules() {
    Axios.get(`/api/get-course-modules/${selectedCourseSlug.value}`).then((res) => {
        selectedModules.value = res.data.data;
        qaSections.value = res.data.qaSections;
        certify.value = res.data.certify;
    });
}

function continueProgress() {
    Axios.get(`/api/contniue/progress/${selectedCourseSlug.value}`).then((res) => {
        const response = res.data.courseContent
        openedLesson(response);
        overallProgress.value = res.data.overAllPogress;
        collapsModuleId.value = response.module_id;
    });
}

const handleKeyDown = (e) => {
    if (!video.value) return;

    switch (e.key) {
        case "ArrowRight":
            e.preventDefault();
            video.value.currentTime = Math.min(video.value.currentTime + 5, video.value.duration);
            break;
        case "ArrowLeft":
            e.preventDefault();
            video.value.currentTime = Math.max(video.value.currentTime - 5, 0);
            break;
    }
};

function fetchSelectedCourse() {
    if (!selectedCourseSlug.value) return;
    Axios.get(`/api/show-course/${selectedCourseSlug.value}`).then((res) => {
        selectedCourse.value = res.data.data; 
    }).catch(err=>{
        redirectRoute();
    });
}

async function redirectRoute() {
    if (!selectedCourse.value?.isMyCourse) {
        await router.push({
            name: "student",
            query: {
                tab: landingPageTab.value,
            },
        });
    }

    if (!isLoggedIn.value) {
        showLoginForm.value = true;
    }
    return;
}

function toggleModuleLesson(id) {
    collapsModuleId.value = collapsModuleId.value === id ? null : id;
}

// Watchers
watchEffect(() => {
    if (!courses.value) return;
     fetchSelectedCourse();
});

watch(
    () => route.query.slug,
    () => { 
        fetchSelectedCourse();
        getCourseModules();
    }
);

// Lifecycle hooks
onMounted(() => {
    startLoading.value = true;
    getCourseModules();
    continueProgress();
    document.addEventListener("keydown", handleKeyDown);

    if (!courses.value) {
        studentStore.fetchCourses();
    }
    startLoading.value = false;
});
</script>

<template>
    <div v-if="startLoading || !selectedModules || !selectedCourseSlug">
        <Spinner />
    </div>
    <div v-else>
        <div v-if="selectedCourseSlug" class="w-[90%] mx-auto mt-24">
            <div class="flex flex-col md:flex-row gap-4 relative"> 
                <div class="flex-1 flex h-full flex-col gap-4"> 
                    <div class="flex-1 flex flex-col gap-4"> 
                        <template v-if="contentType.type === LESSON_TYPE && selectedLesson?.content_type === 1">
                            <LessonVideoPlayer :selectedLesson="selectedLesson" />
                        </template>
    
                        <template v-else-if="contentType.type === LESSON_TYPE && selectedLesson?.content_type === 2">
                            <LessonPdfReader :selectedLesson="selectedLesson" />
                        </template>

                        <!-- Image Lesson -->
                        <template v-else-if="contentType.type === LESSON_TYPE && selectedLesson?.content_type === 3">
                            <LessonImageViewer :selectedLesson="selectedLesson" />
                        </template>

                        <!-- Quiz -->
                        <template v-else-if="contentType.type === QUIZ_TYPE && selectedQuiz">
                            <QuizReader :quizData="selectedQuiz" />
                        </template>

                        <!-- Lesson Info & Progress -->
                        <div class="bg-white p-4 rounded-b-lg">
                            <div class="flex justify-between mt-2">
                                <div class="flex flex-row gap-2">
                                    <div class="flex flex-col self-center">
                                        <p class="text-xl text-gray-600">
                                            {{ selectedLesson?.title }}
                                        </p> 
                                    </div>
                                </div>
                                <div class="relative flex items-center justify-center">
                                    <div
                                        class="relative w-16 h-16 bg-gray-100 rounded-full border border-lime-700 overflow-hidden">
                                        <div class="absolute bottom-0 left-0 w-full" :style="{
                                            height: 0 + '%',
                                            backgroundColor: '#1E40AF',
                                            transition: 'height 0.5s ease',
                                        }"></div>
                                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                                            <span class="text-lg font-bold text-lime-500">{{ overallProgress[0] }} / {{
                                                overallProgress[1]
                                                }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DESKTOP-ONLY TABS -->
                        <div class="mt-4 hidden md:block bg-white p-4 rounded-lg">
                            <div class="flex justify-start border-b-2 border-gray-200 gap-4">
                                <button @click="setActiveTab(TAB_TYPES.QA)" :class="[
                                    'tab-button px-4 py-2 font-semibold',
                                    activeTab === TAB_TYPES.QA
                                        ? 'border-lime-700 border-b-2 text-lime-700'
                                        : 'hover:border-lime-500',
                                ]">
                                    <i class="fas fa-question pr-2"></i> Q&A
                                </button>
                                <button @click="setActiveTab(TAB_TYPES.NOTE)" :class="[
                                    'tab-button px-4 py-2 font-semibold',
                                    activeTab === TAB_TYPES.NOTE
                                        ? 'border-lime-700 border-b-2 text-lime-700'
                                        : 'hover:border-lime-500',
                                ]">
                                    <i class="fas fa-pen pr-2"></i> Notes
                                </button>
                                <button @click="setActiveTab(TAB_TYPES.REVIEW)" :class="[
                                    'tab-button px-4 py-2 font-semibold',
                                    activeTab === TAB_TYPES.REVIEW
                                        ? 'border-lime-700 border-b-2 text-lime-700'
                                        : 'hover:border-lime-500',
                                ]">
                                    <i class="fas fa-star pr-2"></i> Reviews
                                </button>
                            </div>
                            <div class="mt-4">
                                <QA v-if="activeTab === TAB_TYPES.QA" :selectedCourseSlug="selectedCourseSlug"
                                    :courseId="selectedCourse?.id" />
                                <TextEditor v-else-if="activeTab === TAB_TYPES.NOTE" :selectedLesson="selectedLesson"
                                    :selectedCourse="selectedCourse" :openedLesson="openedLesson" />
                                <ReviewList v-else-if="activeTab === TAB_TYPES.REVIEW"
                                    :showOnly="false"  :courseSlug ="selectedCourseSlug"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Course List -->
                <div class="sticky top-10 mt-8 md:mt-0 md:w-[1fr] bg-white h-fit p-2 rounded-b-lg">


                    <!-- start here -->
                    <div class="border-b mb-2 border-lime-700 text-lime-600">
                        <h2 class="text-2xl leading-9 py-2 font-semibold">Course Lesson</h2>
                    </div>
                    <div v-for="(courseModule, moduleIndex) in Array.isArray(selectedModules) ? selectedModules : []" :key="moduleIndex" class="mb-3 px-2">
                        <div class="flex justify-between items-center">
                            <button @click="toggleModuleLesson(courseModule.id)"
                                class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300">
                                <span class="font-semibold text-lg">{{
                                    courseModule.title
                                    }}</span>
                                <i :class="{
                                    'rotate-90': collapsModuleId == courseModule.id,
                                }" class="fa-solid fa-angle-right text-xl transition-transform duration-300"></i>
                            </button>
                        </div>
                        <CourseList v-if="collapsModuleId == courseModule.id" 
                            @openedLesson="openedLesson"
                            @openedQuiz="openedQuiz"
                            :courseModuleId="courseModule.id"/>
                    </div> 

                    <button @click="handleDownloadCertificate()" :disabled="!certify" :class="certify
                            ? 'text-blue-500 hover:underline'
                            : 'text-gray-400 cursor-not-allowed'
                        " :title="certify
                                ? 'Complete all lessons and quizzes to download your certificate'
                                : 'Download your certificate'
                            " class="leading-relaxed text-lg py-2 flex items-center">
                        <i class="fas fa-certificate text-teal-500 mr-2 ml-2"></i>
                        <strong>Certificate of Completion</strong>
                    </button>

                    <!-- Certificate view -->
                    <div v-if="downloadCertificate && certify">
                        <certificate :selectedCourse="selectedCourse" :overallProgress="overallProgress"
                            @backToHome="handleDownloadCertificate" />
                    </div>
                </div>

                <!-- MOBILE-ONLY TABS -->
                <div class="mt-4 block md:hidden bg-white w-full mx-auto rounded-lg">
                    <div class="flex justify-start border-b-2 border-gray-200 gap-4">
                        <button @click="setActiveTab(TAB_TYPES.QA)" :class="[
                            'tab-button px-4 py-2 font-semibold',
                            activeTab === TAB_TYPES.QA
                                ? 'border-lime-700 border-b-2 text-lime-700'
                                : 'hover:border-lime-500',
                        ]">
                            <i class="fas fa-question pr-2"></i> Q&A
                        </button>
                        <button @click="setActiveTab(TAB_TYPES.NOTE)" :class="[
                            'tab-button px-4 py-2 font-semibold',
                            activeTab === TAB_TYPES.NOTE
                                ? 'border-lime-700 border-b-2 text-lime-700'
                                : 'hover:border-lime-500',
                        ]">
                            <i class="fas fa-pen pr-2"></i> Notes
                        </button>
                        <button @click="setActiveTab(TAB_TYPES.REVIEW)" :class="[
                            'tab-button px-4 py-2 font-semibold',
                            activeTab === TAB_TYPES.REVIEW
                                ? 'border-lime-700 border-b-2 text-lime-700'
                                : 'hover:border-lime-500',
                        ]">
                            <i class="fas fa-star pr-2"></i> Reviews
                        </button>
                    </div>
                    <div class="mt-4">
                        <QA v-if="activeTab === TAB_TYPES.QA" :selectedCourseSlug="selectedCourseSlug"
                            :courseId="selectedCourse?.id" />
                        <TextEditor v-else-if="activeTab === TAB_TYPES.NOTE" :selectedLesson="selectedLesson"
                            :selectedCourse="selectedCourse" :openedLesson="openedLesson" />
                        <ReviewList v-else-if="activeTab === TAB_TYPES.REVIEW"
                             :showOnly="false"  :courseSlug ="selectedCourseSlug"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</template>