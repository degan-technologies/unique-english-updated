<script setup>
import Axios from 'axios';
import Popper from "vue3-popper";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from 'vue-router';
import Spinner from "@/components/Layout/Spinner";
import { ref, computed, watch, onMounted } from 'vue';

import { useInstructorStore } from "@/store/useInstructorStore";
import MetaDataForm from "@/components/Quize/MetaDataForm.vue";

const InstructorStore = useInstructorStore();
const { readlessonPdfTab, selectedLesson, addNewLesson } = storeToRefs(InstructorStore);

const route = useRoute();
const router = useRouter();
// Constants
const CONTENT_TYPES = {
    1: { label: 'Video', icon: 'fa-play-circle', color: 'bg-blue-100 text-blue-600' },
    2: { label: 'PDF', icon: 'fa-file-pdf', color: 'bg-red-100 text-red-600' },
    3: { label: 'Image', icon: 'fa-image', color: 'bg-purple-100 text-purple-600' },
    4: { label: 'Document', icon: 'fa-file-alt', color: 'bg-gray-100 text-gray-600' }
};
const rowsPerPageOptions = [5, 10, 15, 20];
const gridView = ref(false);
const addExam = ref(false)

const props = defineProps({ moduleId: Number });
const emit = defineEmits(['cancelEdit', 'onAddModuleContent']);

// Refs 
const isLoading = ref(false);
const loadingTosave = ref(false);
const isProcessing = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const selectedContentTodelete = ref(null);
const rowsPerPage = ref(10);
const currentPage = ref(1);
const loading = ref(true);

const moduleContents = ref([]);
const pagination = ref({});
const totalPages = ref(0);

const form = ref({
    title: "",
    content_type: 1,
    content_url: null,
    thumbnail_url: null,
    create_content_url: null,
    create_thumbnail_url: null,
});

// Computed
const selectedContent = ref(null);
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


// Methods
const fetchModuleContents = async (page = currentPage.value) => {
    try {
        loading.value = true;
        const res = await Axios.get(`/api/courses/module-contents/${props.moduleId}?page=${page}`, {
            params: {
                rowsPerPageOption: rowsPerPage.value
            }
        });

        moduleContents.value = res.data.data;
        pagination.value = res.data.pagination;
        totalPages.value = res.data.pagination.last_page;
        currentPage.value = res.data.pagination.current_page;
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Failed to fetch module contents';
    } finally {
        loading.value = false;
    }
};

function onNextPage() {
    if (currentPage.value == totalPages.value) return;

    fetchModuleContents(currentPage.value + 1);
}

function onPreviousPage() {
    if (currentPage.value <= 1) return;

    fetchModuleContents(currentPage.value - 1);
}

function coursePerPage(amount) {
    rowsPerPage.value = amount;
    fetchModuleContents(currentPage.value);
}

const handleFileUpload = (field, event) => {
    errorMessage.value = '';
    const file = event.target.files[0];

    if (field === 'content_url') {
        form.value.content_url = file;
        form.value.create_content_url = URL.createObjectURL(file);
        form.value.content_type = getContentType(file);
    } else {
        form.value.content_url = null;
    }
};

const getContentType = (file) => {
    if (!file) return 1;
    const type = file.type || '';
    if (type.startsWith('video/')) return 1;
    if (type.startsWith('application/pdf')) return 2;
    if (type.startsWith('image/')) return 3;
    return 4;
};

function isFileObject(obj) {
    return obj instanceof File && typeof obj.name === 'string' && typeof obj.size === 'number';
}

