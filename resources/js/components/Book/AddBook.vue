<script setup>
import { ref } from "vue";
import Axios from "axios";
import { useToast } from "vue-toastification";
import AddBookDescription from "@/components/Book/AddBookDescription";

const toast = useToast();

const newBook = ref({
    title: "",
    auther: "",
    price: null,
    eddition: null,
    discount: null,
    publish_date: "",
    description: "",
    page_number: "",
    language: "English",
    file_format: "pdf",
    cover_page_url: null,
    file_url: null,
    intro_video: null,
    tag: "",
});

const coverPreview = ref(null);
const videoPreview = ref(null);

const onFileChange = (field, event) => {
    const file = event.target.files[0];
    if (file) {
        newBook.value[field] = file;
        if (field === "cover_page_url") {
            coverPreview.value = URL.createObjectURL(file);
        }
        if (field === "intro_video") {
            videoPreview.value = URL.createObjectURL(file);
        }
    }
};

const loading = ref(false);
const error = ref("");
const success = ref("");

const addBook = async () => {
    error.value = "";
    success.value = "";
    loading.value = true;

    const tagsArray = newBook.value.tag
        .split(",")
        .map((t) => t.trim())
        .filter((t) => t !== "");

    const formData = new FormData();
    formData.append("title", newBook.value.title);
    formData.append("auther", newBook.value.auther);
    formData.append("price", newBook.value.price);
    formData.append("eddition", newBook.value.eddition);
    formData.append("discount", newBook.value.discount);
    formData.append("publish_date", newBook.value.publish_date);
    formData.append("description", newBook.value.description);
    formData.append("page_number", newBook.value.page_number);
    formData.append("language", newBook.value.language);
    formData.append("file_format", newBook.value.file_format);

    if (newBook.value.cover_page_url) {
        formData.append("cover_page_url", newBook.value.cover_page_url);
    }
    if (newBook.value.file_url) {
        formData.append("file_url", newBook.value.file_url);
    }
    if (newBook.value.intro_video) {
        formData.append("intro_vedio", newBook.value.intro_video);
    }
    formData.append("tag", JSON.stringify(tagsArray));

    try {
        await Axios.post("/api/books/books", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        success.value = "Book added successfully!";
        toast.success("Book added successfully!", { position: "top-right" });
        newBook.value = {
            title: "",
            auther: "",
            price: null,
            eddition: null,
            discount: null,
            publish_date: "",
            description: "",
            page_number: "",
            language: "English",
            file_format: "pdf",
            cover_page_url: null,
            file_url: null,
            intro_video: null,
            tag: "",
        };
        coverPreview.value = null;
        videoPreview.value = null;
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to add book.";
        toast.error(error.value, { position: "top-right" });
    } finally {
        loading.value = false;
    }
};

function goBack() {
    window.history.back();
}
</script>

<template>
    <button @click="goBack()"
        class="px-4 py-2 bg-white text-black rounded-md hover:bg-slate-200 transition mr-4 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5 mr-2"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
        </svg>
    </button>

    <div class="max-w-4xl mx-auto p-4 bg-white">
        <h2 class="text-2xl font-bold text-center mb-4">Add New Book</h2>
        <form @submit.prevent="addBook"
            class="space-y-6 mt-4">
            <!-- 60/40 layout -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- LEFT: Uploads (3/5 = 60%) -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Cover Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium text-sm mb-2">Upload Cover Page</label>
                        <div v-if="coverPreview"
                            class="mb-2">
                            <img :src="coverPreview"
                                alt="Cover Preview"
                                class="w-3/4 max-h-48 object-cover rounded-md shadow hover:scale-105 transition-transform" />
                        </div>
                        <div
                            class="relative border border-gray-300 rounded-md text-center p-2 cursor-pointer hover:bg-gray-50 transition">
                            <span class="text-gray-500 text-sm">Click to select cover image</span>
                            <input type="file"
                                @change="onFileChange('cover_page_url', $event)"
                                class="absolute inset-0 opacity-0 cursor-pointer"
                                required />
                        </div>
                    </div>

                    <!-- Intro Video Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium text-sm mb-2">Upload Intro Video</label>
                        <div v-if="videoPreview"
                            class="mb-2">
                            <video controls
                                class="w-3/4 max-h-48 rounded-md shadow">
                                <source :src="videoPreview"
                                    type="video/mp4" />
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        <div
                            class="relative border border-gray-300 rounded-md text-center p-2 cursor-pointer hover:bg-gray-50 transition">
                            <span class="text-gray-500 text-sm">Click to select intro video</span>
                            <input type="file"
                                @change="onFileChange('intro_video', $event)"
                                accept="video/*"
                                class="absolute inset-0 opacity-0 cursor-pointer"
                                required />
                        </div>
                    </div>

                    <!-- Book File Upload -->
                    <div>
                        <label class="block text-gray-700 font-medium text-sm mb-2">Upload Book File</label>
                        <input type="file"
                            @change="onFileChange('file_url', $event)"
                            class="w-full border border-gray-300 rounded-md p-2 text-sm"
                            required />
                    </div>
                </div>

                <!-- RIGHT: Fields (2/5 = 40%) -->
                <div class="lg:col-span-2 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input v-model="newBook.title"
                            type="text"
                            class="border border-gray-300 input w-full !py-1"
                            required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Author</label>
                        <input v-model="newBook.auther"
                            type="text"
                            class="border border-gray-300 input w-full !py-1"
                            required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price</label>
                        <input v-model.number="newBook.price"
                            type="number"
                            class="border border-gray-300 input w-full !py-1"
                            required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Edition</label>
                        <input v-model.number="newBook.eddition"
                            type="number"
                            class="border border-gray-300 input w-full !py-1"
                            required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Discount (%)</label>
                        <input v-model.number="newBook.discount"
                            type="number"
                            class="border border-gray-300 input w-full !py-1" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Publish Date</label>
                        <input v-model="newBook.publish_date"
                            type="date"
                            class="border border-gray-300 input w-full !py-1"
                            required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Language</label>
                        <input v-model="newBook.language"
                            type="text"
                            class="border border-gray-300 input w-full !py-1"
                            required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tags (comma-separated)</label>
                        <input v-model="tagInput"
                            type="text"
                            class="border border-gray-300 input w-full !py-1" />
                    </div>
                </div>
            </div>

            <!-- DESCRIPTION (Full Width) -->
            <div class="w-3/4">
                <label class="block text-gray-700 font-medium text-sm mb-2">Book Description</label>
                <AddBookDescription v-model="newBook.description" />
            </div>

            <!-- ACTION BUTTON -->
            <div class="flex justify-end gap-4 pt-4">
                <button type="button"
                    @click="cancelAdd"
                    class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="bg-lime-500 text-white px-6 py-2 rounded hover:bg-lime-600 transition">
                    Add Book
                </button>
            </div>
        </form>
    </div>
</template>
