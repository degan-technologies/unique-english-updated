<script setup>
import Axios from 'axios';
import { storeToRefs } from "pinia";
import { ref, computed, watch } from 'vue';

import { useInstructorStore } from "@/store/useInstructorStore";

import LessonPdfReader from "@/components/Course/LessonPdfReader.vue";

const instructorStore = useInstructorStore();
const { selectedCourse } = storeToRefs(instructorStore);

const showDeleteModal = ref(false);
const editingContent = ref(false);
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const deleteModule = ref(null);
const isProcessing = ref(false);
const selectedLesson = ref(null);
const readeSelectedPdf = ref(false);

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

const emit = defineEmits(['cancelEdit', 'openPdf']);

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

const contentTypeIcons = {
    1: 'fa-play-circle',
    2: 'fa-file-pdf',
    3: 'fa-image',
    4: 'fa-file-alt'
};

const contentTypeColors = {
    1: 'bg-blue-100 text-blue-600',
    2: 'bg-red-100 text-red-600',
    3: 'bg-purple-100 text-purple-600',
    4: 'bg-gray-100 text-gray-600'
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

        const contentIdToUpdate = selectedContent.value.id;
        const moduleIdToUpdate = selectedContent.value.module_id;

        const response = await Axios.post(
            `/api/courses/update-content/${contentIdToUpdate}`,
            formData
        );
 
         selectedCourse.value = {
            ...selectedCourse.value,
            courseModules: selectedCourse.value.courseModules.map(module => {
                if (module.id === moduleIdToUpdate) {
                    return {
                        ...module,
                        courseContents: module.courseContents.map(content => {
                            if (content.id === contentIdToUpdate) {
                                return {
                                    ...content,
                                    ...response.data.data  
                                };
                            }
                            return content;
                        })
                    };
                }
                return module;
            })
        };

        successMessage.value = 'Content updated successfully!';

        setTimeout(() => {
            editingContent.value = false;
            successMessage.value = ''; 
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
    isProcessing.value = true;
    const contentIdToDelete = selectedContent.value.id;
    const moduleIdToDelete = selectedContent.value.module_id;

    await Axios.delete(`/api/courses/content/${contentIdToDelete}`)
        .then(res => {
            selectedCourse.value = {
                ...selectedCourse.value,
                courseModules: selectedCourse.value.courseModules.map(module => {
                    if (module.id === moduleIdToDelete) {
                        return {
                            ...module,
                            courseContents: module.courseContents.filter(
                                content => content.id !== contentIdToDelete
                            )
                        };
                    }
                    return module;
                })
            };

            cancelEdit();
            showDeleteModal.value = false;

        }).catch(err => {
            errorMessage.value = err.response?.data?.message || 'Failed to delete content';
        }).finally(() => {
            isProcessing.value = false;
        });
}

function openPdf() {
    emit('openPdf');
    readeSelectedPdf.value = true;
}
</script>

<template>
    <!-- Content Card -->
    <div
        class="w-full h-full bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
        <!-- View Mode -->
        <div v-if="!editingContent && selectedLesson" class="h-full flex flex-col">
            <!-- Content Preview -->
            <div class="relative aspect-video bg-gray-100 h-48">
                <video v-if="selectedLesson.content_type === 1" controls class="w-full h-full object-contain"
                    :poster="selectedLesson.thumbnail_url">
                    <source :src="selectedLesson.course_content_url" type="video/mp4">
                    Your browser does not support the video tag.
                </video>

                <div v-else-if="selectedLesson.content_type === 2"
                    class="h-full flex flex-col items-center justify-center p-4">
                    <div :class="['p-4 rounded-full mb-3', contentTypeColors[selectedLesson.content_type]]">
                        <i :class="['fas text-2xl', contentTypeIcons[selectedLesson.content_type]]"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">{{ contentTypeLabels[selectedLesson.content_type] }}
                    </p>
                    <button @click="openPdf()"
                        class="mt-2 text-xs text-lime-500 hover:underline inline-flex items-center">
                        <i class="fas fa-external-link-alt mr-1"></i> Open File
                    </button>

                    <teleport to="#pdfRead" v-if="readeSelectedPdf">
                        <LessonPdfReader 
                            :selectedLesson="selectedLesson" 
                        />
                    </teleport>
                </div>

                <img v-else-if="selectedLesson.content_type === 3" :src="selectedLesson.course_content_url"
                    class="w-full h-full object-cover" :alt="selectedLesson.title">

                <div v-else class="h-full flex flex-col items-center justify-center p-4">
                    <div :class="['p-4 rounded-full mb-3', contentTypeColors[selectedLesson.content_type]]">
                        <i :class="['fas text-2xl', contentTypeIcons[selectedLesson.content_type]]"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700">{{ contentTypeLabels[selectedLesson.content_type] }}
                    </p>
                </div>

                <div class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                    {{ contentTypeLabels[selectedLesson.content_type] }}
                </div>
            </div>

            <!-- Content Details -->
            <div class="p-4 flex-grow flex flex-col">
                <h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2">{{ selectedLesson.title }}</h3>
                <div class="mt-auto flex justify-between items-center">
                    <span class="text-xs text-gray-400">
                        {{ selectedLesson.created_at }}
                    </span>

                    <div class="flex space-x-2">
                        <button @click="editSelectedContent"
                            class="p-2 text-gray-500 hover:text-blue-500 hover:bg-blue-50 rounded-full transition-colors"
                            aria-label="Edit content">
                            <i class="fas fa-pencil-alt text-sm"></i>
                        </button>
                        <button @click="openDeleteModal(selectedLesson)"
                            class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors"
                            aria-label="Delete content">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <teleport to="body">
        <transition name="modal-fade">
            <div v-if="editingContent" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <transition name="modal-fade">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                    </transition>

                    <!-- Modal container -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                        <!-- Modal header -->
                        <div
                            class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:items-center sm:justify-between border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Edit Lesson Content
                            </h3>
                            <button @click="cancelEdit" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Modal content -->
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <!-- Success/Error messages -->
                            <div v-if="successMessage"
                                class="mb-4 p-3 bg-green-50 text-green-700 rounded-md flex items-start">
                                <i class="fas fa-check-circle mt-1 mr-3"></i>
                                <div>
                                    <p class="font-medium">Success</p>
                                    <p>{{ successMessage }}</p>
                                </div>
                            </div>
                            <div v-if="errorMessage"
                                class="mb-4 p-3 bg-red-50 text-red-700 rounded-md flex items-start">
                                <i class="fas fa-exclamation-circle mt-1 mr-3"></i>
                                <div>
                                    <p class="font-medium">Error</p>
                                    <p>{{ errorMessage }}</p>
                                </div>
                            </div>

                            <form>
                                <div class="space-y-4">
                                    <!-- Title field -->
                                    <div>
                                        <label for="lesson-title" class="block text-sm font-medium text-gray-700 mb-1">
                                            Lesson Title <span class="text-red-500">*</span>
                                        </label>
                                        <input id="lesson-title" v-model="form.title" type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-lime-500 focus:border-lime-500"
                                            required>
                                    </div>

                                    <!-- Content preview and upload -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Content
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Content preview -->
                                            <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                                                <div v-if="previewContent"
                                                    class="h-48 flex items-center justify-center relative">
                                                    <video v-if="previewContent.type === 1" controls
                                                        class="w-full h-full object-contain">
                                                        <source :src="previewContent.url" type="video/mp4">
                                                    </video>

                                                    <div v-else-if="previewContent.type === 2"
                                                        class="h-full w-full flex flex-col items-center justify-center p-4">
                                                        <div
                                                            :class="['p-4 rounded-full mb-3', contentTypeColors[previewContent.type]]">
                                                            <i
                                                                :class="['fas text-3xl', contentTypeIcons[previewContent.type]]"></i>
                                                        </div>
                                                        <p class="text-sm font-medium text-gray-700">{{
                                                            contentTypeLabels[previewContent.type] }}</p>
                                                    </div>

                                                    <img v-else-if="previewContent.type === 3" :src="previewContent.url"
                                                        class="w-full h-full object-contain">

                                                    <div v-else
                                                        class="h-full w-full flex flex-col items-center justify-center p-4">
                                                        <div
                                                            :class="['p-4 rounded-full mb-3', contentTypeColors[previewContent.type]]">
                                                            <i
                                                                :class="['fas text-3xl', contentTypeIcons[previewContent.type]]"></i>
                                                        </div>
                                                        <p class="text-sm font-medium text-gray-700">{{
                                                            contentTypeLabels[previewContent.type] }}</p>
                                                    </div>
                                                </div>
                                                <div v-else class="h-48 flex items-center justify-center text-gray-400">
                                                    No content available
                                                </div>
                                            </div>

                                            <!-- File upload -->
                                            <div>
                                                <div
                                                    class="relative h-full border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-lime-500 transition-colors">
                                                    <div
                                                        class="h-full flex flex-col items-center justify-center pointer-events-none">
                                                        <i
                                                            class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                                        <p class="text-sm text-gray-600 mb-1">
                                                            <span class="font-medium text-lime-600">Click to
                                                                upload</span> or drag and drop
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            Videos, PDFs, Images (Max 50MB)
                                                        </p>
                                                    </div>
                                                    <input type="file" @change="handleFileUpload('content_url', $event)"
                                                        accept="video/*,application/pdf,image/*"
                                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form actions -->
                                <div class="mt-6 flex justify-end space-x-3">
                                    <button type="button" @click="cancelEdit"
                                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
                                        Cancel
                                    </button>
                                    <button type="submit" @click="updateSelectedContent()" :disabled="isLoading"
                                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 disabled:opacity-70 disabled:cursor-not-allowed">
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
            </div>
        </transition>
    </teleport>

    <!-- Delete Confirmation Modal -->
    <teleport to="body">
        <transition name="modal-fade">
            <div v-if="showDeleteModal && deleteModule" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <!-- Modal container -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Delete Lesson
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to delete <span class="font-medium">"{{
                                                deleteModule?.title }}"</span>? This action cannot be undone.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="deleteSelectedContent"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-70"
                                :disabled="isProcessing">
                                <span v-if="isProcessing">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Deleting...
                                </span>
                                <span v-else>
                                    Delete
                                </span>
                            </button>
                            <button type="button" @click="showDeleteModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions for hover effects */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>