const updateSelectedContent = async () => {
    if (!form.value.title) {
        errorMessage.value = 'Title is required';
        return;
    }
 
    loadingTosave.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const formData = new FormData();
        formData.append("title", form.value.title);

        if (isFileObject(form.value.content_url)) {
            formData.append("content_url", form.value.content_url);
        } else {
            formData.delete("content_url");
        }

        const contentIdToUpdate = selectedContent.value.id;

        const response = await Axios.post(`/api/courses/update-content/${contentIdToUpdate}`,
            formData
        );

        moduleContents.value = moduleContents.value.map(content => {
            if (content.id === contentIdToUpdate) {
                return response.data.data;
            }
            return content;
        })

        setTimeout(() => {
            selectedContent.value = null;
            successMessage.value = '';
        }, 1500);
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Failed to update content';
    }  finally  {
        loadingTosave.value = false
    }
};

const openDeleteModal = (content) => {
    selectedContentTodelete.value = content;
};

const deleteSelectedContent = async (contentIdToDelete) => {
    isProcessing.value = true;

    try {
        await Axios.delete(`/api/courses/content/${contentIdToDelete}`);
        moduleContents.value = moduleContents.value.filter(content => content.id !== contentIdToDelete);
        selectedContentTodelete.value = null;
    } catch (err) {
        errorMessage.value = err.response?.data?.message;
    } finally {
        isProcessing.value = false;
    }
};

const openPdf = (content) => {
    selectedLesson.value = content;
    router.push({
        name: "instructor",
        query: {
            currentTab: route.query.currentTab,
            currentActiveTab: readlessonPdfTab.value,
        },
    })
};

const editSelectedContent = (content) => {
    if (!content.id) return;
    selectedContent.value = content;
    form.value = { ...selectedContent.value };
};

watch(() => addNewLesson.value, () => {
    return moduleContents.value = [
        addNewLesson.value,
        ...moduleContents.value
    ];
});

// Lifecycle hooks
onMounted(() => {
    fetchModuleContents();
});
</script>

