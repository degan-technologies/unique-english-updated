<script setup>
    import Axios from "axios";
    import { useRoute, useRouter } from "vue-router";
    import { ref, onMounted, defineProps  } from "vue";

    const route = useRoute();
    const router = useRouter();

    const props = defineProps({
    courseId: Number,
    moduleId: Number
    });

    const form = ref({
    title: "",
    description: "",
    content_type: 1,
    content_url: null,
    thumbnail_url: null,
    hour: "",
    status: 2, 
    note: "",
    });

    const errorMessage = ref("");
    const content = ref([]);
    const loading = ref(true);

    const editingContent = ref(null);

    const handleFileUpload = (field, event) => {
        form.value[field] = event.target.files[0];
    };

    function editContent(item) {
        editingContent.value = item;
        form.value = { ...item }; 
    };

    const cancelEdit = () => {
        editingContent.value = null;  
        form.value = {}; 
    };

    function submitModuleContent() {
        const formData = new FormData();
        formData.append("course_id", props.courseId); 
        formData.append("course_module_id", props.moduleId);
        formData.append("title", form.value.title);
        formData.append("description", form.value.description);
        formData.append("content_type", Number(form.value.content_type));
        formData.append("content_url", form.value.content_url);
        formData.append("thumbnail_url", form.value.thumbnail_url);
        formData.append("hour", form.value.hour);
        formData.append("status", Number(form.value.status));
        formData.append("note", form.value.note);

        Axios
            .post("/api/courses/content", formData)
        .then(res => {});
    };
    
    function updateContent() { 
        const formData = new FormData();
        formData.append("_method", "PUT");
        formData.append("title", form.value.title);
        formData.append("description", form.value.description);
        formData.append("content_type", form.value.content_type);
        formData.append("content_url", form.value.content_url);
        formData.append("thumbnail_url", form.value.thumbnail_url);
        formData.append("hour", form.value.hour);
        formData.append("status", form.value.status);
        formData.append("note", form.value.note);

        Axios
            .put(`/api/courses/content/${editingContent.value.id}`, formData)
            .then(res => {});
    };

    function deleteContent(id) {
        Axios
            .delete(`/api/courses/content/${id}`)
            .then(res => {});
    };

</script>  

<template>
    <div class="w-full mx-auto p-2  mb-2 h-auto [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden">
        <h2 class="text-lg font-semibold mb-6 text-center text-black">Add Course Content</h2>

        <form class="grid grid-cols-1 gap-4 p-4 bg-white rounded-lg ">
            <div class="relative">
                <label class="block text-md font-medium text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input 
                        v-model="form.title"
                        type="text"
                        placeholder="Enter course title..."
                        class="w-full p-2 pl-10 border rounded-md  text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required />
                    <i class="fas fa-heading absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            <div>
                <label class="block text-md font-medium text-gray-600 mb-1">Description (Min 10 characters)</label>
                <textarea
                    v-model="form.description"
                    placeholder="Write a brief description..."
                    class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    minlength="10" ></textarea>
            </div>
            <div>
                <label class="block text-md font-medium text-gray-600 mb-1">Content Type <span class="text-red-500">*</span></label>
                <select 
                    v-model="form.content_type"
                    class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required >
                    <option :value="1">📹 Video</option>
                    <option :value="2">📄 PDF</option>
                    <option :value="3">🖼️ Image</option>
                </select>
            </div>
            <div>
                <label class="block text-md font-medium text-gray-600 mb-1">Upload Content</label>
                <input
                    type="file"
                    @change="handleFileUpload('content_url', $event)"
                    class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-md font-medium text-gray-600 mb-1">Upload Thumbnail</label>
                <input
                    type="file"
                    @change="handleFileUpload('thumbnail_url', $event)"
                    accept="image/*"
                    class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-md font-medium text-gray-600 mb-1">Duration (HH:MM) <span class="text-red-500">*</span></label>
                <input 
                    v-model="form.hour"
                    type="time"
                    class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
            </div>
            <div>
                <label class="block text-md font-medium text-gray-600 mb-1">Add content note</label>
                <textarea
                    v-model="form.note"
                    placeholder="Add any additional note..."
                    class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    minlength="10" ></textarea>
            </div>
            <div v-if="errorMessage" class="text-red-500 text-center text-md mt-2"> {{ errorMessage }} </div>
            <div class="mt-4 flex justify-end">
                <div class="flex space-x-4">
                    <button
                        type="submit"
                        @click="submitModuleContent()"
                        class="bg-lime-700 text-white text-xs px-3 py-2 rounded-md hover:bg-lime-600 transition duration-300" >
                        Add
                    </button>
                    <button
                        type="button"
                        @click="resetForm"
                        class="bg-gray-300 text-black text-xs px-3 py-2 rounded-md hover:bg-gray-600 transition duration-300" >
                        Cancel
                    </button>
            </div>
            </div>
        </form>
    </div>
</template>
                           
<style scoped>
    html, body {
        height: 100%;
        margin: 0;
    }

    body {
        display: flex;
        flex-direction: column;
    }

    .max-w-3xl {
        min-height: 100%;
    }

    h2 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    form {
        display: grid;
        grid-template-columns: 1fr;
        grid-gap: 1rem;
    }

    form > div {
        display: flex;
        flex-direction: column;
    }

    form .col-span-2 {
        grid-column: span 2;
    }

    @media (min-width: 768px) {
        form {
        grid-template-columns: 1fr 1fr;
        }
        .col-span-2 {
        grid-column: span 2;
        }
    }

    button {
        width: 100%;
    }

    .bg-gray-100 {
        background-color: #f7fafc;
    }

    .shadow-md {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    p {
        margin-bottom: 0.5rem;
    }

    img {
        object-fit: cover;
    }
</style>
            