<script setup>
import Axios from "axios";
import videojs from 'video.js';
import Popper from "vue3-popper";
import 'video.js/dist/video-js.css';
import { storeToRefs } from "pinia";
import { useRouter, useRoute } from "vue-router";
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";

import { useInstructorStore } from '@/store/useInstructorStore';

import QA from "@/components/Exam/QA.vue";
import ReviewList from "@/components/Course/ReviewList.vue";
import MetaDataForm from "@/components/Quize/MetaDataForm.vue";
import AddCourseModule from "@/components/Course/CourseModule/AddCourseModule.vue";
import ModuleContent from "@/components/Course/CourseModule/ModuleContent/ModuleContent.vue";
import AddModuleContent from "@/components/Course/CourseModule/ModuleContent/AddModuleContent.vue";

const InstructorStore = useInstructorStore();
const { selectedCourse, editCourseModule, courseEditTab, courseId, instructorCourses } = storeToRefs(InstructorStore);

const route = useRoute();
const router = useRouter();

// UI State
const isPlaying = ref(false);
const expandedModule = ref(null);
const selectedModule = ref(null);
const actionType = ref('');
const dropdownOpen = ref(null);
const editingCourseId = ref(null);
const selectedContent = ref(null);
const addNewContent = ref(null);
const isLoading = ref(false);
const errorMessage = ref(null);
const isProcessing = ref(false);
const readPdf = ref(false);

// Video Player
const videoPlayer = ref(null);
let playerInstance = null;
const showDeleteModal = ref(false);
const deleteModule = ref(null);

//tab sections
const qaTab = ref("qa");
const reviewTab = ref("review");
const activeTab = ref(qaTab.value);

// Computed
const courseModules = computed(() => selectedCourse.value?.courseModules || []);
const feedBacks = computed(() => selectedCourse.value?.feedBacks);
const averageRating = computed(() => selectedCourse.value?.averageRating);
const starDistribution = computed(() => selectedCourse.value?.starDistribution);

// Methods
function startEditing(module) {
    editCourseModule.value = module;
    actionType.value = 'UPDATE';
};

function openModuleForm(id) {
    courseId.value = id;
    actionType.value = 'STORE';
};

function showDeleteConfirmation(module) {
    deleteModule.value = module;
    showDeleteModal.value = true;
}

async function deleteCourse(id) {
    isLoading.value = true;
    errorMessage.value = null;

    try {
        await Axios.delete(`/api/courses/course/${id}`);
        instructorCourses.value = instructorCourses.value.filter(course => course.id !== id);
        goBack();
    } catch (error) {
        console.error("Failed to delete course:", error);
        errorMessage.value = "Failed to delete course. Please try again.";
    } finally {
        isLoading.value = false;
    }
};

async function deleteCourseModule(moduleId) {
    isProcessing.value = true;
    try {
        await Axios.delete(`/api/courses/module/${moduleId}`);
        if (selectedCourse.value?.courseModules) {
            selectedCourse.value.courseModules = selectedCourse.value.courseModules.filter(
                item => item.id !== moduleId
            );
        }
        showToast("Module deleted successfully", "success");
    } catch (error) {
        console.error("Failed to delete module:", error);
        showToast("Failed to delete module", "error");
    } finally {
        isProcessing.value = false;
        showDeleteModal.value = false;
    }
};

function onAddModuleContent(module) {
    addNewContent.value = module;
};

function actionExpandModule(module) {
    selectedContent.value = null;
    selectedModule.value = null;
    expandedModule.value = expandedModule.value === module.id ? null : module.id;
};

function handleClickOutside(event) {
    if (!event.target.closest(".relative")) {
        dropdownOpen.value = null;
    }
};

function getSelectedCourse(tab, selectedCourse) {
    router.push({
        name: 'instructor',
        query: {
            currentTab: route.query.currentTab,
            currentActiveTab: tab,
            slug: selectedCourse.slug,
            reload: Date.now()
        }
    });

    editingCourseId.value = selectedCourse.id;
    selectedCourse.value = selectedCourse;
};

function goBack() {
    window.history.back();
};

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

function showToast(message, type = "info") {
    console.log(`${type.toUpperCase()}: ${message}`);
}

// Handle image errors
function handleImageError(event, fallbackImage = '/images/default-course-thumbnail.jpg') {
    event.target.src = fallbackImage;
    event.target.onerror = null;
}

