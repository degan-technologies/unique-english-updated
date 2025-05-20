<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { ref, onMounted, onBeforeUnmount } from "vue";
import { UseStudentStore } from "@/store/UseStudentStore";
import CourseCard from "@/components/Course/CourseCard.vue";
import Spinner from "../Layout/Spinner.vue";

const studentStore = UseStudentStore();
const { videoPlayerTab, selectedCourseSlug } = storeToRefs(studentStore);

const myCourses = ref([]);
const isLoading = ref(false);
const router = useRouter();
const isPlaying = ref(false);
const player = ref(null);
const videoPlayer = ref(null);

function getMycourses() {
    isLoading.value = true;
    Axios.get("/api/courses/my-courses")
        .then((res) => {
            myCourses.value = res.data.data;
        })
        .finally(() => {
            isLoading.value = false;
        });
}

function continueLearning(slugValue) {
    router.push({
        name: "student",
        query: {
            tab: videoPlayerTab.value,
            slug: slugValue,
        },
    });
    selectedCourseSlug.value = slugValue;
}

onBeforeUnmount(() => {
    if (player.value) {
        player.value.dispose();
        player.value = null;
    }
});

onMounted(() => {
    getMycourses();
});
</script>

<template>
    <div class="min-h-screen overflow-y-auto bg-gray-50">
        <main class="py-6">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">
                <!-- Spinner centered -->
                <div v-if="isLoading" class="flex items-center justify-center h-[60vh]">
                    <Spinner />
                </div>

                <!-- No courses found -->
                <div v-else-if="myCourses?.length === 0" class="text-center text-gray-500 py-12">
                    No purchased course found.
                </div>

                <!-- Course list -->
                <div v-else class="flex flex-col gap-6">
                    <div v-for="myCourse in myCourses" :key="myCourse.id"
                        class="bg-white border border-gray-200 shadow-md rounded-xl overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <!-- Video section -->
                            <div class="w-full md:w-1/2">
                                <div class="relative w-full h-56 md:h-64">
                                    <video ref="videoPlayer" id="videoPlayer"
                                        class="video-js vjs-default-skin w-full h-full object-cover rounded-t-sm md:rounded-none md:rounded-l-sm"
                                        controls :poster="myCourse?.thumbnail_url" preload="auto">
                                        <source :src="myCourse?.intro_video_url" type="video/mp4" />
                                        Your browser does not support the video
                                        tag.
                                    </video>
                                </div>
                            </div>

                            <!-- Course info -->
                            <div class="w-full md:w-1/2 p-6 flex flex-col justify-center">
                                <h2 class="text-xl font-semibold mb-2 text-gray-800">
                                    {{ myCourse?.course_name }}
                                </h2>

                                <!-- Progress Bar -->
                                <div class="flex items-center gap-2 mt-4">
                                    <div class="flex-grow bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-lime-600 h-2.5 rounded-full" :style="{
                                            width: `${(myCourse.progress[0] / myCourse.progress[1] * 100)}%`,
                                        }"></div>
                                    </div>
                                    <span class="text-sm text-gray-700 font-medium">
                                        {{ myCourse.progress[0] }} / {{ myCourse.progress[1] }}
                                    </span>
                                </div>

                                <!-- Continue button -->
                                <button @click="continueLearning(myCourse.slug)"
                                    class="mt-6 px-4 py-2 w-full sm:w-32 border border-lime-700 bg-white text-lime-700 font-semibold text-sm rounded-md hover:bg-lime-700 hover:text-white transition-all duration-200">
                                    Continue
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
                <!-- Additional component -->
                <div class="mt-12 w-full mx-auto">
                    <CourseCard />
                </div>
    </div>
</template>

<style scoped>
html,
body {
    overflow-y: auto;
    height: auto;
}

.line-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
