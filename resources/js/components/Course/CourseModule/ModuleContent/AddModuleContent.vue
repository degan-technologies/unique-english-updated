<script setup>
import Axios from 'axios';
import 'video.js/dist/video-js.css';
import { storeToRefs } from "pinia";
import { ref, computed, watch } from 'vue';

import { useInstructorStore } from "@/store/useInstructorStore";

const instructorStore = useInstructorStore();
const { selectedCourse } = storeToRefs(instructorStore);

const props = defineProps({
    selectedModule: Object,
});

const emit = defineEmits(['closeModal']);

const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const form = ref({
    title: "",
    content_type: 1,
    content_url: null,
    thumbnail_url: null,
    create_content_url: null,
    create_thumbnail_url: null,
});

// Reset form when module changes
watch(() => props.selectedModule, () => {
    resetForm();
});

const previewContent = computed(() => {
    if (!form.value.create_content_url) return null;

    return {
        url: form.value.create_content_url,
        type: form.value.content_type,
        name: form.value.content_url?.name || ''
    };
});

function getContentType(file) {
    if (!file) return 1;

    const type = file.type || '';
    if (type.startsWith('video/')) return 1;
    if (type.startsWith('application/pdf')) return 2;
    if (type.startsWith('image/')) return 3;
    return 4; // other
}

function handleFileUpload(field, event) {
    errorMessage.value = '';
    const file = event.target.files[0];

    if (!file) return;

    // Validate file size (e.g., 50MB max)
    if (file.size > 50 * 1024 * 1024) {
        errorMessage.value = 'File size must be less than 50MB';
        return;
    }

    if (field === 'content_url') {
        form.value.content_url = file;
        form.value.create_content_url = URL.createObjectURL(file);
        form.value.content_type = getContentType(file);
    }
}

async function storeModuleContent() {
    if (!form.value.title) {
        errorMessage.value = 'Title is required';
        return;
    }

    if (!form.value.content_url) {
        errorMessage.value = 'Please select a file';
        return;
    }

    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const formData = new FormData();
        formData.append("course_module_id", props.selectedModule.id);
        formData.append("title", form.value.title);
        formData.append("content_url", form.value.content_url);
        formData.append("content_type", form.value.content_type);

        const response = await Axios.post("/api/courses/content", formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        successMessage.value = 'Content added successfully!';

        selectedCourse.value = {
            ...selectedCourse.value,  
            courseModules: selectedCourse.value.courseModules.map(module => {
                if (module.id === props.selectedModule.id) {
                    return {
                        ...module,
                        courseContents: [ response.data.data, ...module.courseContents]  
                    };
                }
                return module;  
            })
        }

        setTimeout(() => {
            closeModal();
        }, 1500);
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Failed to add content. Please try again.';
    } finally {
        isLoading.value = false;
    }
}

function resetForm() {
    form.value = {
        title: "",
        description: "",
        content_type: 1,
        content_url: null,
        thumbnail_url: null,
        create_content_url: null,
        create_thumbnail_url: null,
    };
    errorMessage.value = '';
    successMessage.value = '';
}

function closeModal() {
    resetForm();
    emit('closeModal');
}
</script>

<template>
    <div v-if="selectedModule" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded-lg w-full mx-4 max-w-md sm:max-w-lg md:max-w-xl">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-xl font-semibold text-gray-800">
                    Add Lesson to Module
                </h4>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                {{ successMessage }}
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
                {{ errorMessage }}
            </div>

            <form @submit.prevent="storeModuleContent" class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.title" type="text"
                        class="w-full border border-gray-300 p-2.5 text-sm rounded-md focus:ring-2 focus:ring-lime-500 focus:border-lime-500 focus:outline-none transition"
                        placeholder="Enter lesson title" required>
                </div>

                <div>
                    <!-- Preview Section -->
                    <div v-if="previewContent" class="mb-3">
                        <div
                            class="w-full h-48 rounded-md border border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center">
                            <!-- Video Preview -->
                            <video v-if="form.content_type === 1" controls class="w-full h-full object-contain"
                                preload="metadata">
                                <source :src="previewContent.url" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>

                            <!-- PDF Preview -->
                            <div v-else-if="form.content_type === 2" class="p-4 text-center">
                                <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                <p class="text-sm text-gray-600">PDF Document</p>
                            </div>

                            <!-- Image Preview -->
                            <img v-else-if="form.content_type === 3" :src="previewContent.url"
                                class="w-full h-full object-contain" alt="Content preview">

                            <!-- Other File Preview -->
                            <div v-else class="p-4 text-center">
                                <i class="fas fa-file-alt text-5xl text-blue-500 mb-2"></i>
                                <p class="text-sm text-gray-600">Document File</p>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div
                        class="relative border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium text-lime-600">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Videos, PDFs, Images (Max 50MB)
                            </p>
                        </div>
                        <input type="file" @change="handleFileUpload('content_url', $event)"
                            accept="video/*,application/pdf,image/*"
                            class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" required />
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
                    <button type="button" @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-lime-500">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isLoading"
                        class="px-4 py-2 text-sm font-medium text-white bg-lime-600 border border-transparent rounded-md shadow-sm hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span v-if="isLoading">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                        </span>
                        <span v-else>
                            Add Lesson
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>