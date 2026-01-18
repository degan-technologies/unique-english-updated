<script setup>
import AddBookDescription from "@/components/Book/AddBookDescription";
import Axios from "axios";
import { storeToRefs } from "pinia";
import { ref } from "vue";
import { useToast } from "vue-toastification";

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
    intro_video: null,
});

const uploadProgress = ref(0);
const isProcessingThumbnail = ref(0);
const isUploadPdf = ref(0);

const coverPreview = ref(null);
const videoPreview = ref(null);
const loading = ref(false);
const errors = ref({});

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

    if (!newBook.value.intro_video) {
        errors.value.intro_video = "Intro video is required";
        isValid = false;
    }

    return isValid;
};

async function addBook() {
    if (!validateForm()) return;

    loading.value = true;

    // Send only the data, not files (files are already uploaded)
    const bookData = {
        title: newBook.value.title,
        auther: newBook.value.auther,
        price: newBook.value.price,
        eddition: newBook.value.eddition,
        publish_date: newBook.value.publish_date,
        description: newBook.value.description,
        page_number: newBook.value.page_number,
        language: newBook.value.language,
        file_format: newBook.value.file_format,
        cover_page_url: newBook.value.cover_page_url,
        file_url: newBook.value.file_url,
        intro_video: newBook.value.intro_video,
    };

    try {
        const response = await Axios.post("/api/books/books", bookData, {
            headers: { "Content-Type": "application/json" },
        });

        booksAdmin.value = [response.data.data, ...booksAdmin.value];

        analytics.value.total += 1;
        analytics.value.newToday += 1;

        toast.success("Book added successfully!", { position: "top-right" });
        loading.value = false;
        resetForm();
    } catch (err) {
        loading.value = false;
        const message = err.response?.data?.message || "Failed to add book";
        toast.error(message, { position: "top-right" });

        if (err.response?.data?.errors) {
            errors.value = { ...errors.value, ...err.response.data.errors };
        }
    }
}

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
        intro_video: null,
        file_url: null,
        page_number: null,
    };

    isProcessingThumbnail.value = 0;
    uploadProgress.value = 0;
    isUploadPdf.value = 0;
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

function uploadIntroVideo(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Check file size (max 200MB)
    const maxSize = 200 * 1024 * 1024; // 200MB in bytes
    if (file.size > maxSize) {
        errors.value["intro_video"] = "File size must be less than 200MB";
        event.target.value = "";
        return;
    }

    const formData = new FormData();
    formData.append("intro_video", file);

    errors.value["intro_video"] = "";
    uploadProgress.value = 1;

    Axios.post("/api/books/upload-intro-video", formData, {
        headers: {
            "Content-Type": "multipart/form-data",
        },
        onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
                let percent = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total,
                );
                uploadProgress.value = percent > 99 ? 99 : percent;
            }
        },
        timeout: 600000, // 10 minutes timeout
    })
        .then((res) => {
            newBook.value.intro_video = res.data.file_path;
            uploadProgress.value = 100;
        })
        .catch((error) => {
            console.error("Video Upload Error:", error);
            uploadProgress.value = 0;

            if (error.code === "ECONNABORTED") {
                errors.value["intro_video"] =
                    "Upload timeout - file may be too large";
            } else if (error.response?.status === 413) {
                errors.value["intro_video"] =
                    "File too large - reduce file size and try again";
            } else if (error.response?.data?.errors) {
                errors.value["intro_video"] =
                    error.response.data.errors.intro_video?.[0] ||
                    "Upload failed";
            } else if (error.response?.data?.message) {
                errors.value["intro_video"] = error.response.data.message;
            } else {
                errors.value["intro_video"] =
                    "Upload failed - please try again";
            }
            event.target.value = "";
        });
}

