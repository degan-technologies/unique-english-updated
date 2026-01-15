<script setup="">
import DescriptionEditor from "@/components/Book/DescriptionEditor.vue";
import Axios from "axios";
import { storeToRefs } from "pinia";
import { ref } from "vue";
import { useToast } from "vue-toastification";

import { useInstructorStore } from "@/store/useInstructorStore";

const InstructorStore = useInstructorStore();
const { booksAdmin } = storeToRefs(InstructorStore);

const props = defineProps({
    selectedbook: {
        type: Object,
        required: true,
        validator: (book) => book && book.id,
    },
    editingBookId: {
        type: Number,
        default: null,
    },
});

const uploadProgress = ref(0);
const isProcessingThumbnail = ref(0);
const updateFilePdf = ref(null);
const updatedThumbnail = ref(null);
const updateIntroVideo = ref(null);
const page_number = ref(null);
const error = ref("");
const errors = ref({});
const isUploadPdf = ref(0);
const toast = useToast();
const loading = ref(false);
const emit = defineEmits(["cancel-edit"]);

const cancelEdit = () => {
    emit("cancel-edit");
};

function isFileObject(obj) {
    return (
        obj instanceof File &&
        typeof obj.name === "string" &&
        typeof obj.size === "number"
    );
}

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
    formData.append("language", props.selectedbook.language);
    formData.append("discount", props.selectedbook.discount || 0);
    formData.append("description", props.selectedbook.description);
    formData.append("publish_date", props.selectedbook.publish_date);

    if (updatedThumbnail.value !== null) {
        formData.append("cover_page_url", updatedThumbnail.value);
    } else {
        formData.delete("cover_page_url");
    }

    if (updateFilePdf.value !== null) {
        formData.append("page_number", page_number.value);
        formData.append("file_url", updateFilePdf.value);
    } else {
        formData.delete("file_url");
        formData.delete("page_number");
    }

    if (updateIntroVideo.value !== null) {
        formData.append("intro_vedio", updateIntroVideo.value);
    } else {
        formData.delete("intro_vedio");
    }

    try {
        const response = await Axios.post(
            `/api/books/update-books/${props.selectedbook.id}`,
            formData,
            { headers: { "Content-Type": "multipart/form-data" } }
        );

        booksAdmin.value = booksAdmin.value.filter(
            (book) => book.id !== props.selectedbook.id
        );
        booksAdmin.value = [response.data.data, ...booksAdmin.value];

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

function uploadIntroVideo(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append("intro_video", file);

    errors.value["intro_video"] = "";
    uploadProgress.value = 0;

    Axios.post("/api/books/upload-intro-video", formData, {
        headers: {
            "Content-Type": "multipart/form-data",
        },
        onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
                let percent = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
                uploadProgress.value = percent > 99 ? 99 : percent;
            }
        },
    })
        .then((res) => {
            updateIntroVideo.value = res.data.file_path;
            uploadProgress.value = 100;
        })
        .catch((error) => {
            console.error(error);
            if (error.response?.data?.errors) {
                errors.value["intro_video"] =
                    error.response.data.errors.intro_video?.[0] ||
                    "Upload failed";
            }
        });
}

function uploadThumbnail(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append("cover_page_url", file);

    errors.value["cover_page_url"] = "";
    isProcessingThumbnail.value = 0;

    Axios.post("/api/books/upload-thumbnail", formData, {
        headers: {
            "Content-Type": "multipart/form-data",
        },
        onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
                let percent = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
                isProcessingThumbnail.value = percent > 99 ? 99 : percent;
            }
        },
    })
        .then((res) => {
            updatedThumbnail.value = res.data.file_path;
            isProcessingThumbnail.value = 100;
        })
        .catch((error) => {
            console.error(error);
            if (error.response?.data?.errors) {
                errors.value["cover_page_url"] =
                    error.response.data.errors.cover_page_url?.[0] ||
                    "Upload failed";
            }
        });
}

function uploadPdfFile(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append("file_url", file);

    errors.value["file_url"] = "";
    isUploadPdf.value = 0;

    Axios.post("/api/books/upload-pdf", formData, {
        headers: {
            "Content-Type": "multipart/form-data",
        },
        onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
                let percent = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
                isUploadPdf.value = percent > 99 ? 99 : percent;
            }
        },
    })
        .then((res) => {
            updateFilePdf.value = res.data.file_path;
            page_number.value = res.data.page_number;
            isUploadPdf.value = 100;
        })
        .catch((error) => {
            console.error(error);
            if (error.response?.data?.errors) {
                errors.value["file_url"] =
                    error.response.data.errors.file_url?.[0] ||
                    "Upload failed";
            }
        });
}
</script>

<template>
    <div
        class="mx-auto p-6 bg-white rounded-lg shadow-sm border border-gray-200"
    >
        <!-- Error Message -->
        <div
            v-if="error"
            class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded"
        >
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ error }}</span>
            </div>
        </div>

        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">
            Edit Book
        </h2>

        <form @submit.prevent="handleUpdateCourse" class="space-y-8">
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
                            v-model="props.selectedbook.title"
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
                                                        :stroke-dasharray="`${
                                                            uploadProgress *
                                                            1.13
                                                        }, 113`"
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
                                                    :stroke-dasharray="`${
                                                        isUploadPdf * 1.13
                                                    }, 113`"
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
                                    accept="application/pdf"
                                    @change="uploadPdfFile($event)"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                    :disabled="
                                        isUploadPdf < 100 && isUploadPdf > 1
                                    "
                                />
                            </div>
                            <!-- fdgjkdfgj -->
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
                            v-model="props.selectedbook.auther"
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
                            Price (Birr)
                        </label>
                        <input
                            v-model.number="props.selectedbook.price"
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
                            v-model.number="props.selectedbook.eddition"
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
                            v-model="props.selectedbook.publish_date"
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
                            v-model="props.selectedbook.language"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-lime-500 focus:border-lime-500"
                            :class="{ 'border-red-500': errors.language }"
                        >
                            <option value="English">English</option>
                            <option value="Amharic">Amharic</option>
                            <option value="Other">Other</option>
                        </select>
                        <p
                            v-if="errors.language"
                            class="mt-1 text-red-500 text-sm"
                        >
                            {{ errors.language }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- DESCRIPTION (Full Width) -->
            <div>
                <label class="block text-gray-700 font-medium text-sm mb-2">
                    Book Description
                </label>
                <DescriptionEditor
                    :selected="props.selectedbook"
                    @update-description="updateDescription"
                />
                <p v-if="errors.description" class="mt-1 text-red-500 text-sm">
                    {{ errors.description }}
                </p>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex justify-end gap-4 pt-6">
                <button
                    type="button"
                    @click="cancelEdit"
                    class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition font-medium"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2.5 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition font-medium disabled:opacity-70 disabled:cursor-not-allowed"
                >
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
