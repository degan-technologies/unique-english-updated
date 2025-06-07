<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { onMounted, ref, watch, onBeforeUnmount, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import videojs from "video.js";
import "video.js/dist/video-js.css";

import { useAuthStore } from "@/store/useAuthStore";
import { UseStudentStore } from "@/store/UseStudentStore";
import { useAppStore } from "@/store/useAppStore";

import ReviewList from "@/components/Course/ReviewList.vue";
import Spinner from "@/components/Layout/Spinner.vue";

const appStore = useAppStore();
const AuthStore = useAuthStore();
const studentStore = UseStudentStore();

const { isLoggedIn } = storeToRefs(appStore);
const { showLoginForm } = storeToRefs(AuthStore);
const { videoPlayerTab, courses, selectedCourseSlug } = storeToRefs(studentStore);
const route = useRoute();
const router = useRouter();

const checkoutUrl = ref(null);
const selectedCourse = ref(null);
const collapsModuleId = ref(null);
const isPlaying = ref(false);
const isLoading = ref(false);
const startLoading = ref(false);

// Video player related refs
const currentTime = ref(0);
const duration = ref(0);
const bufferProgress = ref(0);
const videoPlayer = ref(null);
const player = ref(null);
const videoSource = ref("");
selectedCourseSlug.value = route.query.slug;

function toggleModuleLesson(id) {
    collapsModuleId.value = collapsModuleId.value === id ? null : id;
}

async function enrollCourse(item) {
    if (!isLoggedIn.value) {
        showLoginForm.value = true;
        return;
    }

    isLoading.value = true;

    if (item.isMyCourse) {
        selectedCourseSlug.value = item.slug;
        try {
            await router.push({
                name: "student",
                query: { tab: videoPlayerTab.value, slug: item.slug },
            });
        } finally {
            isLoading.value = false;
        }
        return;
    }

    try {
        const selectedItem = [{ type: "course", slug: item.slug }];
        const res = await Axios.post("/api/initiate-payment", { cartItems: selectedItem });
        checkoutUrl.value = res.data.checkout_url;
        window.open(checkoutUrl.value, "_blank");
    } catch (error) {
        console.error("Payment error:", error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(async () => {
    startLoading.value = true;
    if (!selectedCourse.value && !courses.value) {
        await studentStore.fetchCourses();
    }
    if (courses.value?.length) {
        selectedCourse.value = courses.value.find(
            (item) => item.slug === selectedCourseSlug.value
        );
    }
    startLoading.value = false;
});

watch(
    () => route.query.slug,
    () => {
        selectedCourseSlug.value = route.query.slug;
        selectedCourse.value = courses.value?.find(
            (course) => course.slug === selectedCourseSlug.value
        ) || null;
    }
);

// Video player functions
const onTimeUpdate = () => {
    if (player.value) {
        currentTime.value = player.value.currentTime();
    }
};

const onLoadedMetadata = () => {
    if (player.value) {
        duration.value = player.value.duration();
    }
};

const onProgress = () => {
    if (player.value && duration.value > 0) {
        const buffered = player.value.buffered();
        if (buffered.length) {
            bufferProgress.value = (buffered.end(0) / duration.value) * 100;
        }
    }
};

const playVideo = () => {
    if (isPlaying.value || !selectedCourse.value?.intro_video_url?.trim()) return;

    isPlaying.value = true;
    videoSource.value = selectedCourse.value.intro_video_url;

    nextTick(() => {
        if (player.value) {
            player.value.dispose();
        }

        player.value = videojs(videoPlayer.value, {
            controls: true,
            autoplay: true,
            preload: "auto",
            fluid: true,
            aspectRatio: "16:9",
            responsive: true
        });

        player.value.src({
            src: videoSource.value,
            type: "video/mp4"
        });

        player.value.on("timeupdate", onTimeUpdate);
        player.value.on("loadedmetadata", onLoadedMetadata);
        player.value.on("progress", onProgress);
        player.value.on("error", (error) => {
            console.error("Video player error:", error);
        });
    });
};

onBeforeUnmount(() => {
    if (player.value) {
        player.value.dispose();
        player.value = null;
    }
});
</script>

<template>
    <div v-if="!selectedCourse">
        <Spinner />
    </div>
    <div v-else class="p-6 mt-24 pb-16 rounded-lg bg-slate-50">
        <div class="grid lg:w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
            <!-- Left Column -->
            <div>
                <div class="text-left">
                    <h2 class="text-3xl text-gray-900 font-bold">
                        {{ selectedCourse?.course_name }}
                    </h2>
                    <div class="flex my-2 gap-4">
                        <div>
                            <img :src="selectedCourse?.user.profile" :alt="selectedCourse?.user.first_name"
                                class="w-12 h-12 object-cover mt-4 rounded-full" />
                        </div>
                        <div class="text-lg text-gray-700 mt-2">
                            <p>A course by</p>
                            <p class="font-bold">
                                {{ selectedCourse?.user.first_name }} {{ selectedCourse?.user.middle_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Video Player Section -->
                <div class="w-full aspect-video rounded-lg mt-3 bg-black relative overflow-hidden">
                    <!-- Thumbnail with play button -->
                    <div v-if="!isPlaying" class="w-full h-full cursor-pointer relative" @click="playVideo">
                        <img :src="selectedCourse?.thumbnail_url" alt="Course Thumbnail"
                            class="w-full h-full object-cover transition-opacity duration-300 hover:opacity-90" />

                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30">
                            <div class="relative">
                                <span class="animate-pulse-circle absolute inset-0"></span>
                                <div
                                    class="relative z-10 p-4 w-14 h-14 bg-lime-500 rounded-full flex items-center justify-center shadow-lg hover:bg-lime-600 transition-colors">
                                    <i class="fas fa-play text-white text-2xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Player -->
                    <div v-else class="w-full h-full">
                        <video ref="videoPlayer" class="video-js vjs-default-skin w-full h-full" playsinline>
                            <source :src="videoSource" type="video/mp4" />
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>

                <!-- Course Content -->
                <div class="bg-white pt-2 rounded-b-lg mt-4">
                    <div class="text-left mb-6">
                        <h2 class="text-3xl text-slate-600 font-semibold mb-3">
                            Course Overview
                        </h2>
                        <div class="preview ql-editor max-w-full text-justify mt-5" v-html="selectedCourse?.overview"
                            style="font-size: 1.1rem !important; line-height: 1.75rem !important; all: revert;">
                        </div>
                        <h2 class="text-3xl text-slate-600 font-semibold mt-5">
                            What you will learn
                        </h2>
                    </div>

                    <!-- Modules and Lessons -->
                    <div v-for="(courseModule, courseModuleIndex) in selectedCourse?.courseModules"
                        :key="courseModuleIndex" class="mb-3 px-2">
                        <div class="flex justify-between items-center">
                            <button @click="toggleModuleLesson(courseModule.id)"
                                class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300">
                                <span class="font-semibold text-lg">{{ courseModule.title }}</span>
                                <i :class="{ 'rotate-90': collapsModuleId === courseModule.id }"
                                    class="fa-solid fa-angle-right text-xl transition-transform duration-300">
                                </i>
                            </button>
                        </div>

                        <div v-if="collapsModuleId === courseModule.id" class="ml-4 mt-2">
                            <ul class="list-none pl-0">
                                <li v-for="(courseContent, courseContentIndex) in courseModule.courseContents"
                                    :key="courseContentIndex"
                                    class="text-gray-700 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1 hover:bg-gray-50 rounded px-2">
                                    <i :class="{
                                        'fa-circle-play': courseContent.content_type === 1,
                                        'fa-file-lines': courseContent.content_type === 2,
                                        'fa-image': courseContent.content_type === 3,
                                    }" class="fa-solid px-8 text-lg w-5 h-5"></i>
                                    {{ courseContent.title }}
                                </li>

                                <li v-for="(qMetaData, qMetaDataIndex) in courseModule.QMetaDatas"
                                    :key="'qMetaData-' + qMetaDataIndex"
                                    class="text-gray-600 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1 hover:bg-gray-50 rounded px-2">
                                    <i class="fa-solid fa-clipboard-list px-8 text-lg w-5 h-5"></i>
                                    <span class="font-semibold">Quiz {{ qMetaDataIndex + 1 }} - </span>
                                    <span class="ml-2">{{ qMetaData.title }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Reviews -->
                <ReviewList v-if="selectedCourse" :feedBacks="selectedCourse?.feedBacks"
                    :averageRating="selectedCourse?.averageRating" :starDistribution="selectedCourse?.starDistribution"
                    :showOnly="true" />
            </div>

            <!-- Right Column: Course Details -->
            <div class="bg-gray-100 p-6 rounded-lg shadow-lg sticky top-0 h-fit">
                <button v-if="selectedCourse" @click="enrollCourse(selectedCourse)" :disabled="isLoading"
                    class="bg-lime-600 text-white px-6 py-3 rounded-lg mb-4 hover:bg-lime-700 transition-colors w-full flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-base font-medium">
                        {{
                            selectedCourse?.isMyCourse
                                ? isLoading
                                    ? "Loading..."
                                    : "Continue Learning"
                                : isLoading
                                    ? "Processing..."
                                    : "Enroll Course"
                        }}
                    </span>
                </button>

                <h2 class="text-xl leading-9 font-semibold mb-4">
                    Course Details
                </h2>
                <div class="flex flex-col gap-4">
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-user-graduate text-blue-500 mr-3 w-5 text-center"></i>
                        <div class="flex flex-row gap-4">
                            <strong class="block text-sm text-gray-500">Level</strong>
                            {{ selectedCourse?.skill_level }}
                        </div>
                    </div>
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-language text-green-500 mr-3 w-5 text-center"></i>
                        <div class="flex flex-row gap-4">
                            <strong class="block text-sm text-gray-500">Language</strong>
                            {{ selectedCourse?.language }}
                        </div>
                    </div>
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-clock text-yellow-500 mr-3 w-5 text-center"></i>
                        <div class="flex flex-row gap-4">
                            <strong class="block text-sm text-gray-500">Duration</strong>
                            {{ selectedCourse?.credit_hour }} hours
                        </div>
                    </div>
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-tasks text-red-500 mr-3 w-5 text-center"></i>
                        <div class="flex flex-row gap-4">
                            <strong class="block text-sm text-gray-500">Activities</strong>
                            {{
                                selectedCourse?.courseModules?.reduce(
                                    (total, module) =>
                                        total +
                                        (module?.courseContents?.length || 0) +
                                        (module?.QMetaDatas?.length || 0),
                                    0
                                )
                            }} lessons
                        </div>
                    </div>

                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-tv text-purple-500 mr-3 w-5 text-center"></i>
                        <div class="flex flex-row gap-4">
                            <strong class="block text-sm text-gray-500">Access</strong>
                            Mobile, Desktop, and TV
                        </div>
                    </div>
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-users text-indigo-500 mr-3 w-5 text-center"></i>
                        <div class="flex flex-row gap-4">
                            <strong class="block text-sm text-gray-500">Community</strong>
                            Lifetime access
                        </div>
                    </div>
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-certificate text-teal-500 mr-3 w-5 text-center"></i>
                        <div class="flex flex-row gap-4">
                            <strong class="block text-sm text-gray-500">Certificate</strong>
                            Included
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.video-js {
    width: 100%;
    height: 100%;
    border-radius: 0.5rem;
}

.video-js .vjs-big-play-button {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 68px;
    height: 68px;
    border-radius: 50%;
    border: none;
    background-color: rgba(101, 163, 13, 0.8);
}

.video-js .vjs-big-play-button:hover {
    background-color: rgba(101, 163, 13, 1);
}

@keyframes pulse-circle {
    0% {
        transform: scale(1);
        opacity: 0.7;
        box-shadow: 0 0 5px rgba(132, 204, 22, 0.6);
    }

    50% {
        transform: scale(1.6);
        opacity: 0.4;
        box-shadow: 0 0 20px rgba(91, 150, 9, 0.8);
    }

    100% {
        transform: scale(2);
        opacity: 0;
        box-shadow: 0 0 30px rgba(72, 118, 12, 0.5);
    }
}

.animate-pulse-circle {
    width: 70px;
    height: 70px;
    background-color: rgba(132, 204, 22, 0.5);
    border-radius: 50%;
    animation: pulse-circle 2s infinite ease-out;
    position: absolute;
}

.rotate-90 {
    transform: rotate(90deg);
}
</style>