function uploadThumbnail(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Check file size (max 20MB)
    const maxSize = 20 * 1024 * 1024; // 20MB in bytes
    if (file.size > maxSize) {
        errors.value["cover_page_url"] = "Image size must be less than 20MB";
        event.target.value = "";
        return;
    }

    const formData = new FormData();
    formData.append("cover_page_url", file);

    errors.value["cover_page_url"] = "";
    isProcessingThumbnail.value = 1;

    Axios.post("/api/books/upload-cover-image", formData, {
        headers: {
            "Content-Type": "multipart/form-data",
        },
        onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
                let percent = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total,
                );
                isProcessingThumbnail.value = percent > 99 ? 99 : percent;
            }
        },
        timeout: 300000, // 5 minutes timeout
    })
        .then((res) => {
            newBook.value.cover_page_url = res.data.file_path;
            isProcessingThumbnail.value = 100;
        })
        .catch((error) => {
            console.error("Image Upload Error:", error);
            isProcessingThumbnail.value = 0;

            if (error.code === "ECONNABORTED") {
                errors.value["cover_page_url"] =
                    "Upload timeout - file may be too large";
            } else if (error.response?.status === 413) {
                errors.value["cover_page_url"] =
                    "File too large - reduce file size and try again";
            } else if (error.response?.data?.errors) {
                errors.value["cover_page_url"] =
                    error.response.data.errors.cover_page_url?.[0] ||
                    "Upload failed";
            } else if (error.response?.data?.message) {
                errors.value["cover_page_url"] = error.response.data.message;
            } else {
                errors.value["cover_page_url"] =
                    "Upload failed - please try again";
            }
            event.target.value = "";
        });
}

function uploadPdfFile(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Check file size (max 100MB)
    const maxSize = 100 * 1024 * 1024; // 100MB in bytes
    if (file.size > maxSize) {
        errors.value["file_url"] = "PDF size must be less than 100MB";
        event.target.value = "";
        return;
    }

    const formData = new FormData();
    formData.append("file_url", file);

    errors.value["file_url"] = "";
    isUploadPdf.value = 1;

    Axios.post("/api/books/upload-pdf", formData, {
        headers: {
            "Content-Type": "multipart/form-data",
        },
        onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
                let percent = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total,
                );
                isUploadPdf.value = percent > 99 ? 99 : percent;
            }
        },
        timeout: 600000, // 10 minutes timeout
    })
        .then((res) => {
            newBook.value.file_url = res.data.file_path;
            newBook.value.page_number = res.data.page_number;
            isUploadPdf.value = 100;
        })
        .catch((error) => {
            console.error("PDF Upload Error:", error);
            isUploadPdf.value = 0;

            if (error.code === "ECONNABORTED") {
                errors.value["file_url"] =
                    "Upload timeout - file may be too large";
            } else if (error.response?.status === 413) {
                errors.value["file_url"] =
                    "File too large - reduce file size and try again";
            } else if (error.response?.data?.errors) {
                errors.value["file_url"] =
                    error.response.data.errors.file_url?.[0] || "Upload failed";
            } else if (error.response?.data?.message) {
                errors.value["file_url"] = error.response.data.message;
            } else {
                errors.value["file_url"] = "Upload failed - please try again";
            }
            event.target.value = "";
        });
}

function goBack() {
    window.history.back();
}
</script>

