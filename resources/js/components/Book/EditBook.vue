<script setup>
import Axios from 'axios'
import { storeToRefs } from 'pinia';
import { useToast } from "vue-toastification";
import { ref, defineProps, defineEmits, onMounted, watch } from 'vue'
import DescriptionEditor from "@/components/Book/DescriptionEditor.vue";

import { useInstructorStore } from "@/store/useInstructorStore";

const InstructorStore = useInstructorStore();
const { booksAdmin } = storeToRefs(InstructorStore);

const props = defineProps({
    selectedbook: {
        type: Object,
        required: true,
        validator: (book) => book && book.id
    }, 
    editingBookId: {
        type: Number,
        default: null
    }
})

const emit = defineEmits(['cancel-edit'])
const toast = useToast();
const error = ref("");
const loading = ref(false);
const introVideoPreview = ref(null);
const coverPagePreview = ref(null);
const errors = ref({});
 
const cancelEdit = () => {
    emit('cancel-edit')
}

function isFileObject(obj) {
    return obj instanceof File && typeof obj.name === 'string' && typeof obj.size === 'number';
}

const onFileChange = (field, event) => {
    errors.value[field] = "";
    const file = event.target.files[0];

    if (!file) return;
 
    // Validate file types
    if (field === "cover_page_url" && !file.type.match(/image.*/)) {
        errors.value[field] = "Please upload an image file (JPEG/PNG)";
        return;
    }

    if (field === "intro_vedio" && !file.type.match(/video.*/)) {
        errors.value[field] = "Please upload a video file (MP4)";
        return;
    }

    props.selectedbook[field] = file;

    if (field === 'intro_vedio') {
        introVideoPreview.value = URL.createObjectURL(file);
    } else if (field === 'cover_page_url') {
        coverPagePreview.value = URL.createObjectURL(file);
    }
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    if (!props.selectedbook.title?.trim()) {
        errors.value.title = "Title is required";
        isValid = false;
    }

    if (!props.selectedbook.auther?.trim()) {
        errors.value.auther = "Author is required";
        isValid = false;
    }

    if (!props.selectedbook.price || props.selectedbook.price <= 0) {
        errors.value.price = "Valid price is required";
        isValid = false;
    }

    if (!props.selectedbook.eddition || props.selectedbook.eddition <= 0) {
        errors.value.eddition = "Valid edition number is required";
        isValid = false;
    }

    if (!props.selectedbook.publish_date) {
        errors.value.publish_date = "Publish date is required";
        isValid = false;
    }

    if (!props.selectedbook.description?.trim()) {
        errors.value.description = "Description is required";
        isValid = false;
    }

    if (!props.selectedbook.language?.trim()) {
        errors.value.language = "Language is required";
        isValid = false;
    }

    return isValid;
};

const handleUpdateCourse = async () => {
    if (!validateForm()) return;

    loading.value = true;
    error.value = "";

    const formData = new FormData();
    formData.append("title", props.selectedbook.title);
    formData.append("auther", props.selectedbook.auther);
    formData.append("price", props.selectedbook.price);
    formData.append("eddition", props.selectedbook.eddition);
    formData.append("discount", props.selectedbook.discount || 0);
    formData.append("publish_date", props.selectedbook.publish_date);
    formData.append("description", props.selectedbook.description);
    formData.append("language", props.selectedbook.language); 

   if (isFileObject(props.selectedbook.cover_page_url)) {
        formData.append("cover_page_url", props.selectedbook.cover_page_url);
    } else {
        formData.delete("cover_page_url");
    }

    if (isFileObject(props.selectedbook.file_url)) {
        formData.append("file_url", props.selectedbook.file_url);
    } else {
        formData.delete("file_url");
    }

    if (isFileObject(props.selectedbook.intro_vedio)) {
        formData.append("intro_vedio", props.selectedbook.intro_vedio);
    } else {
        formData.delete("intro_vedio");
    }

    try {
        const response = await Axios.post( `/api/books/update-books/${props.selectedbook.id}`,
            formData,
            { headers: { "Content-Type": "multipart/form-data" } }
        );

        booksAdmin.value = booksAdmin.value.filter(book => book.id !== props.selectedbook.id);
        booksAdmin.value = [
            response.data.data,
            ... booksAdmin.value
        ];

        toast.success("Book updated successfully!", { position: "top-right" }); 
        cancelEdit();
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to update book.";
        toast.error(error.value, { position: "top-right" });

        if (err.response?.data?.errors) {
            errors.value = { ...errors.value, ...err.response.data.errors };
        }
    } finally {
        loading.value = false;
    }
};

const updateDescription = (description) => {
    props.selectedbook.description = description;
};

// Utility function to deeply parse the tag
const safeParseTag = (rawTag) => {
    try {
        let tag = rawTag
        while (typeof tag === 'string') {
            tag = JSON.parse(tag)
        }
        if (Array.isArray(tag)) return tag.join(', ')
        return typeof tag === 'string' ? tag : ''
    } catch (err) {
        return typeof rawTag === 'string' ? rawTag : ''
    }
} 

</script>

