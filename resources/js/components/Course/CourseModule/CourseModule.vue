<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import videojs from 'video.js';
import 'video.js/dist/video-js.css';

import { useInstructorStore } from '@/store/useInstructorStore';

import QA from "@/components/Exam/QA.vue";
import ReviewList from "@/components/Course/ReviewList.vue";
import ExamManagement from "@/components/Layout/ExamManagement.vue";
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
const showQuizPage = ref(false);
const editingCourseId = ref(null);
const selectedContent = ref(null);
const addNewContent = ref(null);
const isLoading = ref(false);
const errorMessage = ref(null);

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
const feedBacks = computed(()=>{ return selectedCourse.value?.feedBacks; })
const averageRating = computed(()=>{ return selectedCourse.value?.averageRating; })
const starDistribution = computed(()=>{ return selectedCourse.value?.starDistribution; })


// Methods
function startEditing(module) {
    editCourseModule.value = module;
    actionType.value = 'UPDATE';
};

function openModuleForm(id) {
    courseId.value = id;
    actionType.value = 'STORE';
};

function openDeleteModal(newModule) {
    deleteModule.value = newModule;
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
    isLoading.value = true;
    errorMessage.value = null;

    try {
        await Axios.delete(`/api/courses/module/${moduleId}`);
        if (selectedCourse.value?.courseModules) {
            selectedCourse.value.courseModules = selectedCourse.value.courseModules.filter(
                item => item.id !== moduleId
            );
        }
        showDeleteModal.value = false;
    } catch (error) {
        console.error("Failed to delete module:", error);
        errorMessage.value = "Failed to delete module. Please try again.";
    } finally {
        isLoading.value = false;
    }
};

function onAddModuleContent(module) {
    addNewContent.value = module;
};

function onAddNewModule(module) {
   if (selectedCourse.value && Array.isArray(selectedCourse.value.courseModules)) {
        selectedCourse.value.courseModules = [module, ...selectedCourse.value.courseModules];
    } else if (selectedCourse.value) {
        selectedCourse.value.courseModules = [module];
    }
}

function onAddLesson(content) {
    if (selectedCourse.value?.courseModules) {
        selectedCourse.value.courseModules = selectedCourse.value.courseModules.map(module => {
            console.log(module.id,  content.module_id);
            if (module.id === content.module_id) {
                return {
                    ...module,
                    courseContents: [
                        ...(module.courseContents || []),
                        content
                    ]
                };
            }
            return module;
        });
    }
    addNewContent.value = null;
};

function addQuiz(module) {
    selectedContent.value = null;
    selectedModule.value = module;
    showQuizPage.value = true;
};

function closeQuizPage() {
    showQuizPage.value = false;
};

function actionExpandModule(module) {
    selectedContent.value = null;
    selectedModule.value = null;
    expandedModule.value = expandedModule.value === module.id ? null : module.id;
};