<template>
    <div class="w-full mx-auto p-4 md:p-6 bg-white rounded-lg shadow-sm">
        <button
            @click="goBack()"
            class="flex items-center justify-center gap-4 text-gray-600 hover:text-lime-700 transition-colors mb-6 group"
            aria-label="Go back"
        >
            <i
                class="fas fa-arrow-left self-center pb-6 text-lg group-hover:-translate-x-1 transition-transform"
            ></i>
            <span class="text-2xl font-bold self-center text-lime-700 mb-6"
                >Add New Book</span
            >
        </button>

        <form class="space-y-8">
            <!-- 60/40 layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- LEFT: Uploads (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Title
                        </label>
                        <input
                            v-model="newBook.title"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.title }"
                            placeholder="Book title"
                        />
                        <p
                            v-if="errors.title"
                            class="mt-1 text-red-500 text-sm"
                        >
                            {{ errors.title }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2"
                                >Upload Cover Page</label
                            >
                            <div class="relative">
                                <div
                                    class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition relative overflow-hidden"
                                    :class="{
                                        'border-lime-500 text-gray-500 bg-lime-50':
                                            isProcessingThumbnail,
                                    }"
                                >
                                    <div
                                        class="flex flex-col items-center text-gray-500"
                                    >
                                        <template v-if="isProcessingThumbnail">
                                            <div
                                                class="relative mb-2 w-10 h-10 flex items-center justify-center"
                                            >
                                                <div
                                                    class="absolute inset-0 rounded-full bg-lime-100 animate-ping opacity-75"
                                                ></div>
                                                <div
                                                    class="relative z-10 flex items-center justify-center"
                                                >
                                                    <svg
                                                        class="w-8 h-8 text-lime-600"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke="currentColor"
                                                            stroke-linecap="round"
                                                            stroke-width="2"
                                                            d="M12 4v4m0 4v4m0 4v4m8-12h-4m-4 0H8m12 4h-4m-4 0H8"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="space-y-1">
                                                <span
                                                    v-if="
                                                        isProcessingThumbnail <
                                                        100
                                                    "
                                                    class="text-sm font-medium text-gray-600"
                                                    >Processing
                                                    thumbnail...</span
                                                >
                                                <span
                                                    v-else
                                                    class="text-sm font-medium text-green-600"
                                                    >Completed</span
                                                >
                                            </div>
                                        </template>
                                        <template v-else>
                                            <i
                                                class="fa-solid text-2xl mb-2 text-gray-500"
                                                :class="{
                                                    'fa-image':
                                                        isProcessingThumbnail <=
                                                        99,
                                                    'fa-check text-lime-600':
                                                        isProcessingThumbnail ==
                                                        100,
                                                }"
                                            ></i>
                                            <span class="text-sm"
                                                >Click to upload cover image
                                            </span>
                                        </template>
                                    </div>
                                    <input
                                        type="file"
                                        accept="image/jpeg, image/png"
                                        @change="uploadThumbnail($event)"
                                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                        :disabled="
                                            uploadProgress < 100 &&
                                            uploadProgress > 1
                                        "
                                    />
                                </div>
                                <p
                                    v-if="errors.cover_page_url"
                                    class="mt-1 text-red-500 text-sm"
                                >
                                    {{ errors.cover_page_url }}
                                </p>
                            </div>
                        </div>

                        <!-- Intro Video Upload -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2"
                                >Upload Intro Video</label
                            >
                            <div class="relative">
                                <div
                                    class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition"
                                    :class="{
                                        'border-lime-500 bg-lime-50':
                                            uploadProgress,
                                    }"
                                >
                                    <div
                                        class="flex flex-col items-center text-gray-500"
                                    >
                                        <template v-if="uploadProgress">
                                            <div
                                                class="relative mb-2 w-10 h-10"
                                            >
                                                <!-- Dynamic progress spinner -->
                                                <svg
                                                    class="w-full h-full transform -rotate-90"
                                                    viewBox="0 0 36 36"
                                                >
                                                    <circle
                                                        cx="18"
                                                        cy="18"
                                                        r="16"
                                                        fill="none"
                                                        class="stroke-gray-200"
                                                        stroke-width="2"
                                                    ></circle>
                                                    <circle
                                                        cx="18"
                                                        cy="18"
                                                        r="16"
                                                        fill="none"
                                                        class="stroke-lime-600"
                                                        stroke-width="2"
                                                        :stroke-dasharray="`${uploadProgress * 1.13}, 113`"
                                                    ></circle>
                                                </svg>
                                                <div
                                                    class="absolute inset-0 flex items-center justify-center"
                                                >
                                                    <span
                                                        class="text-xs font-bold text-lime-600"
                                                        >{{
                                                            uploadProgress
                                                        }}%</span
                                                    >
                                                </div>
                                            </div>
                                            <span
                                                class="text-sm"
                                                :class="{
                                                    'text-green-600':
                                                        uploadProgress == 100,
                                                }"
                                                >{{
                                                    uploadProgress < 100
                                                        ? "Uploading video..."
                                                        : "Completed"
                                                }}</span
                                            >
                                        </template>
                                        <template v-else>
                                            <i
                                                class="fas fa-video text-2xl mb-2"
                                            ></i>
                                            <span class="text-sm"
                                                >Click to upload intro
                                                video</span
                                            >
                                        </template>
                                    </div>
                                    <input
                                        type="file"
                                        accept="video/mp4"
                                        @change="uploadIntroVideo($event)"
                                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                        :disabled="
                                            uploadProgress < 100 &&
                                            uploadProgress > 1
                                        "
                                    />
                                </div>
                                <p
                                    v-if="errors.intro_video"
                                    class="mt-1 text-red-500 text-sm"
                                >
                                    {{ errors.intro_video }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Book File Upload -->
                    <div>
                        <label
                            class="block text-gray-700 font-medium text-sm mb-2"
                        >
                            Upload Book File
                        </label>
                        <div class="relative">
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center cursor-pointer hover:bg-gray-50 transition"
                                :class="{
                                    'border-lime-500 bg-lime-50': isUploadPdf,
                                }"
                            >
                                <div
                                    class="flex flex-col items-center text-gray-500"
                                >
                                    <template v-if="isUploadPdf">
                                        <div class="relative mb-2 w-10 h-10">
                                            <!-- Dynamic progress spinner -->
                                            <svg
                                                class="w-full h-full transform -rotate-90"
                                                viewBox="0 0 36 36"
                                            >
                                                <circle
                                                    cx="18"
                                                    cy="18"
                                                    r="16"
                                                    fill="none"
                                                    class="stroke-gray-200"
                                                    stroke-width="2"
                                                ></circle>
                                                <circle
                                                    cx="18"
                                                    cy="18"
                                                    r="16"
                                                    fill="none"
                                                    class="stroke-lime-600"
                                                    stroke-width="2"
                                                    :stroke-dasharray="`${isUploadPdf * 1.13}, 113`"
                                                ></circle>
                                            </svg>
                                            <div
                                                class="absolute inset-0 flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-xs font-bold text-lime-600"
                                                    >{{ isUploadPdf }}%</span
                                                >
                                            </div>
                                        </div>
                                        <span
                                            class="text-sm"
                                            :class="{
                                                'text-green-600':
                                                    isUploadPdf == 100,
                                            }"
                                            >{{
                                                isUploadPdf < 100
                                                    ? "Uploading PDF..."
                                                    : "Completed"
                                            }}</span
                                        >
                                    </template>
                                    <template v-else>
                                        <i
                                            class="fas fa-file-pdf text-3xl mb-2"
                                        ></i>
                                        <span class="text-sm"
                                            >Click to upload book file</span
                                        >
                                    </template>
                                </div>
                                <input
                                    type="file"
                                    accept="pdf"
                                    @change="uploadPdfFile($event)"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                    :disabled="
                                        isUploadPdf < 100 && isUploadPdf > 1
                                    "
                                />
                            </div>
                            <p
                                v-if="errors.file_url"
                                class="mt-1 text-red-500 text-sm"
                            >
                                {{ errors.file_url }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Fields (1/3) -->
                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Author
                        </label>
                        <input
                            v-model="newBook.auther"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.auther }"
                            placeholder="Author name"
                        />
                        <p
                            v-if="errors.auther"
                            class="mt-1 text-red-500 text-sm"
                        >
                            {{ errors.auther }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Price In ETB
                        </label>
                        <input
                            v-model.number="newBook.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.price }"
                            placeholder="0.00"
                        />
                        <p
                            v-if="errors.price"
                            class="mt-1 text-red-500 text-sm"
                        >
                            {{ errors.price }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Edition
                        </label>
                        <input
                            v-model.number="newBook.eddition"
                            type="number"
                            min="1"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.eddition }"
                            placeholder="1"
                        />
                        <p
                            v-if="errors.eddition"
                            class="mt-1 text-red-500 text-sm"
                        >
                            {{ errors.eddition }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Publish Date
                        </label>
                        <input
                            v-model="newBook.publish_date"
                            type="date"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.publish_date }"
                        />
                        <p
                            v-if="errors.publish_date"
                            class="mt-1 text-red-500 text-sm"
                        >
                            {{ errors.publish_date }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Language
                        </label>
                        <select
                            v-model="newBook.language"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                        >
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
                <p v-if="errors.description" class="mt-1 text-red-500 text-sm">
                    {{ errors.description }}
                </p>
            </div>

            {{ loading }}
            <!-- ACTION BUTTONS -->
            <div class="flex justify-end gap-4 pt-6">
                <button
                    type="button"
                    @click="cancelAdd"
                    class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-medium"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    @click="addBook()"
                    :disabled="
                        loading ||
                        uploadProgress !== 100 ||
                        isProcessingThumbnail !== 100 ||
                        isUploadPdf !== 100 ||
                        !newBook.intro_video ||
                        !newBook.cover_page_url ||
                        !newBook.file_url
                    "
                    class="px-6 py-2.5 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition font-medium disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    <span v-if="loading">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Processing...
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
