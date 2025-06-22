<script setup>
import { ref } from "vue";
import Axios from "axios";
import { storeToRefs } from "pinia";
import { useToast } from "vue-toastification";
import AddBookDescription from "@/components/Book/AddBookDescription";

import { useInstructorStore } from "@/store/useInstructorStore";

const InstructorStore = useInstructorStore();
const { analytics, booksAdmin } = storeToRefs(InstructorStore);
const toast = useToast();

const newBook = ref({
    title: "",
    auther: "",
    price: null,
    eddition: null,
    publish_date: "",
    description: "",
    page_number: "",
    language: "English",
    file_format: "pdf",
    cover_page_url: null,
    file_url: null,
    intro_vedio: null,
});

const coverPreview = ref(null);
const videoPreview = ref(null);
const loading = ref(false);
const errors = ref({});

const onFileChange = (field, event) => {
    errors.value[field] = "";
    const file = event.target.files[0];

    if (!file) return;

    // Validate file types
    if (field === "cover_page_url" && !file.type.match(/image.*/)) {
        errors.value[field] = "Please upload an image file";
        return;
    }

    if (field === "intro_vedio" && !file.type.match(/video.*/)) {
        errors.value[field] = "Please upload a video file";
        return;
    }

    newBook.value[field] = file;

    if (field === "cover_page_url") {
        coverPreview.value = URL.createObjectURL(file);
    }
    if (field === "intro_vedio") {
        videoPreview.value = URL.createObjectURL(file);
    }
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    if (!newBook.value.title.trim()) {
        errors.value.title = "Title is required";
        isValid = false;
    }

    if (!newBook.value.auther.trim()) {
        errors.value.auther = "Author is required";
        isValid = false;
    }

    if (!newBook.value.price || newBook.value.price <= 0) {
        errors.value.price = "Valid price is required";
        isValid = false;
    }

    if (!newBook.value.eddition || newBook.value.eddition <= 0) {
        errors.value.eddition = "Valid edition number is required";
        isValid = false;
    }

    if (!newBook.value.publish_date) {
        errors.value.publish_date = "Publish date is required";
        isValid = false;
    }

    if (!newBook.value.description.trim()) {
        errors.value.description = "Description is required";
        isValid = false;
    }

    if (!newBook.value.page_number) {
        errors.value.page_number = "Page number is required";
        isValid = false;
    }

    if (!newBook.value.cover_page_url) {
        errors.value.cover_page_url = "Cover image is required";
        isValid = false;
    }

    if (!newBook.value.file_url) {
        errors.value.file_url = "Book file is required";
        isValid = false;
    }

    return isValid;
};

const addBook = async () => {
    if (!validateForm()) return;

    loading.value = true;

    const formData = new FormData();
    formData.append("title", newBook.value.title);
    formData.append("auther", newBook.value.auther);
    formData.append("price", newBook.value.price);
    formData.append("eddition", newBook.value.eddition);
    formData.append("publish_date", newBook.value.publish_date);
    formData.append("description", newBook.value.description);
    formData.append("page_number", newBook.value.page_number);
    formData.append("language", newBook.value.language);
    formData.append("file_format", newBook.value.file_format);
    formData.append("cover_page_url", newBook.value.cover_page_url);
    formData.append("file_url", newBook.value.file_url);

    if (newBook.value.intro_vedio) {
        formData.append("intro_vedio", newBook.value.intro_vedio);
    }

    try {
        await Axios.post("/api/books/books", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        }).then(res => {
            booksAdmin.value = [
                res.data.data,
                ... booksAdmin.value,
            ];

            analytics.value.total += 1;
            analytics.value.newToday += 1;
        });

        toast.success("Book added successfully!", { position: "top-right" });
        resetForm();
    } catch (err) {
        const message = err.response?.data?.message || "Failed to add book";
        toast.error(message, { position: "top-right" });

        if (err.response?.data?.errors) {
            errors.value = { ...errors.value, ...err.response.data.errors };
        }
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    newBook.value = {
        title: "",
        auther: "",
        price: null,
        eddition: null,
        publish_date: "",
        description: "",
        page_number: "",
        language: "English",
        file_format: "pdf",
        cover_page_url: null,
        file_url: null,
        intro_vedio: null,
        tag: "",
    };
    coverPreview.value = null;
    videoPreview.value = null;
    errors.value = {};
};

