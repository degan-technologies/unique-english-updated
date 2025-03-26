<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { useRoute } from "vue-router";
import { useAppStore } from "@/store/useAppStore";
import { UseStudentStore } from "@/store/UseStudentStore";
import { onMounted, ref, watch, computed, onBeforeUnmount, watchEffect } from "vue";

import QA from "@/components/Exam/QA.vue";
import TextEditor from "@/components/Layout/TextEditor.vue";
import CourseList from "@/components/Course/CourseList.vue";
import ReviewList from "@/components/Course/ReviewList.vue";
import QuizReader from "./QuizReader.vue";
import certificate from "@/components/Course/certificate.vue"

const studentStore = UseStudentStore();
const route = useRoute();

const { selectedCourseSlug, courses, completedLessons } = storeToRefs(studentStore);

const overallProgress = ref(0);
selectedCourseSlug.value = route.query.slug;

const selectedCourse = ref(null);
const selectedModules = ref(null);
const selectedModule = ref(null);
const selectedLesson = ref(null);
const selectedQuiz = ref(null);
const volumeControlsVisible = ref(false);
const totalDuration = ref(0);
const watchedTime = ref(0);
const videoWatched = ref(false);
const minWatchThreshold = 0.95;

const downloadCertificate = ref(false);

const video = ref(null);
const isPlaying = ref(false);
const showControls = ref(true);
const progress = ref(0);
const currentTime = ref("0:00");
const totalTime = ref("0:00");
const volume = ref(1);
const isMuted = ref(false);
const playbackRate = ref("default");
const selectedQuality = ref("1080p");

let hideControlsTimeout = null;

const handleDownloadCertificate = (status) => {
    downloadCertificate.value = status;
};
const handleCancelCertificate = () => {
    downloadCertificate.value = false;
};

const activeTab = ref("qa");
const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const contentType = computed(() => {
    if (selectedLesson.value) return "lesson";
    if (selectedQuiz.value) return "quiz";
    return null;
});

const togglePlayPause = () => {
    if (!video.value) return;
    if (video.value.paused) {
        video.value.play();
        isPlaying.value = true;
    } else {
        video.value.pause();
        isPlaying.value = false;
    }
};

const handleSeekInput = (event) => {
    if (!video.value) return;
    const seekTime = (event.target.value / 100) * video.value.duration;

    if (isNaN(seekTime) || seekTime < 0 || seekTime > video.value.duration) return;
    if (video.value.readyState < 2) {
        video.value.addEventListener("loadeddata", () => {
            video.value.currentTime = seekTime;
        }, { once: true });
        return;
    }

    video.value.currentTime = seekTime; 
    progress.value = event.target.value;
};

const storageKey = computed(() => {
    return video.value && video.value.src
        ? "videoSeek_" + btoa(video.value.src)
        : "videoSeek_default";
});