<template>
    <div class="mx-auto p-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Error Message -->
        <div v-if="error" class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ error }}</span>
            </div>
        </div>

        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Edit Book</h2>

        <form @submit.prevent="handleUpdateCourse" class="space-y-8">
            <!-- 60/40 layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- LEFT: Uploads (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Title
                        </label>
                        <input v-model="props.selectedbook.title" type="text"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.title }" placeholder="Book title" />
                        <p v-if="errors.title" class="mt-1 text-red-500 text-sm">{{ errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Cover Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium text-sm mb-2">
                            Cover Page
                        </label>
                        <div v-if="coverPagePreview || props.selectedbook?.cover_page_url" class="mb-3">
                            <img :src="coverPagePreview || props.selectedbook.cover_page_url" alt="Book Cover Preview"
                                class="w-full max-w-md h-48 object-contain rounded-md border border-gray-200" />
                        </div>
                        <div class="relative">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:bg-gray-50 transition"
                                :class="{ 'border-red-500': errors.cover_page_url }">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i class="fas fa-image text-3xl mb-2"></i>
                                    <p class="text-sm font-medium">Click to upload cover image</p> 
                                </div>
                                <input type="file" @change="onFileChange('cover_page_url', $event)"
                                    accept="image/jpeg,image/png"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                            </div>
                            <p v-if="errors.cover_page_url" class="mt-1 text-red-500 text-sm">
                                {{ errors.cover_page_url }}
                            </p>
                        </div>
                    </div>

                    <!-- Intro Video Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium text-sm mb-2">
                            Intro Video
                        </label>
                        <div v-if="introVideoPreview || props.selectedbook?.intro_video_url" class="mb-3">
                            <video controls class="w-full max-w-md h-48 rounded-md border border-gray-200 bg-black">
                                <source :src="introVideoPreview || props.selectedbook.intro_video_url"
                                    type="video/mp4" />
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        <div class="relative">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:bg-gray-50 transition"
                                :class="{ 'border-red-500': errors.intro_vedio }">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i class="fas fa-video text-3xl mb-2"></i>
                                    <p class="text-sm font-medium">Click to upload intro video</p> 
                                </div>
                                <input type="file" @change="onFileChange('intro_vedio', $event)" accept="video/mp4"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                            </div>
                            <p v-if="errors.intro_vedio" class="mt-1 text-red-500 text-sm">
                                {{ errors.intro_vedio }}
                            </p>
                        </div>
                    </div>
                </div>
                    <!-- Book File Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium text-sm mb-2">
                            Book File
                        </label>
                        <div class="relative">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:bg-gray-50 transition"
                                :class="{ 'border-red-500': errors.file_url }">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i class="fas fa-file-pdf text-3xl mb-2"></i>
                                    <p class="text-sm font-medium">Click to upload book file</p> 
                                </div>
                                <input type="file" @change="onFileChange('file_url', $event)" accept=".pdf"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                            </div>
                            <p v-if="errors.file_url" class="mt-1 text-red-500 text-sm">
                                {{ errors.file_url }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Fields (1/3) -->
                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Author
                        </label>
                        <input v-model="props.selectedbook.auther" type="text"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.auther }" placeholder="Author name" />
                        <p v-if="errors.auther" class="mt-1 text-red-500 text-sm">{{ errors.auther }}</p>
                    </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Price (Birr)
                            </label>
                            <input v-model.number="props.selectedbook.price" type="number" min="0" step="0.01"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                                :class="{ 'border-red-500': errors.price }" placeholder="0.00" />
                            <p v-if="errors.price" class="mt-1 text-red-500 text-sm">{{ errors.price }}</p>
                        </div> 

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Edition
                            </label>
                            <input v-model.number="props.selectedbook.eddition" type="number" min="1"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                                :class="{ 'border-red-500': errors.eddition }" placeholder="1" />
                            <p v-if="errors.eddition" class="mt-1 text-red-500 text-sm">{{ errors.eddition }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Publish Date
                            </label>
                            <input v-model="props.selectedbook.publish_date" type="date"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                                :class="{ 'border-red-500': errors.publish_date }" />
                            <p v-if="errors.publish_date" class="mt-1 text-red-500 text-sm">{{ errors.publish_date }}
                            </p>
                        </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Language
                        </label>
                        <select v-model="props.selectedbook.language"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.language }">
                            <option value="English">English</option>
                            <option value="Amharic">Amharic</option>
                            <option value="Other">Other</option>
                        </select>
                        <p v-if="errors.language" class="mt-1 text-red-500 text-sm">{{ errors.language }}</p>
                    </div>  
                </div>
            </div>

            <!-- DESCRIPTION (Full Width) -->
            <div>
                <label class="block text-gray-700 font-medium text-sm mb-2">
                    Book Description
                </label>
                <DescriptionEditor :selected="props.selectedbook" @update-description="updateDescription" />
                <p v-if="errors.description" class="mt-1 text-red-500 text-sm">{{ errors.description }}</p>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex justify-end gap-4 pt-6">
                <button type="button" @click="cancelEdit"
                    class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-medium">
                    Cancel
                </button>
                <button type="submit" :disabled="loading"
                    class="px-6 py-2.5 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition font-medium disabled:opacity-70 disabled:cursor-not-allowed">
                    <span v-if="loading">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Updating...
                    </span>
                    <span v-else>
                        <i class="fas fa-save mr-2"></i> Update Book
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.border-dashed:hover {
    border-color: #84cc16;
    background-color: #f7fee7;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>