const cancelAdd = () => {
    if (confirm("Are you sure you want to cancel? All changes will be lost.")) {
        resetForm();
        window.history.back();
    }
};

function goBack() {
    window.history.back();
}
</script>

<template>
    <div class="w-full mx-auto p-4 md:p-6 bg-white rounded-lg shadow-sm">
        <button @click="goBack()"
            class="flex items-center justify-center gap-4 text-gray-600 hover:text-lime-700 transition-colors mb-6 group"
            aria-label="Go back">
            <i class="fas fa-arrow-left self-center pb-6 text-lg group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-2xl font-bold self-center text-lime-700 mb-6">Add New Book</span>
        </button>

        <form @submit.prevent="addBook" class="space-y-8">
            <!-- 60/40 layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- LEFT: Uploads (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Title
                        </label>
                        <input v-model="newBook.title" type="text"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.title }" placeholder="Book title" />
                        <p v-if="errors.title" class="mt-1 text-red-500 text-sm">{{ errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Cover Upload -->
                        <div>
                            <label class="block text-gray-700 font-medium text-sm mb-2">
                                Upload Cover Page
                            </label>
                            <div v-if="coverPreview" class="mb-3">
                                <img :src="coverPreview" alt="Cover Preview"
                                    class="w-full max-w-md max-h-64 object-contain rounded-md border border-gray-200" />
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
                                Upload Intro Video
                            </label>
                            <div v-if="videoPreview" class="mb-3">
                                <video controls
                                    class="w-full max-w-md max-h-64 rounded-md border border-gray-200 bg-black">
                                    <source :src="videoPreview" type="video/mp4" />
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
                            Upload Book File
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
                        <input v-model="newBook.auther" type="text"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.auther }" placeholder="Author name" />
                        <p v-if="errors.auther" class="mt-1 text-red-500 text-sm">{{ errors.auther }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Price In ETB
                        </label>
                        <input v-model.number="newBook.price" type="number" min="0" step="0.01"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.price }" placeholder="0.00" />
                        <p v-if="errors.price" class="mt-1 text-red-500 text-sm">{{ errors.price }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Edition
                        </label>
                        <input v-model.number="newBook.eddition" type="number" min="1"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.eddition }" placeholder="1" />
                        <p v-if="errors.eddition" class="mt-1 text-red-500 text-sm">{{ errors.eddition }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Page Number
                        </label>
                        <input v-model.number="newBook.page_number" type="number" min="1"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.page_number }" placeholder="100" />
                        <p v-if="errors.page_number" class="mt-1 text-red-500 text-sm">{{ errors.page_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Publish Date
                        </label>
                        <input v-model="newBook.publish_date" type="date"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.publish_date }" />
                        <p v-if="errors.publish_date" class="mt-1 text-red-500 text-sm">{{ errors.publish_date }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Language
                        </label>
                        <select v-model="newBook.language"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500">
                            <option value="English">English</option>
                            <option value="Amharic">Amharic</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- DESCRIPTION (Full Width) -->
            <div>
                <label class="block text-gray-700 font-medium text-sm mb-2">
                    Book Description
                </label>
                <AddBookDescription v-model="newBook.description" />
                <p v-if="errors.description" class="mt-1 text-red-500 text-sm">{{ errors.description }}</p>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex justify-end gap-4 pt-6">
                <button type="button" @click="cancelAdd"
                    class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-medium">
                    Cancel
                </button>
                <button type="submit" :disabled="loading"
                    class="px-6 py-2.5 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition font-medium disabled:opacity-70 disabled:cursor-not-allowed">
                    <span v-if="loading">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                    </span>
                    <span v-else>
                        <i class="fas fa-plus-circle mr-2"></i> Add Book
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