<script setup>
import { ref, defineProps, defineEmits, onMounted, watch } from 'vue'
import Axios from 'axios'
import { useToast } from "vue-toastification";
import DescriptionEditor from "@/components/Book/DescriptionEditor.vue";

const props = defineProps({
    selectedbook: Object,
    booksAdmin: Array,
    editingBookId: Number,
})

const selected = props.selectedbook;
const error = ref("");
const toast = useToast();
const introVideoPreview = ref(null);
const coverPagePreview = ref(null);

const emit = defineEmits(['cancel-edit'])

const cancelEdit = () => {
    emit('cancel-edit')
}

const onFileChange = (field, event) => {
    const file = event.target.files[0];

    if (file) {
        selected[field] = file;

        if (field === 'intro_vedio') {
            introVideoPreview.value = URL.createObjectURL(file);
        } else if (field === 'cover_page_url') {
            coverPagePreview.value = URL.createObjectURL(file);

        }
    }
};

const handleUpdateCourse = async () => {
    if (!selected || !selected.id) {
        error.value = "No BOOK selected.";
        toast.error("No BOOK selected.", { position: "top-right" });
        return;
    }

    const formData = new FormData();
    formData.append("_method", "PUT");
    formData.append("title", selected.title);
    formData.append("auther", selected.auther);
    formData.append("price", selected.price);
    formData.append("eddition", selected.eddition);
    formData.append("discount", selected.discount);
    formData.append("publish_date", selected.publish_date);
    formData.append("description", selected.description);
    formData.append("language", selected.language);
    if (selected.cover_page_url instanceof File) {
        formData.append("cover_page_url", selected.cover_page_url);
    }
    if (selected.file_url instanceof File) {
        formData.append("file_url", selected.file_url);
    }
    if (selected.intro_vedio instanceof File) {
        formData.append("intro_vedio", selected.intro_vedio);
    }
    formData.append("isDownloadable", selected.isDownloadable);
    const parsedTags = tagInput.value
        .split(',')
        .map(tag => tag.trim())
        .filter(tag => tag.length > 0);
    formData.append("tag", JSON.stringify(parsedTags));

    try {
        const response = await Axios.post(
            `/api/books/books/${selected.id}`,
            formData,
            { headers: { "Content-Type": "multipart/form-data" } }
        );
        const updatedBooksAdmin = props.booksAdmin.map((book) =>
            book.id === selected.id ? response.data.data : book
        );
        props.booksAdmin.splice(0, props.booksAdmin.length, ...updatedBooksAdmin);

        toast.success("Book updated successfully!", { position: "top-right" });
        cancelEdit();
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to update book.", { position: "top-right" });
    }
};

const updateDescription = (description) => {
    selected.description = description;
};
const tagInput = ref('')

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

onMounted(() => {
    tagInput.value = safeParseTag(selected.tag)
})

</script>

<template>
    <div class="max-w-4xl mx-auto p-4 mt-6 bg-white shadow-lg rounded-lg border border-gray-200">
        <h2 class="text-2xl font-bold text-center mb-4">Edit Book</h2>
            <form class="space-y-6 mt-4">
                <!-- 60/40 layout -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <!-- LEFT: Uploads (3/5 = 60%) -->
                    <div class="lg:col-span-3 space-y-6">
                        <!-- Cover Upload -->
                        <div>
                            <label class="block text-gray-700 font-medium text-sm mb-2">Upload Cover Page</label>
                            <div v-if="coverPagePreview || selected?.cover_page_url"
                                class="mb-2 ">
                                <img :src="coverPagePreview || selected?.cover_page_url"
                                    alt="Course Thumbnail"
                                    class="w-3/4 max-h-48 object-cover rounded-md shadow hover:scale-105 transition-transform" />
                            </div>
                            <div
                                class="relative border border border-gray-300 rounded-md text-center p-2 cursor-pointer hover:bg-gray-50 transition">
                                <span class="text-gray-500 text-sm">Click to select cover image</span>
                                <input type="file"
                                    @change="onFileChange('cover_page_url', $event)"
                                    class="absolute inset-0 opacity-0 cursor-pointer" />
                            </div>
                        </div>

                        <!-- Intro Video Upload -->
                        <div>
                            <label class="block text-gray-700 font-medium text-sm mb-2">Upload Intro Video</label>
                            <div v-if="introVideoPreview || selected?.intro_vedio"
                                class="mb-2">
                                <video controls
                                    class="w-3/4 max-h-48 rounded-md shadow">
                                    <source :src="introVideoPreview || selected?.intro_video_url"
                                        type="video/mp4" />
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            <div
                                class="relative border border border-gray-300 rounded-md text-center p-2 cursor-pointer hover:bg-gray-50 transition">
                                <span class="text-gray-500 text-sm">Click to select intro video</span>
                                <input type="file"
                                    @change="onFileChange('intro_vedio', $event)"
                                    accept="video/*"
                                    class="absolute inset-0 opacity-0 cursor-pointer" />
                            </div>
                        </div>

                        <!-- Book Upload -->
                        <div>
                            <label class="block text-gray-700 font-medium text-sm mb-2">Upload Book File</label>
                            <input type="file"
                                @change="onFileChange('file_url', $event)"
                                class="w-full border border-gray-300 rounded-md p-2 text-sm" />
                        </div>
                    </div>

                    <!-- RIGHT: Fields (2/5 = 40%) -->
                    <div class="lg:col-span-2 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input v-model="selected.title"
                                type="text"
                                class="input w-full !py-2"
                                required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Author</label>
                            <input v-model="selected.auther"
                                type="text"
                                class="input w-full !py-2"
                                required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price</label>
                            <input v-model.number="selected.price"
                                type="number"
                                class="input w-full !py-2"
                                required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Edition</label>
                            <input v-model.number="selected.eddition"
                                type="number"
                                class="input w-full !py-2"
                                required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Discount (%)</label>
                            <input v-model.number="selected.discount"
                                type="number"
                                class="input w-full !py-2" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Publish Date</label>
                            <input v-model="selected.publish_date"
                                type="date"
                                class="input w-full !py-2"
                                required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Language</label>
                            <input v-model="selected.language"
                                type="text"
                                class="input w-full !py-2"
                                required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tags (comma-separated)</label>
                            <input v-model="tagInput"
                                type="text"
                                class="input w-full !py-2" />
                        </div>
                    </div>
                </div>

                <!-- DESCRIPTION (Full Width) -->
                <div class="w-3/4">
                    <label class="block text-gray-700 font-medium text-sm mb-2">Book Description</label>
                    <DescriptionEditor :selected="selected"
                        @update-description="updateDescription" />
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex justify-end gap-4 pt-4">
                    <button type="button"
                        @click="cancelEdit"
                        class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition">
                        Cancel
                    </button>
                    <button type="button"
                        @click="handleUpdateCourse"
                        class="bg-lime-400 text-white px-6 py-2 rounded hover:bg-lime-600 transition">
                        Update Book
                    </button>
                </div>
            </form>
    </div>
</template>

<style scoped>
.label {
    @apply block text-gray-700 font-medium text-xs mb-1;
}

.input {
    @apply w-full border border-gray-300 rounded-sm p-1 text-xs;
}
</style>