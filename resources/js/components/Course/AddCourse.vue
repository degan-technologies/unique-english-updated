<script setup>
import Axios from 'axios';
import { storeToRefs } from 'pinia';
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRoute } from "vue-router";
import videojs from 'video.js';
import 'video.js/dist/video-js.css';
import OverviewEditor from '@/components/Layout/overviewEditor.vue';

import { useInstructorStore } from "@/store/useInstructorStore";

// Constants 
const SUCCESS_MESSAGE_TIMEOUT = 3000;
const THUMBNAIL_HEIGHT = '200px';

// Store and Router
const InstructorStore = useInstructorStore();
const { selectedCourse, instructorCourses } = storeToRefs(InstructorStore);
const route = useRoute();

// Refs 
const uploadProgress = ref(0);
const videoPlayer = ref(null);
const playerInstance = ref(null);
const isProcessingThumbnail = ref(0);

// Form state
const course = ref({
    course_name: '',
    overview: '',
    skill_level: '',
    price: '',
    upload_thumbnail: null,
    upload_intro_video: null,
    language: '',
});

// UI State
const errors = ref({});
const successMessage = ref("");
const loading = ref(false);
const isSavingDraft = ref(false);
const isStoringCourse = ref(false);
const isUpdatingCourse = ref(false);

const props = defineProps({
    editCourse: Boolean,
});

// Computed properties
const isEditing = computed(() => !!selectedCourse.value?.id && props.editCourse);
const formTitle = computed(() => isEditing.value ? 'Edit Course' : 'Create New Course');
const submitButtonText = computed(() => {
    if (isStoringCourse.value) return 'Creating...';
    if (isUpdatingCourse.value) return 'Updating...';
    return isEditing.value ? 'Update Course' : 'Publish Course';
});

// Initialize form if editing
watch([() => route.query.slug, () => props.editCourse], async ([slug, editMode]) => {
    if (slug && editMode) {
        await fetchCourseToEdit(slug);
    } else if (!editMode) {
        selectedCourse.value = null;
    }
}, { immediate: true });

// Methods
async function fetchCourseToEdit(slug) {
    try {
        loading.value = true;
        const response = await Axios.get(`/api/show-course/${slug}`);
        selectedCourse.value = response.data.data;
        initializeFormFromSelectedCourse();
    } catch (error) {
        errors.value.general = "Failed to load course data. Please try again.";
    } finally {
        loading.value = false;
    }
}

function initializeFormFromSelectedCourse() {
    course.value = {
        ...selectedCourse.value,
        upload_thumbnail: null,
        upload_intro_video: null,
    };
}
  
async function submitCourse(status) {
    loading.value = true;
    errors.value = {};

    // Set appropriate loading state
    if (status === 'draft') {
        isSavingDraft.value = true;
    } else if (isEditing.value) {
        isUpdatingCourse.value = true;
    } else {
        isStoringCourse.value = true;
    }

    try {
        const formData = createFormData(status);
        const endpoint = isEditing.value
            ? `/api/courses/update/${selectedCourse.value.id}`
            : '/api/courses/course';

        const response = await Axios.post(endpoint, formData);
        handleSuccessResponse(response, status);
    } catch (error) {
        handleSubmissionError(error);
    } finally {
        resetLoadingStates();
    }
}
 
function createFormData(status) {
    const formData = new FormData();
    const { upload_thumbnail, upload_intro_video, ...rest } = course.value;

    // Add all simple fields
    Object.entries(rest).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
            formData.append(key, value);
        }
    });

    // Handle special fields
    formData.append("status", status);

    // Add files if they exist
    if (upload_thumbnail !== null) {
        formData.append('thumbnail_url', upload_thumbnail);
    } else {
        formData.delete('thumbnail_url');
    }

    if (upload_intro_video !== null) {
        formData.append('intro_video', upload_intro_video);
    } else {
        formData.delete('intro_video');
    }

    return formData;
}

function handleSuccessResponse(response, status) {
    successMessage.value = response.data.message ||
        (status === 'draft'
            ? 'Course saved as draft successfully'
            : isEditing.value
                ? 'Course updated successfully'
                : 'Course published successfully');

    if (!isEditing.value && status === 'published') {
        selectedCourse.value = response.data.data;

        instructorCourses.value = instructorCourses.value.filter(course => course?.id !== selectedCourse.value?.id);

        instructorCourses.value = [
            selectedCourse.value,
            ...instructorCourses.value
        ];

        resetForm();
        return;
    }

    instructorCourses.value = [
        response.data.data,
        ...instructorCourses.value
    ];
}

function handleSubmissionError(error) {
    if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
    } else {
        errors.value.general = error.response?.data?.message || 'An error occurred. Please try again.';
        console.error('Submission error:', error);
    }
}

