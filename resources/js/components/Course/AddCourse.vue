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
const MAX_THUMBNAIL_SIZE = 10 * 1024 * 1024; // 10MB
const MAX_VIDEO_SIZE = 50 * 1024 * 1024; // 50MB
const SUCCESS_MESSAGE_TIMEOUT = 3000;
const THUMBNAIL_HEIGHT = '200px'; // Specific height for thumbnails

// Store and Router
const InstructorStore = useInstructorStore();
const { selectedCourse } = storeToRefs(InstructorStore);
const route = useRoute();

// Refs
const thumbnail_url = ref('thumbnail_url');
const intro_video = ref('intro_video');
const videoPlayer = ref(null);
const playerInstance = ref(null);
const isProcessingThumbnail = ref(false);
const isProcessingVideo = ref(false);

// Form state
const course = ref({
    course_name: '',
    overview: '',
    skill_level: '',
    price: '',
    discount: '',
    upload_thumbnail: null,
    upload_intro_video: null,
    language: '',
    create_thumbnail_url: null,
    create_intro_video: null
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
        console.error("Failed to fetch course:", error);
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
        create_thumbnail_url: null,
        create_intro_video: null
    };
}

function validateFile(file, field) {
    const maxSize = field === thumbnail_url.value ? MAX_THUMBNAIL_SIZE : MAX_VIDEO_SIZE;
    const maxSizeMB = maxSize / (1024 * 1024);

    if (file.size > maxSize) {
        errors.value[field] = `File size must be less than ${maxSizeMB}MB`;
        return false;
    }

    if (field === thumbnail_url.value && !file.type.startsWith('image/')) {
        errors.value[field] = 'Please upload an image file';
        return false;
    }

    if (field === intro_video.value && !file.type.startsWith('video/')) {
        errors.value[field] = 'Please upload a video file';
        return false;
    }

    return true;
}

async function handleFileUpload(field, event) {
    const file = event.target.files[0];
    if (!file) return;

    // Clear any previous errors
    errors.value[field] = '';

    if (!validateFile(file, field)) return;

    // Set processing state
    if (field === thumbnail_url.value) {
        isProcessingThumbnail.value = true;
    } else {
        isProcessingVideo.value = true;
    }

    // Simulate processing delay (in real app, this might be actual processing)
    await new Promise(resolve => setTimeout(resolve, 800));

    try {
        if (field === thumbnail_url.value) {
            course.value.upload_thumbnail = file;
            course.value.create_thumbnail_url = URL.createObjectURL(file);
        } else {
            course.value.upload_intro_video = file;
            course.value.create_intro_video = URL.createObjectURL(file);
            updateVideoPlayer();
        }
    } finally {
        if (field === thumbnail_url.value) {
            isProcessingThumbnail.value = false;
        } else {
            isProcessingVideo.value = false;
        }
    }
}

function updateVideoPlayer() {
    if (playerInstance.value && course.value.create_intro_video) {
        playerInstance.value.src({
            src: course.value.create_intro_video,
            type: 'video/mp4'
        });
        playerInstance.value.play();
    }
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
    if (upload_thumbnail instanceof File) {
        formData.append('thumbnail_url', upload_thumbnail);
    }
    if (upload_intro_video instanceof File) {
        formData.append('intro_video', upload_intro_video);
    }

    return formData;
}

function handleSuccessResponse(response, status) {
    successMessage.value = response.data.message ||
        (status === 'draft' ? 'Course saved as draft successfully' :
            isEditing.value ? 'Course updated successfully' : 'Course published successfully');

    if (!isEditing.value && status === 'published') {
        selectedCourse.value = response.data.data;
        resetForm();
    }
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
        discount: '',
        upload_thumbnail: null,
        upload_intro_video: null,
        language: '',
        create_thumbnail_url: null,
        create_intro_video: null
    };

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