<template>
    <!-- Content Card -->
    <div v-if="isLoading">
        <Spinner />
    </div>
    <div v-else>
        <div>
            <div class="my-6 gap-4">
                <button @click="gridView = !gridView" class="text-black p-2 rounded-full transition duration-200">
                    <i :class="gridView
                        ? 'fa-solid fa-list'
                        : 'fa-solid fa-th-large'
                        " class="text-xl"></i>
                </button>
                <button type="button" @click="addExam = !addExam"
                    :class="{
                        'bg-lime-500 text-white hover:bg-lime-600': addExam,
                    }"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    {{ addExam ? 'Close' : 'Exams' }}
                </button>
            </div>
        </div>

        <div v-if="addExam">
            <MetaDataForm v-if="addExam" :moduleID="moduleId" />
        </div>
        <div v-else>
            <div v-if="moduleContents.length">
                <!-- grid view -->
                <div v-if="gridView" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                    <div v-for="(content, index) in moduleContents" :key="index"
                        class="w-full h-full bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
                        <!-- View Mode -->
                        <div v-if="!selectedContent" class="h-full flex flex-col">
                            <!-- Content Preview -->
                            <div class="relative aspect-video bg-gray-100 h-48">
                                <template v-if="content.content_type === 1">
                                    <video controls class="w-full h-full object-contain"
                                        :poster="content.thumbnail_url">
                                        <source :src="content.course_content_url" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </template>

                                <template v-else-if="content.content_type === 2">
                                    <div class="h-full flex flex-col items-center justify-center p-4">
                                        <div
                                            :class="['p-4 rounded-full mb-3', CONTENT_TYPES[content.content_type].color]">
                                            <i :class="['fas text-2xl', CONTENT_TYPES[content.content_type].icon]"></i>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700">
                                            {{ CONTENT_TYPES[content.content_type].label }}
                                        </p>
                                        <button @click="openPdf(content)"
                                            class="mt-2 text-xs text-lime-500 hover:underline inline-flex items-center">
                                            <i class="fas fa-external-link-alt mr-1"></i> Open File
                                        </button>
                                    </div>
                                </template>

                                <img v-else-if="content.content_type === 3" :src="content.course_content_url"
                                    class="w-full h-full object-cover" :alt="content.title">

                                <div v-else class="h-full flex flex-col items-center justify-center p-4">
                                    <div :class="['p-4 rounded-full mb-3', CONTENT_TYPES[content.content_type].color]">
                                        <i :class="['fas text-2xl', CONTENT_TYPES[content.content_type].icon]"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-700">
                                        {{ CONTENT_TYPES[content.content_type].label }}
                                    </p>
                                </div>

                                <div
                                    class="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                                    {{ CONTENT_TYPES[content.content_type].label }}
                                </div>
                            </div>

                            <!-- Content Details -->
                            <div class="p-4 flex-grow flex flex-col">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2">{{ content.title }}
                                </h3>
                                <div class="mt-auto flex justify-between items-center">
                                    <span class="text-xs text-gray-400">
                                        {{ content.created_at }}
                                    </span>

                                    <div class="flex space-x-2">
                                        <button @click="editSelectedContent(content)"
                                            class="p-2 text-gray-500 hover:text-blue-500 hover:bg-blue-50 rounded-full transition-colors"
                                            aria-label="Edit content">
                                            <i class="fas fa-pencil-alt text-sm"></i>
                                        </button>
                                        <button @click="openDeleteModal(content)"
                                            class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors"
                                            aria-label="Delete content">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- list view -->
                <div v-else class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <!-- List Header -->
                    <div
                        class="grid grid-cols-12 gap-4 px-4 py-3 bg-gray-50 border-b border-gray-200 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="col-span-5">Content</div>
                        <div class="col-span-2">Type</div>
                        <div class="col-span-3">Date Added</div>
                        <div class="col-span-2 text-right">Actions</div>
                    </div>

                    <!-- List Items -->
                    <div v-for="(content, index) in moduleContents" :key="index"
                        class="border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition-colors duration-150">
                        <div class="grid grid-cols-12 gap-4 px-4 py-3 items-center">
                            <!-- Content Title and Preview -->
                            <div class="col-span-5 flex items-center">
                                <div
                                    class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md overflow-hidden mr-3 flex items-center justify-center">
                                    <template v-if="content.content_type === 1">
                                        <i class="fas fa-video text-gray-400"></i>
                                    </template>
                                    <template v-else-if="content.content_type === 2">
                                        <i class="fas fa-file-pdf text-red-400"></i>
                                    </template>
                                    <template v-else-if="content.content_type === 3">
                                        <img :src="content.course_content_url" class="w-full h-full object-cover"
                                            :alt="content.title">
                                    </template>
                                    <template v-else>
                                        <i class="fas fa-link text-blue-400"></i>
                                    </template>
                                </div>
                                <h3 class="text-sm font-medium text-gray-800 truncate max-w-[30ch]">{{ content.title }}</h3>
                            </div>

                            <!-- Content Type -->
                            <div class="col-span-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="CONTENT_TYPES[content.content_type].color + ' bg-opacity-20'">
                                    {{ CONTENT_TYPES[content.content_type].label }}
                                </span>
                            </div>

                            <!-- Date Added -->
                            <div class="col-span-3 text-sm text-gray-500">
                                {{ content.created_at }}
                            </div>

                            <!-- Actions -->
                            <div class="col-span-2 flex justify-end space-x-2">
                                <button v-if="content.content_type === 2" @click="openPdf(content)"
                                    class="p-1.5 text-gray-500 hover:text-lime-500 hover:bg-lime-50 rounded transition-colors"
                                    aria-label="Open PDF">
                                    <i class="fas fa-external-link-alt text-sm"></i>
                                </button>
                                <button @click="editSelectedContent(content)"
                                    class="p-1.5 text-gray-500 hover:text-blue-500 hover:bg-blue-50 rounded transition-colors"
                                    aria-label="Edit content">
                                    <i class="fas fa-pencil-alt text-sm"></i>
                                </button>
                                <button @click="openDeleteModal(content)"
                                    class="p-1.5 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
                                    aria-label="Delete content">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div v-else class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg mt-4">
                <i class="fas fa-file-alt text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 mb-4">No content in this module</p>
                <button @click="emit('onAddModuleContent')"
                    class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add First Lesson
                </button>
            </div>
            <!-- Pagination Footer -->
            <div class="p-4 bg-white flex flex-row items-center justify-between">
                <!-- Rows Per Page Selector -->

                <Popper>
                    <div class="flex flex-row md:gap-2">
                        <span class="hidden md:flex text-sm text-gray-600">rows per page:</span>
                        <span class="text-sm font-medium">{{ rowsPerPage }}</span>
                        <i class="fa-solid fa-chevron-down text-lg"></i>
                    </div>
                    <template #content>
                        <div v-for="option in rowsPerPageOptions" :key="option" @click="coursePerPage(option)"
                            class="border w-32 block border-gray-200 rounded-md px-2 py-2 text-sm cursor-pointer transition-all duration-200"
                            :class="{
                                'bg-gray-300 text-white font-bold':
                                    rowsPerPage === option,
                                'bg-white text-gray-700 hover:bg-gray-200':
                                    rowsPerPage !== option,
                            }">
                            {{ option }}
                        </div>
                    </template>
                </Popper>

                <!-- Pagination Controls -->
                <div class="flex items-center space-x-3">
                    <button @click="onPreviousPage()" :disabled="currentPage === 1"
                        class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        Prev
                    </button>

                    <span class="text-sm text-gray-600">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>

                    <button @click="onNextPage()" :disabled="currentPage === totalPages"
                        class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <teleport to="body">
        <transition name="modal-fade">
            <div v-if="selectedContent" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

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
                            <button @click="selectedContent = null"
                                class="text-gray-400 hover:text-gray-500 transition-colors">
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
                                                            :class="['p-4 rounded-full mb-3', CONTENT_TYPES[previewContent.type].color]">
                                                            <i
                                                                :class="['fas text-3xl', CONTENT_TYPES[previewContent.type].icon]"></i>
                                                        </div>
                                                        <p class="text-sm font-medium text-gray-700">
                                                            {{ CONTENT_TYPES[previewContent.type].label }}
                                                        </p>
                                                    </div>

                                                    <img v-else-if="previewContent.type === 3" :src="previewContent.url"
                                                        class="w-full h-full object-contain">

                                                    <div v-else
                                                        class="h-full w-full flex flex-col items-center justify-center p-4">
                                                        <div
                                                            :class="['p-4 rounded-full mb-3', CONTENT_TYPES[previewContent.type].color]">
                                                            <i
                                                                :class="['fas text-3xl', CONTENT_TYPES[previewContent.type].icon]"></i>
                                                        </div>
                                                        <p class="text-sm font-medium text-gray-700">
                                                            {{ CONTENT_TYPES[previewContent.type].label }}
                                                        </p>
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
                                                                upload</span>
                                                            or drag and drop
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
                                    <button type="button" @click="selectedContent = null"
                                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
                                        Cancel
                                    </button>
                                    <button @click="updateSelectedContent" type="button" :disabled="loadingTosave"
                                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 disabled:opacity-70 disabled:cursor-not-allowed">
                                        <span v-if="loadingTosave">
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
            <div v-if="selectedContentTodelete" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <!-- Modal container -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class=" bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 align-middle max-w-lg w-full">
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
                                                selectedContentTodelete?.title }}"</span>? This action cannot be undone.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="deleteSelectedContent(selectedContentTodelete?.id)"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-70"
                                :disabled="isProcessing">
                                <span v-if="isProcessing">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Deleting...
                                </span>
                                <span v-else>
                                    Delete
                                </span>
                            </button>
                            <button type="button" @click="selectedContentTodelete = null"
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

.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>