function resetForm() {
    course.value = {
        course_name: '',
        overview: '',
        skill_level: '',
        price: '',
        upload_thumbnail: null,
        upload_intro_video: null,
        language: '',
    };

    uploadProgress.value = 0;
    isProcessingThumbnail.value = 0;
    course.value.upload_thumbnail = null; 
    course.value.upload_intro_video = null; 
    
    if (playerInstance.value) {
        playerInstance.value.src({});
    }
}

function resetLoadingStates() {
    loading.value = false;
    isSavingDraft.value = false;
    isStoringCourse.value = false;
    isUpdatingCourse.value = false;
}

function uploadIntroVideo(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('intro_video', file);

    errors.value['intro_video'] = '';
    uploadProgress.value = 0;

    Axios.post('/api/courses/upload-intro-video', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
                let percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                uploadProgress.value = percent > 99 ? 99 : percent;
            }
        }
    }).then(res => {
        course.value.upload_intro_video = res.data.path; 
        uploadProgress.value = 100;
    }).catch(error => {
        console.error(error);
        if (error.response?.data?.errors) {
            errors.value['intro_video'] = error.response.data.errors.intro_video?.[0] || 'Upload failed';
        }
    })
}

function uploadThumbnail(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('thumbnail_url', file);

    errors.value['thumbnail_url'] = '';
    isProcessingThumbnail.value = 0;

    Axios.post('/api/courses/upload-thumbnail', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
                let percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                isProcessingThumbnail.value = percent > 99 ? 99 : percent; 
            }
        }
    }).then(res => {
        course.value.upload_thumbnail = res.data.path; 
        isProcessingThumbnail.value = 100;
    }).catch(error => {
        console.error(error);
        if (error.response?.data?.errors) {
            errors.value['thumbnail_url'] = error.response.data.errors.thumbnail_url?.[0] || 'Upload failed';
        }
    })
}

const updateOverview = (newOverview) => {
    course.value.overview = newOverview;
};

function goBack() {
    window.history.back();
}

// Lifecycle Hooks
onMounted(() => {
    if (videoPlayer.value) {
        playerInstance.value = videojs(videoPlayer.value, {
            controls: true,
            autoplay: false,
            responsive: true,
            fluid: true,
            aspectRatio: '16:9'
        });
    }
});

onBeforeUnmount(() => {
    if (playerInstance.value) {
        playerInstance.value.dispose();
        playerInstance.value = null;
    }
});

// Watchers
watch(successMessage, (newVal) => {
    if (newVal) {
        setTimeout(() => successMessage.value = '', SUCCESS_MESSAGE_TIMEOUT);
    }
});
</script>

