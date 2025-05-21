<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
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
import QuizReader from "./QuizReader.vue";
import Spinner from "@/components/Layout/Spinner.vue";
import TextEditor from "@/components/Layout/TextEditor.vue";
import CourseList from "@/components/Course/CourseList.vue";
import ReviewList from "@/components/Course/ReviewList.vue";
import certificate from "@/components/Course/certificate.vue";
import LessonPdfReader from "@/components/Course/LessonPdfReader.vue";
import LessonImageViewer from "@/components/Course/LessonImageViewer.vue";

// Constants
const LESSON_TYPE = "lesson";
const QUIZ_TYPE = "quiz";
const MIN_WATCH_THRESHOLD = 0.95;
const TAB_TYPES = {
    QA: "qa",
    NOTE: "note",
    REVIEW: "review"
};

// Stores and routing
const studentStore = UseStudentStore();
const route = useRoute();
const { selectedCourseSlug, courses, completedLessons } = storeToRefs(studentStore);

// Refs
const overallProgress = ref(0);
const selectedCourse = ref(null);
const selectedModules = ref(null);
const selectedModule = ref(null);
const selectedLesson = ref(null);
const selectedQuiz = ref(null);
const video = ref(null);
const qaSections = ref([]);
const certify = ref(false);
const startLoading = ref(true);

// Video controls
const isPlaying = ref(false);
const showControls = ref(true);
const progress = ref(0);
const currentTime = ref("0:00");
const totalTime = ref("0:00");
const volume = ref(1);
const isMuted = ref(false);
const playbackRate = ref("default");
const isLoading = ref(false);
const volumeControlsVisible = ref(false);
const totalDuration = ref(0);
const watchedTime = ref(0);
const videoWatched = ref(false);
const downloadCertificate = ref(false);

// Content and tabs
const contentType = ref({
    type: LESSON_TYPE,
    id: null,
    lessonId: null,
    quizeId: null,
});
const activeTab = ref(TAB_TYPES.QA);
let hideControlsTimeout = null;

// Computed properties
const feedBacks = computed(() => selectedCourse.value?.feedBacks);
const averageRating = computed(() => selectedCourse.value?.averageRating);
const starDistribution = computed(() => selectedCourse.value?.starDistribution);
const streamVideo = computed(() => selectedLesson?.value?.course_content_url);

// Initialize course slug from route
selectedCourseSlug.value = route.query.slug;

// Methods
const handleDownloadCertificate = () => {
    downloadCertificate.value = !downloadCertificate.value;
};

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const togglePlayPause = () => {
    if (!video.value) return;
    video.value.paused ? video.value.play() : video.value.pause();
    isPlaying.value = !video.value.paused;
};

const toggleMute = () => {
    if (!video.value) return;
    video.value.muted = !video.value.muted;
    isMuted.value = video.value.muted;
};

const handleSeekInput = (event) => {
    if (!video.value) return;
    const seekTime = (event.target.value / 100) * video.value.duration;

    if (isNaN(seekTime) || seekTime < 0 || seekTime > video.value.duration) return;

    if (video.value.readyState < 2) {
        video.value.addEventListener(
            "loadeddata",
            () => { video.value.currentTime = seekTime; },
            { once: true }
        );
        return;
    }

    video.value.currentTime = seekTime;
    progress.value = event.target.value;
    isLoading.value = false;
};

