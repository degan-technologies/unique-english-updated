<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { onMounted, ref, watch, onBeforeUnmount, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import videojs from 'video.js';
import 'video.js/dist/video-js.css';

import { UseStudentStore } from "@/store/UseStudentStore";

import ReviewList from "@/components/Course/ReviewList.vue";

const studentStore = UseStudentStore();
const { videoPlayerTab, courses, selectedCourseSlug } = storeToRefs(studentStore);
const route = useRoute();
const router = useRouter();

const checkoutUrl = ref(null);
const selectedCourse = ref(null);
const collapsModuleId = ref(null);
const isPlaying = ref(false);

selectedCourseSlug.value = route.query.slug;

function toggleModuleLesson(id) {
    if (collapsModuleId.value === id) {
        collapsModuleId.value = null;
    } else {
        collapsModuleId.value = id;
    }
}

function enrollCourse(item) {

    if (item.isMyCourse) {
        router.push({
            name: "student",
            query: { tab: videoPlayerTab.value, slug: item.slug },
        });
        selectedCourseSlug.value = slug;
        return;
    }

    const selectedItem = [{ type: "course", slug: item.slug }];
    Axios
        .post("/api/initiate-payment", { cartItems: selectedItem })
        .then((res) => {
            checkoutUrl.value = res.data.checkout_url;
            window.open(checkoutUrl.value, "_blank");
        })
        .catch((error) => {
            console.error("Payment initiation error:", error);
        });
}

async function handleCertificateClick() {
    try {
        // Call the new API endpoint to get certificate status.
        const response = await Axios.get(`/api/courses/${selectedCourse.value.id}/certificate-status`);
        const { certificate_active, allContentsCompleted, allQuizzesCompleted } = response.data;

        if (!certificate_active) {
            // You might want to notify the user with a message.
            alert("Certificate is not available yet. Please complete all course content and quizzes.");
            return;
        }

        if (window.innerWidth < 768) {
            if (certificateComponentRef.value && certificateComponentRef.value.downloadCertificate) {
                certificateComponentRef.value.downloadCertificate();
            }
        } else {
            // Desktop view:
            showCertificateModal.value = true;
        }
    } catch (error) {
        console.error("Error checking certificate status:", error);
    }
}

onMounted(async () => {
    await studentStore.fetchCourses();
    selectedCourse.value = courses.value.find(
        (item) => item.slug === selectedCourseSlug.value
    );
});

watch(
    () => route.query.slug,
    () => {
        selectedCourseSlug.value = route.query.slug;
        selectedCourse.value = courses.value.find(
            (course) => (course.slug = selectedCourseSlug.value)
        );
    }
);

const currentTime = ref(0);
const duration = ref(0);
const bufferProgress = ref(0);
const videoPlayer = ref(null);
const player = ref(null);
const videoSource = ref('');

const onTimeUpdate = () => {
    if (player.value) {
        currentTime.value = player.value.currentTime();
        console.log("Current time updated:", currentTime.value);
    }
};

const onLoadedMetadata = () => {
    if (player.value) {
        duration.value = player.value.duration();
        console.log("Video metadata loaded. Duration:", duration.value);
    }
};

const onProgress = () => {
    if (player.value && duration.value > 0) {
        const buffered = player.value.buffered();
        if (buffered.length) {
            const bufferEnd = buffered.end(0);
            bufferProgress.value = (bufferEnd / duration.value) * 100;
            console.log("Buffer progress:", bufferProgress.value);
        }
    }
};

const playVideo = () => {
    if (isPlaying.value) {
        console.log("Video is already playing.");
        return;
    }
    isPlaying.value = true;

    videoSource.value = selectedCourse.value.intro_video_url;
    console.log("Selected video source:", videoSource.value);
    if (!videoSource.value || videoSource.value.trim() === '') {
        console.error("Invalid video source!");
        return;
    }
    nextTick(() => {
        if (player.value) {
            console.log("Disposing existing player...");
            player.value.dispose();
            player.value = null;
        }
        console.log("Initializing the video player...");
        player.value = videojs(videoPlayer.value, {
            controls: true,
            autoplay: true,
            preload: 'auto',
            fluid: true,
        });

        player.value.src({
            src: videoSource.value,
            type: 'video/mp4',
        });
        console.log("Video source set successfully.");
        player.value.on('timeupdate', onTimeUpdate);
        player.value.on('loadedmetadata', onLoadedMetadata);
        player.value.on('progress', onProgress);
        player.value.on('error', (e) => {
            console.error("Video player error:", e);
        });
        player.value.on('seeked', () => {
            console.log("Video seeked to:", player.value.currentTime());
        });
    });
};

const seekVideo = (event) => {
    const seekTime = parseFloat(event.target.value);
    if (isNaN(seekTime)) {
        console.error("Invalid seek time:", event.target.value);
        return;
    }
    console.log("Attempting to seek to:", seekTime);
    if (player.value && duration.value > 0 && seekTime >= 0 && seekTime <= duration.value) {
        player.value.currentTime(seekTime);
        console.log("After seeking, current time is:", player.value.currentTime());
    } else {
        console.error("Seek time out of bounds:", seekTime, "Duration:", duration.value);
    }
};

onBeforeUnmount(() => {
    if (player.value) {
        player.value.dispose();
        player.value = null;
    }
});
</script>

<template>
    <div v-if="selectedCourseSlug" class="p-6 mt-24 pb-16 rounded-lg bg-slate-50">
        <div class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative">
            <div class="">
                <div class="text-left">
                    <h1 class="text-4xl text-gray-900 font-bold">
                        {{ selectedCourse?.course_name }}
                    </h1>
                    <div class="flex my-2 gap-4">
                        <div>
                            <img :src="selectedCourse?.thumbnail_url" :alt="selectedCourse?.user.first_name"
                                class="w-12 h-12 object-cover mt-4 rounded-full" />
                        </div>
                        <div class="text-lg text-gray-700 mt-2">
                            <p>A course by</p>
                            <p class="font-bold">
                                {{ selectedCourse?.user.first_name }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="w-full p-6 flex flex-col items-start relative">
                    <div class="overflow-hidden w-full aspect-video rounded-t-lg mt-3 relative">
                        <!-- Thumbnail with play icon -->
                        <div v-if="!isPlaying" class="relative w-full h-full cursor-pointer" @click="playVideo">
                            <img :src="selectedCourse?.thumbnail_url" alt="Course Thumbnail"
                                class="w-full h-full object-cover transition-transform duration-300 rounded-t-lg shadow-lg hover:shadow-xl" />
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="p-4 bg-lime-500 rounded-full animate-breathe flex items-center justify-center">
                                    <i class="fas fa-play-circle text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <!-- Video Player with Video.js -->
                        <div v-else class="relative w-full h-full">
                            <video ref="videoPlayer" id="videoPlayer"
                                class="video-js vjs-default-skin w-full h-full rounded-t-lg shadow-md border" controls
                                preload="auto" @timeupdate="onTimeUpdate" @loadedmetadata="onLoadedMetadata"
                                @progress="onProgress">
                                <source :src="videoSource" type="video/mp4" />
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-4 py-8 rounded-b-lg">
                    <div class="text-left mb-6">
                        <div class="flex items-left justify-between">
                            <h2 class="text-3xl text-slate-600 font-semibold">
                                Course Overview
                            </h2>
                        </div>
                        <p class="py-4 text-lg leading-9">
                            {{ selectedCourse?.overview }}
                        </p>
                        <div class="flex items-left justify-between">
                            <h2 class="text-3xl text-slate-600 font-semibold">
                                What you will learn
                            </h2>
                        </div>
                    </div>
                    <div v-for="(
courseModule, courseModuleIndex
                        ) in selectedCourse?.courseModules" :key="courseModuleIndex" class="mb-3 px-2">
                        <div class="flex justify-between items-center">
                            <button @click="toggleModuleLesson(courseModule.id)"
                                class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300">
                                <span class="font-semibold text-lg">{{
                                    courseModule.title
                                }}</span>
                                <i :class="[
                                    {
                                        'rotate-90':
                                            collapsModuleId ==
                                            courseModule.id,
                                    },
                                ]" class="fa-solid fa-angle-right text-xl transition-transform duration-300">
                                </i>
                            </button>
                        </div>

                        <div v-if="collapsModuleId == courseModule.id" class="ml-4 mt-2">
                            <ul class="list-none pl-0">
                                <li v-for="(courseContent, courseContentIndex) in courseModule.courseContents"
                                    :key="courseContentIndex"
                                    class="text-gray-700 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1">
                                    <i :class="{
                                        'fa-circle-play': courseContent.content_type == 1,
                                        'fa-file-lines': courseContent.content_type == 2,
                                        'fa-image': courseContent.content_type == 3
                                    }" class="fa-solid px-8 text-lg w-5 h-5"></i>
                                    {{ courseContent.title }}
                                </li>

                                <li v-for="(qMetaData, qMetaDataIndex) in courseModule.QMetaDatas"
                                    :key="'qMetaData-' + qMetaDataIndex"
                                    class="text-gray-600 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1 ">
                                    <i class="fa-solid fa-clipboard-list px-8 text-lg w-5 h-5"></i>
                                    <span class="font-semibold">Quiz {{ qMetaDataIndex + 1 }} - </span>
                                    <span class="ml-2">{{ qMetaData.title }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <ReviewList v-if="selectedCourse" :feedBacks="selectedCourse?.feedBacks"
                    :averageRating="selectedCourse?.averageRating" :starDistribution="selectedCourse?.starDistribution"
                    :showOnly="true" />
            </div>

            <!-- Right Card: Course Details -->
            <div class="bg-gray-100 p-6 rounded-lg shadow-lg sticky top-0 h-fit justify-center">
                <button v-if="selectedCourse" @click="enrollCourse(selectedCourse)"
                    class="bg-lime-600 text-white px-6 py-2 rounded-lg mb-4 hover:bg-lime-700 transition-colors w-full">
                    {{ selectedCourse?.isMyCourse ? 'continue' : 'Enolle Course' }}
                </button>
                <h2 class="text-xl leading-9 font-semibold mb-4">
                    Course Details
                </h2>
                <div class="flex flex-col gap-4">
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-user-graduate text-blue-500 mr-2"></i>
                        <strong>Level:</strong>
                        {{ selectedCourse?.skill_level }}
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-language text-green-500 mr-2"></i>
                        <strong>Language:</strong>
                        {{ selectedCourse?.language }}
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-clock text-yellow-500 mr-2"></i>
                        <strong>Duration:</strong>
                        {{ selectedCourse?.credit_hour }}
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-tasks text-red-500 mr-2"></i>
                        <strong>Activities:</strong>
                        {{selectedCourse?.courseModules?.reduce((total, module) =>
                            total +
                            (module?.courseContents?.length || 0) +
                            (module?.QMetaDatas?.length || 0)
                            , 0)}}
                    </div>

                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-tv text-purple-500 mr-2"></i>
                        <strong>Access on:</strong> Mobile, Desktop, and TV
                    </div>
                    <div class="leading-relaxed text-lg py-1">
                        <i class="fas fa-users text-indigo-500 mr-2"></i>
                        <strong>Lifetime access to the community</strong>
                    </div>
                    <a class="text-blue-500 hover:underline leading-relaxed text-lg py-2">
                        <i class="fas fa-certificate text-teal-500 mr-2"></i>
                        <strong>Certificate of completion</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bg-green-500:hover,
.bg-blue-500:hover,
.bg-gray-500:hover {
    transition: background-color 0.3s ease;
}

.bg-green-500 {
    background-color: #38a169;
}

.bg-blue-500 {
    background-color: #3182ce;
}

.bg-gray-500 {
    background-color: #6b7280;
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
</style>