const updateOverview = (newOverview) => {
    course.value.overview = newOverview;
};

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

    // Clean up object URLs
    if (course.value.create_thumbnail_url) {
        URL.revokeObjectURL(course.value.create_thumbnail_url);
    }
    if (course.value.create_intro_video) {
        URL.revokeObjectURL(course.value.create_intro_video);
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
    <div class="flex justify-center items-start min-h-screen py-8">
        <div class="w-full max-w-6xl bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-lime-700 mb-6">
                {{ formTitle }}
            </h2>

            <!-- Success Message -->
            <transition name="fade">
                <div v-if="successMessage"
                    class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ successMessage }}
                </div>
            </transition>

            <!-- Error Message -->
            <div v-if="errors.general" class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ errors.general }}
            </div>

            <div v-if="loading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-lime-600"></div>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Content Column -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Course Name -->
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
                        <!-- Thumbnail Upload -->
                        <div>
                            <div v-if="course.create_thumbnail_url || course?.thumbnail_url" class="mb-2 relative">
                                <img :src="course.create_thumbnail_url || course.thumbnail_url" alt="Course Thumbnail"
                                    class="w-full object-cover rounded-md shadow-md border border-gray-200"
                                    :style="{ height: THUMBNAIL_HEIGHT }" />
                                <div v-if="isProcessingThumbnail"
                                    class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center rounded-md">
                                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white">
                                    </div>
                                </div>
                            </div>
                            <label class="block text-gray-700 font-medium mb-2">Course Thumbnail</label>
                            <div class="relative">
                                <div class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition"
                                    :class="{ 'border-lime-500 bg-lime-50': isProcessingThumbnail }">
                                    <div class="flex flex-col items-center text-gray-500">
                                        <template v-if="isProcessingThumbnail">
                                            <div
                                                class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-lime-600 mb-2">
                                            </div>
                                            <span class="text-sm">Processing thumbnail...</span>
                                        </template>
                                        <template v-else>
                                            <i class="fas fa-image text-2xl mb-2"></i>
                                            <span class="text-sm">Click to upload thumbnail</span>
                                            <span class="text-xs mt-1">(Max 10MB, JPG/PNG)</span>
                                        </template>
                                    </div>
                                    <input type="file" accept="image/jpeg, image/png"
                                        @change="handleFileUpload(thumbnail_url, $event)"
                                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                        :disabled="isProcessingThumbnail" />
                                </div>
                                <p v-if="errors.thumbnail_url" class="mt-1 text-red-500 text-sm">{{ errors.thumbnail_url
                                    }}</p>
                            </div>
                        </div>

                        <!-- Intro Video Upload -->
                        <div>
                            <div v-if="course.create_intro_video || course?.intro_video_url" class="mb-2 relative">
                                <video ref="videoPlayer"
                                    class="video-js w-full rounded-md border border-gray-200 bg-black" controls
                                    preload="auto" :style="{ height: THUMBNAIL_HEIGHT }">
                                    <source :src="course.create_intro_video || course.intro_video_url"
                                        type="video/mp4" />
                                </video>
                                <div v-if="isProcessingVideo"
                                    class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center rounded-md">
                                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white">
                                    </div>
                                </div>
                            </div>
                            <label class="block text-gray-700 font-medium mb-2">Intro Video</label>
                            <div class="relative">
                                <div class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition"
                                    :class="{ 'border-lime-500 bg-lime-50': isProcessingVideo }">
                                    <div class="flex flex-col items-center text-gray-500">
                                        <template v-if="isProcessingVideo">
                                            <div
                                                class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-lime-600 mb-2">
                                            </div>
                                            <span class="text-sm">Processing video...</span>
                                        </template>
                                        <template v-else>
                                            <i class="fas fa-video text-2xl mb-2"></i>
                                            <span class="text-sm">Click to upload video</span>
                                            <span class="text-xs mt-1">(Max 50MB, MP4)</span>
                                        </template>
                                    </div>
                                    <input type="file" accept="video/mp4"
                                        @change="handleFileUpload(intro_video, $event)"
                                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                        :disabled="isProcessingVideo" />
                                </div>
                                <p v-if="errors.intro_video" class="mt-1 text-red-500 text-sm">{{ errors.intro_video }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Course Overview -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Course Overview <span class="text-red-500">*</span>
                        </label>
                        <OverviewEditor :selectedCourse="course" @update-overview="updateOverview" />
                        <p v-if="errors.overview" class="mt-1 text-red-500 text-sm">{{ errors.overview }}</p>
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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Price ($)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-500">$</span>
                                <input v-model.number="course.price" type="number" min="0" step="0.01"
                                    class="w-full border border-gray-300 rounded-md pl-8 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition"
                                    placeholder="0.00" />
                            </div>
                            <p v-if="errors.price" class="mt-1 text-red-500 text-sm">{{ errors.price }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Discount (%)</label>
                            <div class="relative">
                                <span class="absolute right-3 top-3 text-gray-500">%</span>
                                <input v-model.number="course.discount" type="number" min="0" max="100"
                                    class="w-full border border-gray-300 rounded-md px-4 pr-8 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition"
                                    placeholder="0" />
                            </div>
                            <p v-if="errors.discount" class="mt-1 text-red-500 text-sm">{{ errors.discount }}</p>
                        </div>
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

            <!-- Form Actions -->
            <div class="flex justify-end mt-8 space-x-4"> 
                <button type="button" @click="submitCourse('published')"
                    :disabled="isStoringCourse || isUpdatingCourse || loading"
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