const handleVideoEnd = () => {
    if (watchedTime.value >= totalDuration.value * MIN_WATCH_THRESHOLD) {
        videoWatched.value = true;
        storeContentProgress();
    }
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs < 10 ? "0" : ""}${secs}`;
};

const resetControlsTimeout = () => {
    showControls.value = true;
    if (hideControlsTimeout) clearTimeout(hideControlsTimeout);
    startControlsHideTimer();
};

const startControlsHideTimer = () => {
    hideControlsTimeout = setTimeout(() => {
        if (isPlaying.value) showControls.value = false;
    }, 3000);
};

const handlePlay = () => {
    isPlaying.value = true;
    isLoading.value = false;
    if (progress.value >= 90) {
        storeContentProgress();
    }
};

const handlePause = () => {
    isPlaying.value = false;
    showControls.value = true;
    storeContentProgress();
};

const changeVolume = (event) => {
    if (!video.value) return;
    volume.value = event.target.value;
    video.value.volume = volume.value;
};

const handleWaiting = () => {
    isLoading.value = true;
};

const handleCanPlay = () => {
    isLoading.value = false;
};

const changePlaybackRate = () => {
    if (!video.value || playbackRate.value === "default") return;
    video.value.playbackRate = parseFloat(playbackRate.value);
};

const toggleFullscreen = () => {
    if (!video.value) return;
    document.fullscreenElement
        ? document.exitFullscreen()
        : video.value.requestFullscreen();
};

const changeQuality = (newQuality) => {
    if (!selectedLesson.value?.course_content_url) return;

    const currentUrl = new URL(selectedLesson.value.course_content_url);
    const params = new URLSearchParams(currentUrl.search);

    newQuality === "Auto"
        ? params.delete("quality")
        : params.set("quality", newQuality);

    const newUrl = `${currentUrl.origin}${currentUrl.pathname}?${params.toString()}`;
    selectedLesson.value.course_content_url = newUrl;

    if (video.value) {
        video.value.src = newUrl;
        video.value.load();
        video.value.play();
    }
};

function openedLesson(module, lesson) {
    selectedQuiz.value = null;
    selectedModule.value = module;
    selectedLesson.value = lesson;
    totalTime.value = selectedLesson.value.hour;
    currentTime.value = selectedLesson.value?.courseContentProgress?.video_progress;
    progress.value = selectedLesson.value?.courseContentProgress?.max_video_progress;

    contentType.value = {
        type: LESSON_TYPE,
        id: module.id,
        lessonId: lesson.id,
        quizeId: null
    };
}

function openedQuiz(module, qMetaData) {
    selectedLesson.value = null;
    selectedModule.value = module;
    selectedQuiz.value = qMetaData;

    contentType.value = {
        type: QUIZ_TYPE,
        id: module.id,
        quizeId: qMetaData.id,
        lessonId: null
    };
}

const updateTotalTime = () => {
    if (!video.value?.duration) return;
    isLoading.value = false;
    totalDuration.value = video.value.duration;
    totalTime.value = formatTime(video.value.duration);
    video.value.currentTime = selectedLesson.value?.courseContentProgress?.current_time;
};

function storeContentProgress() {
    if (!video.value || !totalDuration.value) return;
    const current = video.value.currentTime;
    const newProgress = new Date(current * 1000).toISOString().substr(11, 8);
    watchedTime.value = current;
    currentTime.value = formatTime(current);
    progress.value = (current / video.value.duration) * 100;

    Axios.post("/api/coursecontent/progress", {
        course_content_id: selectedLesson.value.id,
        progress: newProgress
    });
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
        openedLesson(res.data.courseModule, res.data.courseContent);
        overallProgress.value = res.data.overAllPogress;
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
    });
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

watch(
    () => selectedLesson.value?.id,
    () => {
        if (contentType.value.type !== LESSON_TYPE) return;
        updateTotalTime();
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

onBeforeUnmount(() => {
    document.removeEventListener("keydown", handleKeyDown);
    if (hideControlsTimeout) clearTimeout(hideControlsTimeout);
});
</script>

<template>
    <div v-if="startLoading">
        <Spinner />
    </div>
    <div v-else>
        <div v-if="selectedCourseSlug" class="w-[90%] mx-auto mt-24">
            <div class="flex flex-col md:flex-row gap-4 relative"> 
                <div class="flex-1 flex  flex-col gap-4"> 
                    <div class="flex-1 flex flex-col gap-4"> 
                        <template v-if="contentType.type === LESSON_TYPE && selectedLesson?.content_type === 1">
                            <div class="video-container relative bg-black h-96 rounded-lg overflow-hidden border-2 border-lime-700"
                                tabindex="0" @mousemove="resetControlsTimeout" @mouseleave="startControlsHideTimer"
                                @mouseenter="resetControlsTimeout" @click="togglePlayPause" @keydown="handleKeyDown">
                                <video ref="video" class="w-full h-full object-contain" :src="streamVideo"
                                    :poster="selectedLesson?.thumbnail_url" @loadedmetadata="updateTotalTime"
                                    @ended="handleVideoEnd" @play="handlePlay" @pause="handlePause" @waiting="handleWaiting"
                                    @canplay="handleCanPlay" playsinline>
                                    Your browser does not support the video tag.
                                </video>

                                <div v-if="isLoading"
                                    class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                                    <div class="spinner">
                                        <Spinner />
                                    </div>
                                </div>

                                <!-- Controls Overlay -->
                                <div v-show="showControls"
                                    class="absolute bottom-0 left-0 right-0 pl-2 pr-2 pb-1 bg-lime-700 transition-opacity duration-300 z-20 gap-2"
                                    @click.stop>
                                    <!-- Single Seek Bar -->
                                    <div class="mb-1">
                                        <input type="range" :value="progress" @input="handleSeekInput" min="0" max="100"
                                            step="0.01" class="w-full h-1 bg-white rounded-md" aria-label="Seek Video" />
                                    </div>

                                    <!-- MOBILE CONTROLS (2 ROWS) -->
                                    <div class="flex flex-col sm:hidden" style="padding: 4px 0">
                                        <!-- Row 2: All Other Controls -->
                                        <div class="flex items-center justify-between px-2">
                                            <!-- Play Button -->
                                            <button @click="togglePlayPause" class="text-white p-1" aria-label="Play/Pause">
                                                <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
                                            </button>

                                            <!-- Volume Control -->
                                            <div class="flex items-center space-x-1">
                                                <button @click="toggleMute" class="text-white p-1" aria-label="Mute/Unmute">
                                                    <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                                                </button>
                                                <input type="range" :value="volume" @input="changeVolume" min="0" max="1"
                                                    step="0.01" class="w-12 h-1 rounded-md bg-gray-300"
                                                    aria-label="Volume Control" />
                                            </div>

                                            <!-- Time Display -->
                                            <span class="text-white text-xs whitespace-nowrap mx-2">
                                                {{ currentTime }}/{{ totalTime }}
                                            </span>

                                            <!-- Quality Selector -->
                                            <select @change="changeQuality($event.target.value)"
                                                class="text-white text-xs bg-lime-700 p-1 rounded mr-1"
                                                style="max-width: 70px">
                                                <option value="Auto">Auto</option>
                                                <option value="480p">480p</option>
                                                <option value="720p">720p</option>
                                            </select>

                                            <!-- Playback Speed -->
                                            <select v-model="playbackRate" @change="changePlaybackRate"
                                                class="text-white text-xs bg-lime-700 p-1 rounded mr-1"
                                                style="max-width: 60px">
                                                <option value="1.0">1x</option>
                                                <option value="1.5">1.5x</option>
                                                <option value="2.0">2x</option>
                                            </select>

                                            <!-- Fullscreen -->
                                            <button @click="toggleFullscreen" class="text-white p-1 ml-auto"
                                                aria-label="Fullscreen">
                                                <i class="fas fa-expand"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- DESKTOP CONTROLS -->
                                    <div class="hidden sm:flex sm:items-center sm:gap-4">
                                        <button @click="togglePlayPause" class="text-white text-sm flex items-center"
                                            aria-label="Play/Pause">
                                            <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
                                        </button>
                                        <div class="flex-1 flex items-center gap-4">
                                            <div class="relative flex items-center overflow-hidden transition-all duration-300"
                                                :class="volumeControlsVisible ? 'w-40' : 'w-12'"
                                                @mouseenter="volumeControlsVisible = true"
                                                @mouseleave="volumeControlsVisible = false">
                                                <button @click="toggleMute" class="text-white p-2" aria-label="Mute/Unmute">
                                                    <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                                                </button>
                                                <div v-if="volumeControlsVisible" class="flex items-center space-x-1 ml-2">
                                                    <span class="text-xs text-white">
                                                        {{ Math.round(volume * 100) }}%
                                                    </span>
                                                    <input type="range" :value="volume" @input="changeVolume" min="0"
                                                        max="1" step="0.01" class="w-16 h-1 rounded-md bg-gray-300"
                                                        aria-label="Volume Control" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-white text-sm whitespace-nowrap">
                                            <span>{{ currentTime }}</span> /
                                            <span>{{ totalTime }}</span>
                                        </div>
                                        <select @change="changeQuality($event.target.value)"
                                            class="text-white text-sm bg-lime-700 p-1 rounded">
                                            <option value="Auto">Auto</option>
                                            <option value="1080p">1080p</option>
                                            <option value="720p">720p</option>
                                            <option value="480p">480p</option>
                                        </select>
                                        <select v-model="playbackRate" @change="changePlaybackRate"
                                            class="text-white text-sm bg-lime-700 p-1 rounded">
                                            <option disabled value="default">1x</option>
                                            <option value="0.5">0.5x</option>
                                            <option value="1.0">1x</option>
                                            <option value="1.5">1.5x</option>
                                            <option value="2.0">2x</option>
                                        </select>
                                        <button @click="toggleFullscreen" class="text-white flex items-center p-2"
                                            aria-label="Fullscreen">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
    
                        <template v-else-if="contentType.type === LESSON_TYPE && selectedLesson?.content_type === 2">
                            <LessonPdfReader :selectedLesson="selectedLesson" />
                        </template>

                        <!-- Image Lesson -->
                        <template v-else-if="contentType.type === LESSON_TYPE && selectedLesson?.content_type === 3">
                            <LessonImageViewer :selectedLesson="selectedLesson" />
                        </template>

                        <!-- Quiz -->
                        <template v-else-if="contentType.type === QUIZ_TYPE">
                            <QuizReader :quizData="selectedQuiz" />
                        </template>

                        <!-- Lesson Info & Progress -->
                        <div class="bg-white p-4 rounded-b-lg">
                            <div class="flex justify-between mt-2">
                                <div class="flex flex-row gap-2">
                                    <div class="flex flex-col self-center">
                                        <p class="text-md text-gray-600">
                                            {{ selectedLesson?.title }}
                                        </p>
                                        <p class="text-xl text-blue-600">
                                            {{ selectedModule?.title }}
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
                                <ReviewList v-else-if="activeTab === TAB_TYPES.REVIEW" :feedBacks="feedBacks"
                                    :averageRating="averageRating" :starDistribution="starDistribution" :showOnly="false" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Course List -->
                <div class="sticky top-10 mt-8 md:mt-0 md:w-[1fr] bg-white h-fit p-2 rounded-b-lg">
                    <CourseList v-if="selectedModules" :selectedModules="selectedModules" :contentType="contentType"
                        :certify="certify" @openedLesson="openedLesson" @openedQuiz="openedQuiz"
                        @downloadCertificate="handleDownloadCertificate" />
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
                        <ReviewList v-else-if="activeTab === TAB_TYPES.REVIEW" :feedBacks="feedBacks"
                            :averageRating="averageRating" :starDistribution="starDistribution" :showOnly="false" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</template>