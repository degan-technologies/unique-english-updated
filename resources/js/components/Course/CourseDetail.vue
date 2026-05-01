<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import "video.js/dist/video-js.css";
import { useRoute, useRouter } from "vue-router";
import { onMounted, ref, watch, onBeforeUnmount, nextTick } from "vue";

import { useAuthStore } from "@/store/useAuthStore";
import { UseStudentStore } from "@/store/UseStudentStore";
import { useSessionStore } from "@/store/useSessionStore";

import Spinner from "@/components/Layout/Spinner.vue";
import ReviewList from "@/components/Course/ReviewList.vue";
import VueVideoPlayer from "@/components/Course/VueVideoPlayer.vue";


const sessionStore = useSessionStore();
const AuthStore = useAuthStore();
const studentStore = UseStudentStore();

const { isLoggedIn } = storeToRefs(sessionStore);
const { showLoginForm } = storeToRefs(AuthStore);
const { videoPlayerTab, courses, selectedCourseSlug } = storeToRefs(studentStore);
const route = useRoute();
const router = useRouter();

const checkoutUrl = ref(null);
const selectedCourse = ref(null);
const collapsModuleId = ref(null);
const isLoading = ref(false);
const startLoading = ref(false); 
 
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
    if (!selectedCourse.value && (!courses.value || courses.value.length === 0)) {
        await studentStore.fetchCourses();
    }
    if (courses.value?.length) {
        selectedCourse.value = courses.value.find(
            (item) => item.slug === selectedCourseSlug.value
        );
    }
    startLoading.value = false;
});

watch(() => route.query.slug, (newSlug) => {
    if (selectedCourseSlug.value !== newSlug) {
        selectedCourseSlug.value = newSlug;
        selectedCourse.value = courses.value?.find(c => c.slug === newSlug) || null;
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
                        <div class="text-gray-700 mt-2">
                            <p class="text-sm">A course by</p>
                            <p class="font-bold">
                                {{ selectedCourse?.user.first_name }} {{ selectedCourse?.user.middle_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Video Player Section -->
                <div class="w-full aspect-video rounded-lg mt-3 bg-black relative overflow-hidden">
                    <template v-if="selectedCourse?.intro_video_url">
                        <VueVideoPlayer 
                            :videoSource="selectedCourse?.intro_video_url" 
                            :posterImage="selectedCourse?.thumbnail_url"/>
                    </template> 
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

                        <transition name="fade-slide" mode="out-in" >
                            <div v-if="collapsModuleId === courseModule.id" class="ml-2 mt-2">
                                <ul class="list-none pl-0">
                                    <li v-for="(courseContent, courseContentIndex) in courseModule.courseContents"
                                        :key="courseContentIndex"
                                        class="text-gray-700 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1 hover:bg-gray-50 rounded px-2">
                                        <i :class="{
                                            'fa-circle-play text-blue-500': courseContent.content_type === 1,
                                            'fa-file-lines text-green-500': courseContent.content_type === 2,
                                            'fa-image text-purple-500': courseContent.content_type === 3,
                                        }" class="fa-solid  pr-8 text-lg w-5 h-5"></i>
                                        {{ courseContent.title }}
                                        <span v-if="courseContent.duration" class="ml-auto text-sm text-gray-500">
                                            {{ courseContent.duration }}
                                        </span>
                                    </li>

                                    <li v-for="(qMetaData, qMetaDataIndex) in courseModule.QMetaDatas"
                                        :key="'qMetaData-' + qMetaDataIndex"
                                        class="text-gray-600 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1 hover:bg-gray-50 rounded px-2">
                                        <i class="fa-solid fa-clipboard-list  pr-8 text-lg w-5 h-5 text-yellow-500"></i>
                                        <span class="font-semibold">Quiz {{ qMetaDataIndex + 1 }} - </span>
                                        <span class="ml-2">{{ qMetaData.title }}</span> 
                                    </li>
                                </ul>
                            </div>
                        </transition>
                    </div>
                </div>

                <!-- Reviews -->
                <ReviewList v-if="selectedCourse" :feedBacks="selectedCourse?.feedBacks"
                    :courseSlug ="selectedCourseSlug"
                    :addFeedbackType="'course'"
                    :showOnly="true" />
            </div>

            <!-- Right Column: Course Details -->
            <div class="bg-gray-100 p-6 rounded-lg shadow-lg sticky top-24 h-fit">
                <div class="mb-6"> 
                    <button v-if="selectedCourse" @click="enrollCourse(selectedCourse)" :disabled="isLoading"
                        class="bg-lime-600 text-white px-6 py-3 rounded-lg hover:bg-lime-700 transition-colors w-full flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
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
                </div> 

                <div class="border-t border-gray-300 pt-4 mt-4"> 
                    <div class="flex flex-col gap-4">
                        <div class="leading-relaxed text-lg py-1 flex items-center">
                            <i class="fas fa-user-graduate text-blue-500 mr-3 w-5 text-center"></i>
                            <div class="flex self-center flex-row gap-4">
                                <strong class="block text-sm text-gray-500">Level</strong>
                                <span class="text-sm">{{ selectedCourse?.skill_level }}</span>
                            </div>
                        </div>
                        <div class="leading-relaxed text-lg py-1 flex items-center">
                            <i class="fas fa-language text-green-500 mr-3 w-5 text-center"></i>
                            <div class="flex flex-row gap-4">
                                <strong class="block text-sm text-gray-500">Language</strong>
                                <span class="text-sm">{{ selectedCourse?.language }}</span>
                            </div>
                        </div>
                        <div class="leading-relaxed text-lg py-1 flex items-center">
                            <i class="fas fa-clock text-yellow-500 mr-3 w-5 text-center"></i>
                            <div class="flex flex-row gap-4">
                                <strong class="block text-sm text-gray-500">Duration</strong>
                                <span class="text-sm">{{ selectedCourse?.credit_hour }} hours </span>
                            </div>
                        </div>
                        <div class="leading-relaxed text-lg py-1 flex items-center">
                            <i class="fas fa-tasks text-red-500 mr-3 w-5 text-center"></i>
                            <div class="flex flex-row gap-4">
                                <strong class="block text-sm text-gray-500">Activities</strong>
                                <span class="text-sm">{{
                                    selectedCourse?.courseModules?.reduce(
                                        (total, module) =>
                                            total +
                                            (module?.courseContents?.length || 0) +
                                            (module?.QMetaDatas?.length || 0),
                                        0
                                    )
                                }} lessons </span>
                            </div>
                        </div>
                    </div>
                </div> 
                <div > 
                    <ul class="space-y-3"> 
                        <li class="flex items-start">
                            <i class="fas fa-mobile-alt text-purple-500 mt-1 mr-3"></i>
                            <span>Access on mobile and TV</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-infinity text-red-500 mt-1 mr-3"></i>
                            <span>Full lifetime access</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-trophy text-yellow-500 mt-1 mr-3"></i>
                            <span>Certificate of completion</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>