<template>
    <div class="w-full bg-white rounded-lg shadow-md p-6">
        <button @click="goBack()"
            class="flex items-center gap-4 justify-center text-gray-600 hover:text-lime-700 transition-colors mb-6 group"
            aria-label="Go back">
            <i class="fas fa-arrow-left  self-center pb-6 text-lg group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-2xl font-bold self-center  text-lime-700 mb-6"> {{ editCourse ? 'Edit' : 'Add' }} New
                Course</span>
        </button>

        <!-- Success Message -->
        <transition name="fade">
            <div v-if="successMessage" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                {{ successMessage }}
            </div>
        </transition>

        <!-- Error Message -->
        <div v-if="errors.general" class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ errors.general }}
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8"> 
            <div class="lg:col-span-3 space-y-6"> 
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        Course Name <span class="text-red-500">*</span>
                    </label>
                    <input v-model.trim="course.course_name" type="text"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition"
                        :class="{ 'border-red-500': errors.course_name }" placeholder="Enter course name"
                        maxlength="100" />
                    <p v-if="errors.course_name" class="mt-1 text-red-500 text-sm">{{ errors.course_name }}</p>
                </div>

                <!-- Media Uploads -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4"> 
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Course Thumbnail</label>
                        <div class="relative">
                            <div class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition relative overflow-hidden"
                                :class="{ 'border-lime-500 text-gray-500 bg-lime-50': isProcessingThumbnail }">
                                <div class="flex flex-col items-center text-gray-500">
                                    <template v-if="isProcessingThumbnail">
                                        <div class="relative mb-2 w-10 h-10 flex items-center justify-center"> 
                                            <div class="absolute inset-0 rounded-full bg-lime-100 animate-ping opacity-75"></div>
                                            <div class="relative z-10 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-lime-600" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" 
                                                        d="M12 4v4m0 4v4m0 4v4m8-12h-4m-4 0H8m12 4h-4m-4 0H8"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <span v-if="isProcessingThumbnail < 100" class="text-sm font-medium text-gray-600">Processing thumbnail...</span>
                                            <span v-else class="text-sm font-medium text-green-600">Completed</span>
                                        </div>
                                    </template>
                                    <template v-else> 
                                        <i class="fa-solid text-2xl mb-2 text-gray-500"
                                            :class="{
                                                'fa-image': isProcessingThumbnail <= 99,
                                                'fa-check text-lime-600' : isProcessingThumbnail == 100, 
                                            }"></i>
                                        <span class="text-sm">Click to upload thumbnail  </span>
                                    </template> 
                                </div>
                                <input type="file" accept="image/jpeg, image/png"
                                    @change="uploadThumbnail($event)"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                    :disabled="isProcessingThumbnail < 100 && isProcessingThumbnail > 1" />
                            </div>
                            <p v-if="errors.thumbnail_url" class="mt-1 text-red-500 text-sm">{{ errors.thumbnail_url
                                }}</p>
                        </div>
                    </div>

                    <!-- Intro Video Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Intro Video</label>
                        <div class="relative">
                            <div class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition"
                                :class="{ 'border-lime-500 bg-lime-50': uploadProgress }">
                                <div class="flex flex-col items-center text-gray-500">
                                    <template v-if="uploadProgress">
                                        <div class="relative mb-2 w-10 h-10">
                                            <!-- Dynamic progress spinner -->
                                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                                <circle cx="18" cy="18" r="16" fill="none" class="stroke-gray-200"
                                                    stroke-width="2"></circle>
                                                <circle cx="18" cy="18" r="16" fill="none" class="stroke-lime-600"
                                                    stroke-width="2"
                                                    :stroke-dasharray="`${uploadProgress * 1.13}, 113`"></circle>
                                            </svg>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="text-xs font-bold text-lime-600">{{ uploadProgress
                                                    }}%</span>
                                            </div>
                                        </div>
                                        <span class="text-sm"
                                            :class="{
                                                'text-green-600': uploadProgress == 100 
                                            }">{{ uploadProgress < 100  ? 'Uploading video...' : 'Completed' }}</span>
                                    </template>
                                    <template v-else>
                                        <i class="fas fa-video text-2xl mb-2"></i>
                                        <span class="text-sm">Click to upload video</span>
                                    </template>
                                </div>
                                <input type="file" accept="video/mp4" @change="uploadIntroVideo($event)"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                    :disabled="uploadProgress < 100 && uploadProgress > 1"
                                    />
                            </div>
                            <p v-if="errors.intro_video" class="mt-1 text-red-500 text-sm">{{ errors.intro_video }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="space-y-6">
                <!-- Skill Level -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        Skill Level <span class="text-red-500">*</span>
                    </label>
                    <select v-model="course.skill_level"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition"
                        :class="{ 'border-red-500': errors.skill_level }">
                        <option value="" disabled>Select skill level</option>
                        <option value="1">Beginner</option>
                        <option value="2">Intermediate</option>
                        <option value="3">Advanced</option>
                        <option value="4">All Levels</option>
                    </select>
                    <p v-if="errors.skill_level" class="mt-1 text-red-500 text-sm">{{ errors.skill_level }}
                    </p>
                </div>

                <!-- Pricing -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Price in ETB</label>
                    <div class="relative">
                        <input v-model.number="course.price" type="number" min="0" step="0.01"
                            class="w-full border border-gray-300 rounded-md pl-8 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition"
                            placeholder="0.00" />
                    </div>
                    <p v-if="errors.price" class="mt-1 text-red-500 text-sm">{{ errors.price }}</p>
                </div>

                <!-- Language -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Language</label>
                    <input v-model.trim="course.language" type="text"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition"
                        placeholder="e.g. English, Amharic" />
                    <p v-if="errors.language" class="mt-1 text-red-500 text-sm">{{ errors.language }}</p>
                </div>
            </div>
        </div>
        <!-- Course Overview -->
        <div>
            <label class="block text-gray-700 font-semibold mb-2">
                Course Overview <span class="text-red-500">*</span>
            </label>
            <OverviewEditor class="w-full" :selectedCourse="course" @update-overview="updateOverview" />
            <p v-if="errors.overview" class="mt-1 text-red-500 text-sm">{{ errors.overview }}</p>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end mt-8 space-x-4">
            <button type="button" @click="submitCourse('published')"
                :disabled="isStoringCourse || (!isEditing && (isUpdatingCourse || loading || uploadProgress < 100))"
                class="px-6 py-3 bg-lime-600 text-white rounded-lg hover:bg-lime-700 transition font-medium disabled:opacity-70 disabled:cursor-not-allowed">
                <span v-if="isStoringCourse || isUpdatingCourse">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    {{ submitButtonText }}
                </span>
                <span v-else>
                    <i class="fas fa-upload mr-2"></i>
                    {{ submitButtonText }}
                </span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Video player styling with fixed height */
.video-js {
    height: v-bind(THUMBNAIL_HEIGHT);
    background-color: #000;
}

.video-js .vjs-big-play-button {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* File upload hover effect */
.border-dashed:hover {
    border-color: #84cc16;
    background-color: #f7fee7;
}

/* Disabled button styling */
button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Price input styling */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Loading spinner animation */
@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>