const handleVideoEnd = () => {
    if (watchedTime.value >= totalDuration.value * minWatchThreshold) {
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
    resetControlsTimeout();
    storeContentProgress();
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

const toggleMute = () => {
    if (!video.value) return;
    video.value.muted = !video.value.muted;
    isMuted.value = video.value.muted;
};

const changePlaybackRate = () => {
    if (!video.value || playbackRate.value === "default") return;
    video.value.playbackRate = parseFloat(playbackRate.value);
};

const toggleFullscreen = () => {
    if (!video.value) return;
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        video.value.requestFullscreen();
    }
};

const changeQuality = (newQuality) => {
    if (!selectedLesson.value || !selectedLesson.value.course_content_url) {
        return;
    }

    let currentUrl = new URL(selectedLesson.value.course_content_url);
    let params = new URLSearchParams(currentUrl.search);

    if (newQuality === "Auto") {
        params.delete("quality"); 
    } else {
        params.set("quality", newQuality); 
    }

    let newUrl = `${currentUrl.origin}${currentUrl.pathname}?${params.toString()}`;

    selectedLesson.value.course_content_url = newUrl;

    if (video.value) {
        video.value.src = newUrl;  
        video.value.load(); 
        video.value.play(); 
    } else {
    }
};

function openedLesson(module, lesson) {
    selectedQuiz.value = null;
    selectedModule.value = module;
    selectedLesson.value = lesson;

    videoWatched.value = false;
    watchedTime.value = 0;
    totalDuration.value = 0;

    if (selectedLesson.value && video.value) {
        video.value.src = selectedLesson.value.course_content_url;
        video.value.load();
        video.value.play();
        
    }
}

function openedQuiz(module, qMetaData) {
    selectedLesson.value = null;
    selectedModule.value = module;
    selectedQuiz.value = qMetaData;
}

const updateTotalTime = () => {
    if (!video.value || !video.value.duration) return;
    totalDuration.value = video.value.duration;
    totalTime.value = formatTime(video.value.duration);
    const savedSeek = localStorage.getItem(storageKey.value);
    if (savedSeek) {
        video.value.currentTime = Number(savedSeek);
    }
};

function storeContentProgress() {
    if (!video.value || totalDuration.value === 0) return;
    const current = video.value.currentTime;
    const newPogress = new Date(current * 1000).toISOString().substr(11, 8);
    watchedTime.value = current; 
    currentTime.value = formatTime(current);
    progress.value = (video.value.currentTime / video.value.duration) * 100;

    const currentLessonId = selectedLesson.value.id;

    // let progressRecord = courseListRef.value.progressRecords[currentLessonId];
 
    // if (progressRecord && Number(progressRecord.progress) >= minWatchThreshold) {
    //     return;
    // }

    const payload = {
        course_content_id: currentLessonId,
        progress: newPogress,  
    };

    Axios
        .post("/api/coursecontent/progress", payload)
        .then(res => {})
};

function getCourseModules() {
    Axios
        .get(`/api/get-course-modules/${selectedCourseSlug.value}`)
        .then(res => {
            selectedModules.value = res.data.data;
        })
}

function contniueProgress() {
    Axios
        .get(`/api/contniue/progress/${selectedCourseSlug.value}`)
        .then(res => {
            openedLesson(res.data.courseModule, res.data.courseContent);
            overallProgress.value = res.data.overAllPogress;
        })
}

watchEffect(() => {
    if (!courses.value) return;
    selectedCourse.value = courses.value.find(item => item.slug === selectedCourseSlug.value);
});

watch(() => route.query.slug, () => {
    selectedCourseSlug.value = route.query.slug;
    selectedCourse.value = courses.value.find(item => item.slug === selectedCourseSlug.value);
    getCourseModules();
});

watch(selectedLesson.value, (newVal, oldVal) => {
    if (newVal && newVal !== oldVal && video.value) {
    }
});

const handleKeyDown = (e) => {
    if (!video.value) return;
    let currentVolume = Number(volume.value);
    if (!isFinite(currentVolume)) {
        currentVolume = 0.5; // fallback to a default volume if not a number
    }

    if (e.key === 'ArrowUp') {
        // Increase volume by 5%
        e.preventDefault();
        let newVolume = Math.min(currentVolume + 0.05, 1);
        if (isFinite(newVolume)) {
            volume.value = newVolume;
            video.value.volume = newVolume;
        }
    } else if (e.key === 'ArrowDown') {
        // Decrease volume by 5%
        e.preventDefault();
        let newVolume = Math.max(currentVolume - 0.05, 0);
        if (isFinite(newVolume)) {
            volume.value = newVolume;
            video.value.volume = newVolume;
        }
    } else if (e.key === 'ArrowRight') {
        // Seek forward 5 seconds
        e.preventDefault();
        video.value.currentTime = Math.min(video.value.currentTime + 5, video.value.duration);
    } else if (e.key === 'ArrowLeft') {
        // Seek backward 5 seconds
        e.preventDefault();
        video.value.currentTime = Math.max(video.value.currentTime - 5, 0);
    } else if (e.key === ' ' || e.code === 'Space') {
        e.preventDefault();
        togglePlayPause();
    }
};

onMounted(() => {
    getCourseModules();
    contniueProgress();
    // Make sure your video container is focusable
    const videoContainer = document.querySelector(".video-container");
    if (videoContainer) {
        videoContainer.setAttribute("tabindex", "0");
    }

    studentStore.fetchCourses();
    if (!video.value) {
        return;
    }
    video.value.volume = volume.value;
    video.value.playbackRate = parseFloat(playbackRate.value) || 1.0;
    video.value.addEventListener("loadedmetadata", updateTotalTime);
    video.value.addEventListener("timeupdate", storeContentProgress);

    // Attach keydown event listener to document instead of window
    document.addEventListener("keydown", handleKeyDown);

    video.value.load();
});

onBeforeUnmount(() => {
    document.removeEventListener("keydown", handleKeyDown);
});

</script>

<template>
    <div v-if="selectedCourseSlug"
        class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative mt-24 bg-slate-50">
        <div v-if="downloadCertificate && overallProgress === 100">
            <certificate :selectedCourse="selectedCourse" :overallProgress="overallProgress"
                @cancelCertificate="handleCancelCertificate" />
        </div>


        <div v-else class="flex-1 flex flex-col gap-4">
            <template v-if="contentType === 'lesson'">
                <!-- Make the container focusable by adding tabindex -->
                <div class="video-container relative bg-black rounded-lg overflow-hidden border-2 border-lime-700"
                    tabindex="0" @mousemove="resetControlsTimeout" @mouseleave="startControlsHideTimer"
                    @mouseenter="resetControlsTimeout" @click="togglePlayPause" @keydown="handleKeyDown">

                    <div v-if="!isPlaying" class="relative w-full h-full cursor-pointer" @click="handlePlay">
                        <img :src="selectedLesson?.thumbnail_url" alt="Course Thumbnail"
                            class="w-full h-full object-cover transition-transform duration-300 rounded-t-lg shadow-lg hover:shadow-xl" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="p-4 bg-lime-500 rounded-full animate-breathe flex items-center justify-center">
                                <i class="fas fa-play-circle text-white text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Video Element: Remove pointer-events-none -->
                    <video v-else ref="video" class="w-full h-full object-cover" :src="selectedLesson?.course_content_url"
                        @timeupdate="storeContentProgress" @loadedmetadata="updateTotalTime" @ended="handleVideoEnd"
                        @play="handlePlay" @pause="handlePause" playsinline>
                        Your browser does not support the video tag.
                    </video>

                    <!-- Custom Controls Overlay with reduced height -->
                    <div v-show="showControls"
                        class="absolute bottom-0 left-0 right-0 pl-2 pt-0 pr-2 pb-1 bg-lime-700 bg-opacity-100 transition-opacity duration-300 z-20 gap-2"
                        @click.stop>
                        <!-- Seek Bar (Full Width) -->
                        <div class="mb-1">
                            <input type="range" :value="progress" @input="handleSeekInput" min="0" max="100" step="0.01"
                                class="w-full h-1 bg-white rounded-md transition-all duration-300"
                                aria-label="Seek Video" />
                        </div>

                        <!-- MOBILE LAYOUT: below sm breakpoint -->
                        <div class="flex flex-col gap-2 sm:hidden">
                            <!-- Row 1: Volume & Time Display -->
                            <div class="flex items-center justify-between">
                                <!-- Volume Controls -->
                                <div class="relative flex items-center overflow-hidden transition-all duration-300"
                                    :class="volumeControlsVisible ? 'w-40' : 'w-12'"
                                    @mouseenter="volumeControlsVisible = true"
                                    @mouseleave="volumeControlsVisible = false">
                                    <button @click="toggleMute" class="text-white p-2" aria-label="Mute/Unmute">
                                        <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                                    </button>
                                    <div v-if="volumeControlsVisible" class="flex items-center space-x-1 ml-2">
                                        <span class="text-xs text-white">{{ Math.round(volume * 100) }}%</span>
                                        <input type="range" :value="volume" @input="changeVolume" min="0" max="1"
                                            step="0.01" class="w-16 h-1 rounded-md bg-gray-300"
                                            aria-label="Volume Control" />
                                    </div>
                                </div>
                                <!-- Time Display -->
                                <div class="text-white text-sm whitespace-nowrap">
                                    <span>{{ currentTime }}</span> / <span>{{ totalTime }}</span>
                                </div>
                            </div>
                            <!-- Row 2: Quality, Playback Rate, and Fullscreen -->
                            <div class="flex items-center justify-end gap-2">
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

                        <!-- DESKTOP LAYOUT: sm and above -->
                        <div class="hidden sm:flex sm:items-center sm:gap-4">
                            <!-- Play/Pause Button -->
                            <button @click="togglePlayPause" class="text-white text-sm flex items-center"
                                aria-label="Play/Pause">
                                <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
                            </button>
                            <!-- Volume Controls & Other Controls -->
                            <div class="flex-1 items-center gap-4">
                                <!-- Volume Controls -->
                                <div class="relative flex items-center overflow-hidden transition-all duration-300"
                                    :class="volumeControlsVisible ? 'w-40' : 'w-12'"
                                    @mouseenter="volumeControlsVisible = true"
                                    @mouseleave="volumeControlsVisible = false">
                                    <button @click="toggleMute" class="text-white p-2" aria-label="Mute/Unmute">
                                        <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                                    </button>
                                    <div v-if="volumeControlsVisible" class="flex items-center space-x-1 ml-2">
                                        <span class="text-xs text-white">{{ Math.round(volume * 100) }}%</span>
                                        <input type="range" :value="volume" @input="changeVolume" min="0" max="1"
                                            step="0.01" class="w-16 h-1 rounded-md bg-gray-300"
                                            aria-label="Volume Control" />
                                    </div>
                                </div>
                            </div>
                            <!-- Time Display -->
                            <div class="text-white text-sm whitespace-nowrap">
                                <span>{{ currentTime }}</span> / <span>{{ totalTime }}</span>
                            </div>
                            <!-- Quality Selection -->
                            <select @change="changeQuality($event.target.value)"
                                class="text-white text-sm bg-lime-700 p-1 rounded">
                                <option value="Auto">Auto</option>
                                <option value="1080p">1080p</option>
                                <option value="720p">720p</option>
                                <option value="480p">480p</option>
                            </select>
                            <!-- Playback Rate Selection -->
                            <select v-model="playbackRate" @change="changePlaybackRate"
                                class="text-white text-sm bg-lime-700 p-1 rounded">
                                <option disabled value="default">1x</option>
                                <option value="0.5">0.5x</option>
                                <option value="1.0">1x</option>
                                <option value="1.5">1.5x</option>
                                <option value="2.0">2x</option>
                            </select>
                            <!-- Fullscreen Button -->
                            <button @click="toggleFullscreen" class="text-white flex items-center p-2"
                                aria-label="Fullscreen">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>


            <template v-else-if="contentType === 'quiz'">
                <!-- Quiz Component -->
                <QuizReader :quizData="selectedQuiz" />
            </template>

            <div class="bg-white p-4 py-8 rounded-b-lg">
                <div class="flex justify-between">
                    <div class="flex flex-col">
                        <p class="text-lg text-gray-600 py-2">{{ selectedLesson?.title }}</p>
                        <p class="text-2xl text-blue-600">{{ selectedModule?.title }}</p>
                    </div>

                    <div class="relative mt-2 flex items-center justify-center">
                        <div class="relative w-16 h-16 bg-gray-100 rounded-full border border-lime-700 overflow-hidden">
                            <div class="absolute bottom-0 left-0 w-full" :style="{ 
                                    height: overallProgress + '%', 
                                    backgroundColor: '#00BFFF', 
                                    transition: 'height 0.5s ease' 
                                }">
                            </div>

                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-bold text-black">
                                    {{ overallProgress }} %
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-start mt-8 border-b-2 border-gray-200 pb-4 gap-4">
                    <button @click="setActiveTab('qa')"
                        class="tab-button text-black px-4 py-2 rounded-lg text-lg font-semibold" :class="{
                            'bg-lime-800 text-white': activeTab === 'qa',
                            'hover:bg-lime-500': activeTab !== 'qa',
                        }">
                        <i class="fas fa-question"></i> Q&A
                    </button>
                    <button @click="setActiveTab('editor')"
                        class="tab-button text-black px-4 py-2 rounded-lg text-lg font-semibold" :class="{
                            'bg-lime-800 text-white': activeTab === 'editor',
                            'hover:bg-lime-500': activeTab !== 'editor',
                        }">
                        <i class="fas fa-pen"></i> Notes
                    </button>
                    <button @click="setActiveTab('reviews')"
                        class="tab-button text-black px-4 py-2 rounded-lg text-lg font-semibold" :class="{
                            'bg-lime-800 text-white': activeTab === 'reviews',
                            'hover:bg-lime-500': activeTab !== 'reviews',
                        }">
                        <i class="fas fa-star"></i> Reviews
                    </button>
                </div>

                <!-- Tab Content -->
                <div v-if="activeTab === 'qa'" class="pt-4">
                    <QA v-if="selectedCourse" :selectedCourse="selectedCourse" />
                </div>
                <div v-if="activeTab === 'editor'" class="mt-4">
                    <div v-if="activeTab === 'editor'" class="mt-4">
                        <TextEditor :selectedLesson="selectedLesson" :selectedCourse="selectedCourse"
                            :openedLesson="openedLesson" />

                    </div>

                </div>
                <div v-if="activeTab === 'reviews'" class="mt-4">
                    <ReviewList :feedBacks="selectedCourse?.feedBacks" :averageRating="selectedCourse?.averageRating"
                        :starDistribution="selectedCourse?.starDistribution" :showOnly="false" />
                </div>
            </div>
        </div>

        <!-- Course List Section (now on the right side) -->
        <div class="sticky top-10 h-fit">
            <CourseList v-if="selectedModules" :selectedModules="selectedModules" @openedLesson="openedLesson"
                @openedQuiz="openedQuiz" @downloadCertificate="handleDownloadCertificate" />
        </div>
    </div>
</template>
