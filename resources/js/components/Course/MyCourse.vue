<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { ref, onMounted, onBeforeUnmount } from "vue";

import { UseStudentStore } from "@/store/UseStudentStore";
import CourseCard from "@/components/Course/CourseCard.vue";

const studentStore = UseStudentStore();
const { videoPlayerTab, selectedCourseSlug } = storeToRefs(studentStore);

const myCourses = ref([]);
const router = useRouter();
const isPlaying = ref(false);
const player = ref(null);
const videoPlayer = ref(null);

function getMycourses() {
    Axios.get("/api/courses/my-courses").then((res) => {
        myCourses.value = res.data.data;
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
    <div class="min-h-screen overflow-y-auto">
        <main class="pt-10">
            <div class="container mx-auto p-8 max-w-6xl">
                <div
                    v-if="myCourses?.length === 0"
                    class="text-center text-gray-500"
                >
                    No purchased Course found.
                </div>
                <div v-else class="flex flex-col md:flex-row gap-4">
                    <div class="md:w-2/3 space-y-6">
                        <div
                            v-for="myCourse in myCourses"
                            :key="myCourse.id"
                            class="bg-white border border-gray-300 shadow-lg rounded-lg h-fit overflow-hidden p-4"
                        >
                            <div class="flex flex-col md:flex-row">
                                <div class="md:w-1/2">
                                    <div class="relative w-full h-56">
                                        <video
                                            ref="videoPlayer"
                                            id="videoPlayer"
                                            class="video-js vjs-default-skin w-full h-full rounded-t-lg shadow-md border"
                                            controls
                                            :poster="myCourse?.thumbnail_url"
                                            preload="auto"
                                        >
                                            <source
                                                :src="myCourse?.intro_video_url"
                                                type="video/mp4"
                                            />
                                            Your browser does not support the
                                            video tag.
                                        </video>
                                    </div>
                                </div>
                                <div
                                    class="md:w-1/2 p-4 flex flex-col justify-center"
                                >
                                    <h2 class="text-xl font-semibold pb-2">
                                        {{ myCourse?.course_name }}
                                    </h2>

                                    <!-- Progress Bar -->
                                    <div
                                        class="flex flex-row items-center gap-2 mt-8"
                                    >
                                        <div
                                            class="w-[90%] bg-gray-200 rounded-full h-2.5"
                                        >
                                            <div
                                                class="bg-green-500 h-2.5 rounded-full"
                                                :style="{
                                                    width: `${myCourse.progress}%`,
                                                }"
                                            ></div>
                                        </div>
                                        <span
                                            class="text-sm font-medium text-gray-700 inline-flex"
                                            >{{ myCourse.progress }} %</span
                                        >
                                    </div>
                                    <button
                                        @click="continueLearning(myCourse.slug)"
                                        class="mt-9 px-3 py-2 w-28 border border-lime-700 bg-white text-lime-600 font-semibold text-sm rounded-md hover:bg-lime-700 hover:text-white transition-colors"
                                    >
                                        Continue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Optional extra component below -->
            <div class="mt-8 w-full">
                <CourseCard />
            </div>
        </main>
    </div>
</template>

<style>
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
