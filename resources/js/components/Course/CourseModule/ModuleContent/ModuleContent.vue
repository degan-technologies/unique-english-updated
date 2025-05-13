<script setup>
import Axios from 'axios';
import { ref, computed, watch } from 'vue';

const showDeleteModal = ref(false);
const editingContent = ref(false);
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
 
const selectedLesson = ref(null);

const form = ref({
    title: "",
    description: "",
    content_type: 1,
    content_url: null,
    thumbnail_url: null,
    create_content_url: null,
    create_thumbnail_url: null,
});

const props = defineProps({
    selectedContent: Object, 
});

const emit = defineEmits(['cancelEdit', 'updateContent']);

const selectedContent = computed(() => props.selectedContent);

watch(
    () => props.selectedContent,
    (val) => {
        if (val?.id) {
            selectedLesson.value = val;
            resetForm();
            form.value = {
                title: val.title,
                description: val.description,
                content_type: val.content_type,
                content_url: null,
                thumbnail_url: null,
                create_content_url: val.course_content_url,
                create_thumbnail_url: val.thumbnail_url,
            };
        }
    },
    { immediate: true }
);

const contentTypeLabels = {
    1: 'Video',
    2: 'PDF',
    3: 'Image',
    4: 'Document'
};

const previewContent = computed(() => {
    if (form.value.create_content_url) {
        return {
            url: form.value.create_content_url,
            type: form.value.content_type
        };
    }
    if (selectedContent.value?.course_content_url) {
        return {
            url: selectedContent.value.course_content_url,
            type: selectedContent.value.content_type
        };
    }
    return null;
});

function editSelectedContent() {
    if (!selectedLesson.value?.id) return;
    editingContent.value = true;
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

function cancelEdit() {
    editingContent.value = false;
    resetForm();
    emit('cancelEdit');
}

function handleFileUpload(field, event) {
    errorMessage.value = '';
    const file = event.target.files[0];
    
    if (!file) return;
    
    // Validate file size (50MB max)
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

function getContentType(file) {
    if (!file) return 1;
    const type = file.type || '';
    if (type.startsWith('video/')) return 1;
    if (type.startsWith('application/pdf')) return 2;
    if (type.startsWith('image/')) return 3;
    return 4;
}

async function updateSelectedContent() {
    if (!form.value.title) {
        errorMessage.value = 'Title is required';
        return;
    }

    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = ''; 

    try {
        const formData = new FormData();
        formData.append("title", form.value.title);
        formData.append("description", form.value.description);
        if (form.value.content_url) {
            formData.append("content_url", form.value.content_url);
        }

        const response = await Axios.post(
            `/api/courses/update-content/${selectedContent.value.id}`, 
            formData
        );

        successMessage.value = 'Content updated successfully!';
        selectedLesson.value = response.data.data;
        
        setTimeout(() => {
            editingContent.value = false;
            emit('updateContent');
        }, 1500);
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Failed to update content';
    } finally {
        isLoading.value = false;
    }
}

function openDeleteModal(newModule) {
    deleteModule.value = newModule;
    showDeleteModal.value = true;
}

async function deleteSelectedContent() { 
    try {
        await Axios.delete(`/api/courses/content/${selectedContent.value.id}`);
        emit('updateContent');
        cancelEdit();
    } catch (err) {
        errorMessage.value = 'Failed to delete content';
    }
}
</script>

<template>
    <div class="w-full">
        <!-- View Mode -->
        <div v-if="!editingContent && selectedLesson" class="w-full flex flex-col items-start">
            <div class="w-full aspect-video bg-gray-100 rounded-lg overflow-hidden mb-4">
                <video v-if="selectedLesson.content_type === 1" 
                    controls
                    class="w-full h-full object-contain"
                    :poster="selectedLesson.thumbnail_url">
                    <source :src="selectedLesson.course_content_url" type="video/mp4">
                </video>
                
                <div v-else-if="selectedLesson.content_type === 2" class="h-full flex flex-col items-center justify-center p-4">
                    <i class="fas fa-file-pdf text-6xl text-red-500 mb-2"></i>
                    <p class="text-sm text-gray-600">PDF Document</p>
                    <a :href="selectedLesson.content_url" target="_blank" 
                       class="mt-2 text-sm text-blue-500 hover:underline">
                        View PDF
                    </a>
                </div>
                
                <img v-else-if="selectedLesson.content_type === 3" 
                    :src="selectedLesson.course_content_url"
                    class="w-full h-full object-contain"
                    :alt="selectedLesson.title">
                
                <div v-else class="h-full flex flex-col items-center justify-center p-4">
                    <i class="fas fa-file text-6xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-600">{{ contentTypeLabels[selectedLesson.content_type] || 'File' }}</p>
                </div>
            </div>
            
            <div class="w-full">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ selectedLesson.title }}</h3> 
                
                <div class="flex gap-2">
                    <button @click="editSelectedContent"
                        class="flex items-center w-full px-2 py-2 text-md hover:bg-lime-400 transition duration-200 rounded">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    <button @click="openDeleteModal(selectedLesson)"
                        class="flex items-center w-full px-2 py-2 text-md hover:bg-red-100 transition duration-200 rounded">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Edit Mode -->
        <div v-if="editingContent" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">Edit Lesson</h3>
                        <button @click="cancelEdit" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <!-- Messages -->
                    <div v-if="successMessage" class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                        {{ successMessage }}
                    </div>
                    <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
                        {{ errorMessage }}
                    </div>
                    
                    <form @submit.prevent="updateSelectedContent" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.title" type="text"
                                class="w-full border border-gray-300 p-2.5 rounded-md focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                                required>
                        </div>
                        
                        <div>
                            <div v-if="previewContent" class="w-full h-48 bg-gray-50 rounded-md border border-gray-200 overflow-hidden">
                                <video v-if="previewContent.type === 1" 
                                    controls
                                    class="w-full h-full object-contain">
                                    <source :src="previewContent.url" type="video/mp4">
                                </video>
                                
                                <div v-else-if="previewContent.type === 2" class="h-full flex flex-col items-center justify-center">
                                    <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                    <p class="text-sm text-gray-600">PDF Document</p>
                                </div>
                                
                                <img v-else-if="previewContent.type === 3" 
                                    :src="previewContent.url"
                                    class="w-full h-full object-contain">
                                
                                <div v-else class="h-full flex flex-col items-center justify-center">
                                    <i class="fas fa-file text-5xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Document</p>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Replace Content (optional)
                                </label>
                                <div class="relative border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium text-lime-600">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Videos, PDFs, Images (Max 50MB)
                                        </p>
                                    </div>
                                    <input type="file" 
                                        @change="handleFileUpload('content_url', $event)" 
                                        accept="video/*,application/pdf,image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="cancelEdit"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                :disabled="isLoading"
                                class="px-4 py-2 text-sm font-medium text-white bg-lime-600 rounded-md shadow-sm hover:bg-lime-700 disabled:opacity-70 disabled:cursor-not-allowed">
                                <span v-if="isLoading">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                                </span>
                                <span v-else>
                                    Save Changes
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                        <button @click="deleteSelectedContent(deleteModule?.id)"
                            class="px-4 py-3 bg-red-500 text-white rounded hover:bg-red-600">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>