function toggleDropdown(moduleId, event) {
    event.stopPropagation();
    dropdownOpen.value = dropdownOpen.value === moduleId ? null : moduleId;
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

onMounted(() => {
    if(!selectedCourse.value) { 
        Axios.get(`/api/show-course/${ route.query.slug}`)
        .then(res => {
            selectedCourse.value = res.data.data;
        })
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

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);

    if (playerInstance) {
        playerInstance.dispose();
    }
});
</script>

<template>
    <div v-if="!showQuizPage" class="w-full">
        <!-- Back Button -->
        <button @click="goBack()"
            class="px-4 py-2 text-black rounded-md hover:text-lime-700 transition mr-4 flex items-center"
            aria-label="Go back">
            <i class="fa-solid fa-arrow-left text-xl font-bold"></i>
        </button>

        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ errorMessage }}</p>
        </div>

        <!-- Loading Indicator -->
        <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-4">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-lime-600"></div>
                <p class="text-gray-700">Processing your request...</p>
            </div>
        </div>

        <!-- Course Header Section -->
        <div class="bg-white rounded-lg shadow-sm grid gap-4 grid-cols-1 lg:grid-cols-2 w-full mx-auto p-6">
            <!-- Video Thumbnail/Player -->
            <div class="w-full h-full p-2 flex flex-col items-start relative">
                <div v-if="!isPlaying && selectedCourse?.thumbnail_url" class="relative w-full h-full cursor-pointer"
                    @click="isPlaying = true" role="button" aria-label="Play course introduction video">
                    <img :src="selectedCourse.thumbnail_url" :alt="`Thumbnail for ${selectedCourse.course_name}`"
                        class="w-full h-full object-cover transition-transform duration-300 rounded-t-lg shadow-lg hover:shadow-xl">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="absolute w-16 h-16 rounded-full bg-lime-500 opacity-50 animate-burst"></div>
                        <div class="p-2 bg-lime-500 rounded-full z-10 flex items-center justify-center shadow-md">
                            <i class="fas fa-play-circle text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div v-else class="relative w-full h-full">
                    <video v-if="selectedCourse?.intro_video_url" ref="videoPlayer"
                        class="video-js vjs-default-skin w-full h-full rounded-t-lg shadow-md border" controls
                        preload="auto" :poster="selectedCourse.thumbnail_url">
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
                    <p v-else class="text-center text-gray-500 p-4">No video available</p>
                </div>
            </div>

            <!-- Course Details -->
            <div class="p-6 h-fit justify-center">
                <h2 class="text-xl font-semibold mb-4">Course Details</h2>
                <div class="flex flex-col gap-4">
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-user-graduate text-blue-500 w-6 text-center"></i>
                        <strong class="px-4">Level:</strong>
                        <span>{{ selectedCourse?.skill_level || 'Not specified' }}</span>
                    </div>
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-language text-green-500 w-6 text-center"></i>
                        <strong class="px-4">Language:</strong>
                        <span>{{ selectedCourse?.language || 'Not specified' }}</span>
                    </div>
                    <div class="leading-relaxed text-lg py-1 flex items-center">
                        <i class="fas fa-clock text-yellow-500 w-6 text-center"></i>
                        <strong class="px-4">Duration:</strong>
                        <span>{{ selectedCourse?.credit_hour || 'Not specified' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Content Section -->
        <div class="bg-white rounded-lg shadow-sm mt-4 w-full mx-auto p-6">
            <!-- Course Title -->
            <div class="flex flex-col lg:flex-row items-left justify-between mb-6">
                <h3 class="text-2xl text-gray-900 font-bold">{{ selectedCourse?.course_name }}</h3>

                <!-- Course Actions -->
                <div class="flex items-center space-x-2 mt-4 lg:mt-0">
                    <button @click="getSelectedCourse(courseEditTab, selectedCourse)"
                        class="flex items-center space-x-1 px-3 py-2 rounded-md transition text-lime-600 hover:bg-slate-200"
                        aria-label="Edit course">
                        <i class="fas fa-edit"></i>
                        <span class="hidden sm:inline">Edit</span>
                    </button>
                    <button @click="openModuleForm(selectedCourse?.id)"
                        class="flex items-center space-x-1 px-3 py-2 rounded-md transition text-black-600 hover:bg-slate-200"
                        aria-label="Add module">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">Add Module</span>
                    </button>
                    <button @click="deleteCourse(selectedCourse?.id)"
                        class="flex items-center space-x-1 px-3 py-2 rounded-md transition text-black hover:bg-slate-200"
                        aria-label="Delete course">
                        <i class="fas fa-trash"></i>
                        <span class="hidden sm:inline">Delete</span>
                    </button>
                </div>
            </div>

            <!-- Course Overview -->
            <div class="course-overview mb-8">
                <h2 class="text-xl font-semibold mb-4">Course Overview</h2>
                <div class="overview-content preview ql-editor max-w-full text-justify mt-5"
                    v-html="selectedCourse?.overview || '<p>No overview provided</p>'"></div>
            </div>

            <!-- Module Management -->
            <AddCourseModule v-if="editCourseModule" :actionType="actionType" />

            <!-- Modules List -->
            <div class="mt-8">
                <h3 v-if="courseModules.length > 0" class="text-xl font-semibold mb-6">Modules And Lessons</h3>
                <p v-else class="text-gray-500 text-center py-8">
                    No modules added yet. Click "Add Module" to get started.
                </p>

                <!-- Modules List -->
                <div class="modules-list space-y-4">
                    <div v-for="module in courseModules" :key="module.id"
                        class="relative p-4 rounded-lg border border-gray-200 transition-all duration-200 hover:shadow-md"
                        :class="{ 'shadow-md z-10 bg-white': expandedModule === module.id }">
                        <!-- Module Header -->
                        <div class="flex justify-between items-center cursor-pointer"
                            @click="actionExpandModule(module)" :aria-expanded="expandedModule === module.id"
                            :aria-controls="`module-${module.id}-contents`">
                            <div class="font-semibold flex-grow text-lg text-blue-500 flex items-center">
                                <i class="fa-solid mr-4 text-xl fa-folder text-yellow-500"></i>
                                {{ module.title }}
                                <span class="ml-2 text-sm text-gray-500">
                                    ({{ module.courseContents?.length || 0 }} items)
                                </span>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button class="text-gray-500 text-sm"
                                    :aria-label="expandedModule === module.id ? 'Collapse module' : 'Expand module'">
                                    <i
                                        :class="expandedModule === module.id ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                                </button>

                                <div class="relative">
                                    <button @click.stop="toggleDropdown(module.id, $event)"
                                        class="p-1 rounded-full hover:bg-gray-200 transition duration-200"
                                        :aria-label="`Actions for ${module.title}`"
                                        :aria-expanded="dropdownOpen === module.id">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <transition name="fade-slide">
                                        <div v-if="dropdownOpen === module.id"
                                            class="absolute right-0 mt-2 bg-white shadow-md rounded-md z-50 w-48 text-sm border border-gray-100"
                                            role="menu">
                                            <button @click="startEditing(module)"
                                                class="flex hover:bg-blue-50 items-center w-full px-3 py-2 text-md transition duration-200"
                                                role="menuitem">
                                                <i class="fas fa-edit mr-2 text-blue-500"></i>
                                                Edit Module
                                            </button>
                                            <button @click="openDeleteModal(module)"
                                                class="flex items-center hover:bg-red-50 w-full px-3 py-2 text-md transition duration-200"
                                                role="menuitem">
                                                <i class="fas fa-trash mr-2 text-red-500"></i>
                                                Delete Module
                                            </button>
                                            <button @click="onAddModuleContent(module)"
                                                class="flex items-center hover:bg-blue-50 w-full px-3 py-2 text-md transition duration-200"
                                                role="menuitem">
                                                <i class="fas fa-plus mr-2 text-green-500"></i>
                                                Add Content
                                            </button>
                                            <button @click="addQuiz(module)"
                                                class="flex items-center hover:bg-blue-50 w-full px-3 py-2 text-md transition duration-200"
                                                role="menuitem">
                                                <i class="fas fa-question-circle mr-2 text-purple-500"></i>
                                                Add Quiz
                                            </button>
                                        </div>
                                    </transition>
                                </div>
                            </div>
                        </div>

                        <!-- Module Contents (Expandable) -->
                        <transition name="fade-slide">
                            <div v-if="expandedModule === module.id" class="mt-4 pt-4 border-t border-gray-100"
                                :id="`module-${module.id}-contents`">
                                <div v-if="module.courseContents?.length"
                                    class="grid sm:grid-cols-2 my-4 lg:grid-cols-3 gap-4">
                                    <ModuleContent v-for="content in module.courseContents" :key="content.id"
                                        :selectedContent="content" :selectedModule="null" />
                                </div>
                                <p v-else class="text-gray-500 text-center py-4">
                                    No content added to this module yet.
                                </p>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>

        <!-- tabs -->
        <div class="flex justify-start mt-16 border-b-2 border-gray-200 gap-4">
                    <button @click="setActiveTab(qaTab)"
                        class="tab-button  text-black px-4 py-2 text-md font-semibold" :class="{
                            'border-lime-700 border-b-2 text-lime-700': activeTab === qaTab,
                            'hover:border-lime-500': activeTab !== qaTab,
                        }">
                        <i class="fas fa-question pr-2"></i> Q&A
                    </button> 
                    <button @click="setActiveTab(reviewTab)"
                        class="tab-button  text-black px-4 py-2 text-md font-semibold" :class="{
                            'border-lime-700 border-b-2 text-lime-700': activeTab === reviewTab,
                            'hover:border-lime-500': activeTab !== reviewTab,
                        }">
                        <i class="fas fa-star pr-2"></i> Reviews
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="mt-8">
                    <div v-if="activeTab === qaTab">
                        <QA v-if="selectedCourse" 
                            :selectedCourseSlug="selectedCourse?.slug" 
                            :courseId="selectedCourse?.id"/>
                    </div> 
                    <div v-if="activeTab === reviewTab">
                        <ReviewList 
                            v-if="feedBacks"
                            :feedBacks="feedBacks" 
                            :averageRating="averageRating"
                            :starDistribution="starDistribution" 
                            :showOnly="false" />
                    </div>
                </div>

            <!-- Add Module Form -->
            <AddCourseModule 
                v-if="courseId" 
                :courseId="courseId" 
                :actionType="actionType"
                @onAddNewModule="onAddNewModule" />

            <!-- Add Content Form -->
            <AddModuleContent 
                v-if="addNewContent" 
                :selectedModule="addNewContent" 
                @onAddLesson="onAddLesson"
                @closeModal="addNewContent = null" />            
        </div>
        <!-- delete conformation dialog -->
        <transition name="fade">
            <div v-if="showDeleteModal && deleteModule"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div class="bg-white rounded shadow-lg w-96 p-6">
                    <h3 class="text-xl font-bold mb-4">Confirm Deletion dfdfdfdf</h3>
                    <p class="mb-6">
                        Are you sure you want to delete <strong>{{ deleteModule?.title }} </strong>?
                    </p>
                    <div class="flex justify-end space-x-2">
                        <button @click="showDeleteModal = false" class="px-4 py-3 border rounded hover:bg-gray-100">
                            Cancel
                        </button>
                        <button @click="deleteCourseModule(deleteModule?.id)"
                            class="px-4 py-3 bg-red-500 text-white rounded hover:bg-red-600">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>


    <!-- Quiz Management -->
    <ExamManagement v-else :module="selectedModule" @backToModule="closeQuizPage" />
</template>

<style scoped>
.overview-content {
    font-size: 1.1rem !important;
    line-height: 1.75rem !important;
    all: revert;
}

.overview-content img {
    max-width: 100%;
    height: auto;
}

/* Animations */
@keyframes burst {
    0% {
        transform: scale(1);
        opacity: 0.5;
    }

    70% {
        transform: scale(2.2);
        opacity: 0;
    }

    100% {
        opacity: 0;
    }
}

.animate-burst {
    animation: burst 1.8s ease-out infinite;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Video player customization */
.video-js {
    border-radius: 8px;
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
}

.video-js .vjs-big-play-button:hover {
    background-color: rgba(22, 163, 74, 1);
}

/* Accessibility focus styles */
button:focus {
    outline: 2px solid #1a73e8;
    outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .overview-content {
        font-size: 1rem !important;
        line-height: 1.6rem !important;
    }
}
</style>