onMounted(() => {
    if (!selectedCourse.value) {
        Axios.get(`/api/show-course/${route.query.slug}`)
            .then(res => {
                selectedCourse.value = res.data.data;
            })
            .catch(error => {
                console.error("Failed to fetch course:", error);
                showToast("Failed to load course details", "error");
            });
    }

    document.addEventListener("click", handleClickOutside);

    if (videoPlayer.value) {
        playerInstance = videojs(videoPlayer.value, {
            controls: true,
            autoplay: false,
            responsive: true,
            fluid: true,
            sources: [{
                src: selectedCourse.value?.intro_video_url,
                type: 'video/mp4'
            }]
        });
    }
});

const openPdf = () => {
    readPdf.value = !readPdf.value;
}

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);

    if (playerInstance) {
        playerInstance.dispose();
    }
});
</script>

<template>
    <div class="relative">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Button -->
            <button @click="goBack()"
                class="flex items-center text-gray-600 hover:text-lime-700 transition-colors mb-6 group"
                aria-label="Go back">
                <i class="fas fa-arrow-left text-lg group-hover:-translate-x-1 transition-transform"></i>
                <span class="ml-2 font-medium">Back to Courses</span>
            </button>

            <!-- Error Message -->
            <div v-if="errorMessage"
                class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                <div>
                    <p class="font-medium">Error</p>
                    <p>{{ errorMessage }}</p>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-xl shadow-xl flex items-center space-x-4 animate-pulse">
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-lime-500 border-t-transparent">
                    </div>
                    <div>
                        <p class="font-medium text-gray-700">Processing your request...</p>
                        <p class="text-sm text-gray-500">Please wait a moment</p>
                    </div>
                </div>
            </div>

            <!-- Course Header Section -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
                    <!-- Video Thumbnail/Player -->
                    <div class="relative rounded-lg overflow-hidden bg-gray-100 aspect-video">
                        <div v-if="!isPlaying" class="absolute inset-0 cursor-pointer group" @click="isPlaying = true"
                            role="button" aria-label="Play course introduction video">

                            <img :src="selectedCourse?.thumbnail_url || '/images/default-course-thumbnail.jpg'"
                                :alt="`Thumbnail for ${selectedCourse?.course_name || 'course'}`"
                                @error="handleImageError"
                                class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-90">

                            <div v-if="selectedCourse?.intro_video_url"
                                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-20 group-hover:bg-opacity-30 transition-all">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-lime-500 rounded-full opacity-20 animate-ping">
                                    </div>
                                    <div
                                        class="relative w-16 h-16 bg-lime-500 rounded-full flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                                        <i class="fas fa-play text-white text-xl"></i>
                                    </div>
                                </div>
                            </div>
                            <div v-else
                                class="absolute inset-0 bg-gray-100 flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-video-slash text-4xl mb-2"></i>
                                <p class="text-sm">No video available</p>
                            </div>
                        </div>

                        <div v-else class="h-full w-full bg-black">
                            <video v-if="selectedCourse?.intro_video_url" ref="videoPlayer"
                                class="video-js vjs-default-skin w-full h-full object-contain" controls preload="auto"
                                :poster="selectedCourse.thumbnail_url">
                                <source :src="selectedCourse.intro_video_url" type="video/mp4" />
                                <track kind="captions" src="" srclang="en" label="English" default />
                                <p class="vjs-no-js">
                                    To view this video please enable JavaScript, and consider upgrading to a
                                    web browser that
                                    <a href="https://videojs.com/html5-video-support/" target="_blank">
                                        supports HTML5 video
                                    </a>
                                </p>
                            </video>
                            <div v-else
                                class="h-full w-full flex flex-col items-center justify-center bg-gray-100 text-gray-400">
                                <i class="fas fa-video-slash text-4xl mb-2"></i>
                                <p class="text-sm">No video available</p>
                            </div>
                        </div>
                    </div>

                    <!-- Course Details -->
                    <div class="flex flex-col">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Course Details</h2>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="bg-lime-100 p-2 rounded-lg mr-4">
                                    <i class="fas fa-user-graduate text-lime-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Level</h3>
                                    <p class="text-gray-800 font-medium">{{ selectedCourse?.skill_level || 'Not specified' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="bg-blue-100 p-2 rounded-lg mr-4">
                                    <i class="fas fa-language text-blue-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Language</h3>
                                    <p class="text-gray-800 font-medium">{{ selectedCourse?.language || 'Not specified' }} </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="bg-purple-100 p-2 rounded-lg mr-4">
                                    <i class="fas fa-clock text-purple-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Duration</h3>
                                    <p class="text-gray-800 font-medium">{{ selectedCourse?.credit_hour || 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Course Title and Actions -->
                <div
                    class="border-b border-gray-200 px-6 py-4 w-full flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex-grow">
                        <h1 class="text-2xl font-bold text-gray-900">{{ selectedCourse?.course_name }}</h1>
                        <p v-if="selectedCourse?.courseModules?.length" class="text-sm text-gray-500 mt-1">
                            {{ selectedCourse.courseModules.length }} modules •
                            {{selectedCourse.courseModules.reduce((acc, module) => acc + (module.courseContents?.length
                                ||
                            0), 0)}} lessons
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 w-fit">
                        <button @click="getSelectedCourse(courseEditTab, selectedCourse)"
                            class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-edit mr-2 text-blue-500"></i>
                            <span>Edit Course</span>
                        </button>

                        <button @click="openModuleForm(selectedCourse?.id)"
                            class="flex items-center px-4 py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            <span>Add Module</span>
                        </button>
                    </div>
                </div>

                <!-- Course Overview -->
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Course Overview</h2>
                    <div class="preview ql-editor max-w-full text-justify mt-5" v-html="selectedCourse?.overview" style="
                                font-size: 1.1rem !important;
                                line-height: 1.75rem !important;
                                all: revert; "></div>
                </div>

                <!-- Module Management -->
                <AddCourseModule v-if="editCourseModule" :actionType="actionType" />
                <AddCourseModule v-if="courseId" :actionType="actionType" />
                <!-- Modules List -->
                <div class="p-6">
                    <div v-if="courseModules.length > 0">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-800">Course Modules</h3>
                            <button @click="openModuleForm(selectedCourse?.id)"
                                class="flex items-center px-3 py-1.5 text-sm bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Add Module
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="module in courseModules" :key="module.id"
                                class="bg-white rounded-lg border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">

                                <div class="flex flex-row">
                                    <!-- Module Header -->
                                    <div class="flex items-center flex-grow justify-between p-4 cursor-pointer group"
                                        @click="actionExpandModule(module)"
                                        :aria-expanded="expandedModule === module.id"
                                        :aria-controls="`module-${module.id}-contents`">

                                        <div class="flex items-center flex-1 min-w-0">
                                            <div class="bg-yellow-100 p-2 rounded-lg mr-4 text-yellow-600">
                                                <i class="fas fa-folder text-lg"></i>
                                            </div>

                                            <div class="min-w-0">
                                                <h4 class="text-lg font-semibold text-gray-800 truncate">{{ module.title
                                                    }}
                                                </h4>
                                                <p class="text-sm text-gray-500">
                                                    {{ module.courseContents?.length || 0 }} lessons
                                                </p>
                                            </div>
                                        </div>
                                        <button class="text-gray-400  hover:text-gray-600 transition-colors"
                                            :aria-label="expandedModule === module.id ? 'Collapse module' : 'Expand module'">
                                            <i
                                                :class="expandedModule === module.id ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                                        </button>
                                    </div>

                                    <div class="flex w-fit items-center space-x-2 ">
                                        <Popper>
                                            <i v-if="expandedModule === module.id"
                                                class="fas fa-ellipsis-v text-gray-500 px-4"></i>
                                            <template #content>
                                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-100 py-1"
                                                    role="menu">
                                                    <button @click="startEditing(module)"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                                        role="menuitem">
                                                        <i class="fas fa-edit mr-3 text-blue-500"></i>
                                                        Edit Module
                                                    </button>
                                                    <button @click="onAddModuleContent(module)"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                                        role="menuitem">
                                                        <i class="fas fa-plus mr-3 text-green-500"></i>
                                                        Add Content
                                                    </button>
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <button @click="showDeleteConfirmation(module)"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                                        role="menuitem">
                                                        <i class="fas fa-trash mr-3"></i>
                                                        Delete Module
                                                    </button>
                                                </div>
                                            </template>
                                        </Popper>
                                    </div>
                                </div>

                                <!-- Module Contents -->
                                <div v-if="expandedModule === module.id" class="px-4 pb-4">
                                    <div v-if="module.courseContents?.length"
                                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                        <ModuleContent v-for="content in module.courseContents" :key="content.id"
                                            :selectedContent="content" :selectedModule="null" @openPdf="openPdf" />
                                    </div>

                                    <div v-else
                                        class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg mt-4">
                                        <i class="fas fa-file-alt text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500 mb-4">No content in this module</p>
                                        <button @click="onAddModuleContent(module)"
                                            class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-colors">
                                            <i class="fas fa-plus mr-2"></i>Add First Lesson
                                        </button>
                                    </div>

                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <MetaDataForm :courseID="module?.course_id" :moduleID="module?.id" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg">
                        <i class="fas fa-folder-open text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-500 mb-2">No modules yet</h3>
                        <p class="text-gray-400 mb-6">Start by adding your first module</p>
                        <button @click="openModuleForm(selectedCourse?.id)"
                            class="px-5 py-2.5 bg-lime-600 text-white rounded-lg hover:bg-lime-700 transition-colors font-medium">
                            <i class="fas fa-plus mr-2"></i>Create Module
                        </button>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-t border-gray-200 px-6">
                    <nav class="flex space-x-8">
                        <button @click="setActiveTab(qaTab)"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors" :class="{
                                'border-lime-600 text-lime-600': activeTab === qaTab,
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== qaTab
                            }">
                            <i class="fas fa-question-circle mr-2"></i>
                            Questions & Answers
                        </button>
                        <button @click="setActiveTab(reviewTab)"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors" :class="{
                                'border-lime-600 text-lime-600': activeTab === reviewTab,
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== reviewTab
                            }">
                            <i class="fas fa-star mr-2"></i>
                            Reviews
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <div v-if="activeTab === qaTab">
                        <QA v-if="selectedCourse" :selectedCourseSlug="selectedCourse?.slug"
                            :courseId="selectedCourse?.id" />
                    </div>
                    <div v-if="activeTab === reviewTab">
                        <ReviewList v-if="feedBacks" :feedBacks="feedBacks" :averageRating="averageRating"
                            :starDistribution="starDistribution" :showOnly="false" />
                    </div>
                </div>
            </div>

            <!-- Add Content Form -->
            <AddModuleContent v-if="addNewContent" :selectedModule="addNewContent" @closeModal="addNewContent = null" />

            <!-- Delete Confirmation Dialog -->
            <transition name="fade">
                <div v-if="showDeleteModal && deleteModule"
                    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="bg-red-100 p-2 rounded-full mr-4">
                                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800">Confirm Deletion</h3>
                            </div>

                            <p class="text-gray-600 mb-6">
                                Are you sure you want to delete the module <strong class="text-gray-800">"{{
                                    deleteModule?.title }}"</strong>?
                                This action cannot be undone.
                            </p>

                            <div class="flex justify-end space-x-3">
                                <button @click="showDeleteModal = false"
                                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                    Cancel
                                </button>
                                <button @click="deleteCourseModule(deleteModule?.id)"
                                    class="px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center"
                                    :disabled="isProcessing">
                                    <span v-if="isProcessing" class="flex items-center">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        Deleting...
                                    </span>
                                    <span v-else>
                                        <i class="fas fa-trash mr-2"></i>
                                        Delete Module
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <div v-if="readPdf" class="fixed inset-0 z-50 bg-white overflow-y-auto scrollbar">
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <button @click="openPdf"
                    class="flex items-center text-gray-600 hover:text-lime-700 transition-colors mb-6 group"
                    aria-label="Go back">
                    <i class="fas fa-arrow-left text-lg group-hover:-translate-x-1 transition-transform"></i>
                    <span class="ml-2 font-medium">Back to Courses</span>
                </button>

                <div id="pdfRead"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.prose {
    line-height: 1.75;
    font-size: 1rem;
}

.prose :where(img):not(:where([class~="not-prose"] *)) {
    margin-top: 1em;
    margin-bottom: 1em;
    border-radius: 0.5rem;
    max-width: 100%;
    height: auto;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.2s ease;
    transform-origin: top right;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

/* Video player customization */
.video-js {
    border-radius: 0.5rem;
    overflow: hidden;
}

.video-js .vjs-big-play-button {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    width: 64px;
    height: 64px;
    line-height: 64px;
    border: none;
    background-color: rgba(22, 163, 74, 0.8);
    transition: all 0.3s ease;
}

.video-js .vjs-big-play-button:hover {
    background-color: rgba(22, 163, 74, 1);
    transform: translate(-50%, -50%) scale(1.05);
}

/* Accessibility focus styles */
button:focus-visible {
    outline: 2px solid #1a73e8;
    outline-offset: 2px;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
}
</style>