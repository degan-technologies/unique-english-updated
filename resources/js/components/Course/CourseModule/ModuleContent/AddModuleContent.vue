<script setup>
import Axios from 'axios';
import 'video.js/dist/video-js.css';
import { storeToRefs } from "pinia";
import { ref, computed, watch } from 'vue';

import { useInstructorStore } from "@/store/useInstructorStore";

const instructorStore = useInstructorStore();
const { addNewLesson } = storeToRefs(instructorStore);

const props = defineProps({
    selectedModule: Object,
});

const emit = defineEmits(['closeModal']);

const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const uploadProgress = ref(0);

const form = ref({
    title: "",
    content_type: null,
    content_url: null, 
    duration: null,
});

// Reset form when module changes
watch(() => props.selectedModule, () => {
    resetForm();
}); 

function uploadIntroVideo(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('uploaded_file', file);

    errorMessage.value = '';
    uploadProgress.value = 0;

    Axios.post('/api/courses/upload-lesson-file', formData, {
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
        form.value.content_url = res.data.data.content_url; 
        form.value.content_type = res.data.data.content_type; 
        form.value.duration = res.data.data.duration; 
        uploadProgress.value = 100;
 
    }).catch(error => {
        console.error(error);
        if (error.response?.data?.errors) {
            errorMessage.value = error.response.data.errors.intro_video?.[0] || 'Upload failed';
        }
    })
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
        formData.append("duration", form.value.duration);

        const response = await Axios.post("/api/courses/content", formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        addNewLesson.value = response.data.data;

        successMessage.value = 'Content added successfully!';

        setTimeout(() => { 
            successMessage.value = '';
        }, 1500);

        setTimeout(() => {
            closeModal();
        }, 1500);
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Failed to add content. Please try again.';
    } finally {
        isLoading.value = false;
        resetForm()
    }
} 

function resetForm() {
    form.value = {
        title: "",
        description: "", 
        content_url: null, 
        content_type: null,
        duration: null,
    };
    errorMessage.value = '';
    successMessage.value = '';
    form.value.content_url = null; 
    form.value.content_type = null; 
    form.value.duration = null; 
    uploadProgress.value = 0;
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
                    <!-- File Upload -->
                    <div class="relative border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition"
                        :class="{
                            'border-lime-500 bg-lime-50': uploadProgress && uploadProgress < 100,
                            'border-green-500 bg-green-50': uploadProgress === 100
                        }">
                        <div class="flex flex-col items-center">
                            <template v-if="uploadProgress">
                                <!-- Upload in progress -->
                                <div class="relative mb-2 w-10 h-10">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                        <circle cx="18" cy="18" r="16" fill="none" class="stroke-gray-200" stroke-width="2"></circle>
                                        <circle cx="18" cy="18" r="16" fill="none" 
                                                :class="uploadProgress === 100 ? 'stroke-green-600' : 'stroke-lime-600'" 
                                                stroke-width="2"
                                                :stroke-dasharray="`${uploadProgress * 1.13}, 113`"></circle>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <template v-if="uploadProgress === 100">
                                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </template>
                                        <template v-else>
                                            <span class="text-xs font-bold text-lime-600">{{ uploadProgress }}%</span>
                                        </template>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-sm font-medium" 
                                        :class="uploadProgress === 100 ? 'text-green-600' : 'text-gray-600'">
                                        {{ uploadProgress === 100 ? 'Upload complete!' : 'Uploading...' }}
                                    </span>
                                    <span v-if="uploadProgress < 100" class="text-xs text-gray-400">
                                        Please don't close this window
                                    </span>
                                </div>
                            </template>
                            
                            <template v-else>
                                <!-- Default upload state -->
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <div class="space-y-1">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium text-lime-600">Click to upload</span> or drag and drop
                                    </p> 
                                </div>
                            </template>
                        </div>
                        
                        <input type="file" 
                            @change="uploadIntroVideo($event)"
                            accept="video/mp4,application/pdf,image/*"
                            class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                            :disabled="uploadProgress < 100 && uploadProgress > 1"
                            />
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
                    <button type="button" @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-lime-500">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isLoading || uploadProgress < 100"
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