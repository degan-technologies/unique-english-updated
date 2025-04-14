<script setup>
import Axios from 'axios';
import { ref, nextTick, } from 'vue';
import 'video.js/dist/video-js.css';

const isPlaying = ref(false);
const editingContent = ref(false);

const content_url = ref('content_url')
const thumbnail_url = ref('thumbnail_url')
const playerInstance = ref(null);
const videoPlayer = ref(null);

const form = ref({
    title: "",
    description: "",
    content_type: 1,
    content_url: null,
    thumbnail_url: null,

});

const props = defineProps({
    selectedContent: Object,
    selectedModule: Object,
})
const emit = defineEmits(['cancelEdit']);

function editSelectedContent() {
    if (!props.selectedContent?.id) return;
    editingContent.value = true;
    form.value = { ...props.selectedContent };
};

if (props.selectedModule) {
    editingContent.value = true;
}

function cancelEdit() {
    editingContent.value = false;
    form.value = {};
    emit('cancelEdit');
};

function handleFileUpload(field, event) {
    const file = event.target.files[0];
    if (file) {
        if (field === 'thumbnail_url') {
            form.value.thumbnail_url = file;
            form.value.create_thumbnail_url = URL.createObjectURL(file);
        } else if (field === 'content_url') {
            form.value.content_url = file;
            form.value.create_content_url = URL.createObjectURL(file);

            if (file.type.includes('video')) {
                form.value.content_type = 1;
            } else if (file.type.includes('pdf')) {
                form.value.content_type = 2;
            } else if (file.type.includes('image')) {
                form.value.content_type = 3;
            }
            updateVideoPlayer();
        }
    }
}

function updateVideoPlayer() {
    nextTick(() => {
        if (videoPlayer.value && form.value.create_content_url) {
            videoPlayer.value.src = form.value.create_content_url;
            videoPlayer.value.load();
            videoPlayer.value.play();
        }
    });
}

function storeModuleContent() {
    const formData = new FormData();
    formData.append("course_id", props.selectedModule.course_id);
    formData.append("course_module_id", props.selectedModule.id);
    formData.append("title", form.value.title);
    formData.append("description", form.value.description);
    formData.append("content_url", form.value.content_url);
    formData.append("thumbnail_url", form.value.thumbnail_url);

    Axios
        .post("/api/courses/content", formData)
        .then(res => { });
};

function updateSelectedContent() {
    const formData = new FormData();

    formData.append("title", form.value.title);
    formData.append("description", form.value.description);
    formData.append("content_url", form.value.content_url);
    formData.append("thumbnail_url", form.value.thumbnail_url);

    Axios
        .post(`/api/courses/update-content/${props.selectedContent.id}`, formData)
        .then(res => {
            editingContent.value = false;
        })
};

function deleteSelectedContent(id) {
    Axios
        .delete(`/api/courses/content/${id}`)
        .finally(() => {
            props.selectedContent = null;
        })
};

</script>

<template>
    <div class="my-4 border w-full p-4">
        <div v-if="!editingContent"
            class="w-full flex flex-col items-center justify-center p-4">
            <div class="relative w-full max-w-full md:max-w-3xl aspect-video lg:max-h-[400px]">
                <div v-if="!isPlaying && selectedContent.content_type === 1"
                    class="absolute inset-0 cursor-pointer"
                    @click="isPlaying = true">
                    <img :src="selectedContent.thumbnail_url"
                        alt="Content Thumbnail"
                        class="w-full h-full object-cover rounded-md shadow-md">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <!-- Animated Burst Ring -->
                        <div class="absolute w-16 h-16 rounded-full bg-lime-500 opacity-50 animate-burst"></div>

                        <!-- Actual Play Button -->
                        <div class="p-2 bg-lime-500 rounded-full z-10 flex items-center justify-center shadow-md">
                            <i
                                class="fas fa-play-circle text-white text-lg sm:text-lg md:text-xl lg:text-2xl xl:text-3xl"></i>
                        </div>
                    </div>
                </div>
                <div v-else
                    class="absolute inset-0 w-full h-auto rounded-md shadow-md">
                    <iframe
                        v-if="selectedContent.course_content_url.includes('youtube.com') || selectedContent.course_content_url.includes('youtu.be')"
                        :src="selectedContent.course_content_url + '?autoplay=1'"
                        class="w-full h-full rounded-md shadow-md border"
                        frameborder="0"
                        allowfullscreen>
                    </iframe>

                    <video v-else-if="selectedContent.content_type === 1"
                        controls
                        autoplay
                        class="w-full h-full rounded-md shadow-md border">
                        <source :src="selectedContent.course_content_url">
                    </video>

                    <iframe v-else-if="selectedContent.content_type === 2"
                        :src="selectedContent.content_url"
                        class="w-full h-full rounded-md shadow-md border">
                    </iframe>

                    <img v-else-if="selectedContent.content_type === 3"
                        :src="selectedContent.course_content_url"
                        class="w-full h-full rounded-md shadow-md border object-contain">

                    <p v-else
                        class="text-center text-gray-500">Unsupported content type</p>
                </div>
            </div>
        </div>
        <div v-if="!editingContent"
            class="flex flex-col md:flex-col px-4 gap-4">

            <div class="w-full">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 my-2">
                    <div class="h-full flex justify-center items-center self-center">
                        <i :class="{
                            'fa-circle-play': selectedContent.content_type == 1,
                            'fa-file-lines': selectedContent.content_type == 2,
                            'fa-image': selectedContent.content_type == 3,
                        }"
                            class="fa-solid text-xl mr-2">
                        </i>
                        <h3 class="text-lg font-semi-bold text-gray-800">{{ selectedContent.title }}</h3>
                    </div>
                    <div class="flex gap-2">
                        <button @click="editSelectedContent()"
                            class="border p-1 bg-slate-50 rounded-sm text-lime-700">
                            <i class="fas fa-edit text-sm"></i> Edit
                        </button>
                        <button @click="deleteSelectedContent(selectedContent.id)"
                            class="border p-1 bg-slate-50 rounded-sm text-slate-700">
                            <i class="fas fa-trash text-sm"></i> Delete
                        </button>
                    </div>
                </div>
                <p class="text-gray-700 text-justify">{{ selectedContent.description }}</p>
            </div>
        </div>
        <div v-if="editingContent"
            class="mt-4 p-6 bg-white rounded-lg ">
            <h4 class="text-xl font-semibold mb-6 text-gray-800 text-start">
                {{ selectedModule ? 'Add Module Content' : 'Edit Module Content' }}
            </h4>
            <form class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-md font-medium text-gray-700">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.title"
                        type="text"
                        class="w-full border p-3 text-md rounded-md focus:ring-2 focus:ring-lime-700 focus:outline-none"
                        required>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div v-if="form.create_thumbnail_url || form?.thumbnail_url">
                        <img :src="form.create_thumbnail_url ? form.create_thumbnail_url : form?.thumbnail_url"
                            alt="Course Thumbnail"
                            class="w-full h-40 object-cover rounded-md shadow-md transition transform hover:scale-105">
                    </div>
                    <div>
                        <label class="block text-md font-medium text-gray-700 mb-1">
                            Upload Thumbnail
                        </label>
                        <input type="file"
                            @change="handleFileUpload(thumbnail_url, $event)"
                            class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700">
                    </div>
                    <div v-if="form.create_content_url || form?.content_url"
                        class="w-full h-40 rounded-md shadow-md border">
                        <iframe v-if="(form.create_content_url || selectedContent.course_content_url)?.includes('youtube.com') ||
                            (form.create_content_url || selectedContent.course_content_url)?.includes('youtu.be')"
                            :src="(form.create_content_url ? form.create_content_url : selectedContent.course_content_url) + '?autoplay=1'"
                            class="w-full h-full rounded-md shadow-md border"
                            frameborder="0"
                            allowfullscreen>
                        </iframe>
                        <video v-else-if="(form.content_type || selectedContent.content_type) === 1"
                            ref="videoPlayer"
                            class="video-js vjs-default-skin w-full h-40 rounded-md shadow-md border"
                            controls
                            autoplay
                            preload="auto">
                            <source
                                :src="form.create_content_url ? form.create_content_url : selectedContent.course_content_url"
                                type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <embed v-else-if="(form.content_type || selectedContent.content_type) === 2"
                            :src="form.create_content_url ? form.create_content_url : selectedContent.content_url"
                            class="w-full h-full rounded-md shadow-md border"
                            type="application/pdf">
                        </embed>
                        <img v-else-if="(form.content_type || selectedContent.content_type) === 3"
                            :src="form.create_content_url ? form.create_content_url : selectedContent.content_url"
                            class="w-full h-full rounded-md shadow-md border object-cover">
                    </div>
                    <div>
                        <label class="block text-md font-medium text-gray-700 mb-1">
                            Upload Content
                        </label>
                        <input type="file"
                            @change="handleFileUpload(content_url, $event)"
                            class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700">
                    </div>
                </div>
                <div>
                    <label class="block text-md font-medium text-gray-700">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea v-model="form.description"
                        class="w-full p-3 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        minlength="10"></textarea>
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-4">
                    <button type="button"
                        @click="cancelEdit()"
                        class="bg-slate-50 hover:bg-slate-100 text-black rounded-md px-6 py-2 text-md transition duration-200">
                        Cancel
                    </button>
                    <button type="button"
                        @click="selectedContent ? updateSelectedContent() : storeModuleContent()"
                        class="text-black bg-slate-300 hover:bg-slate-400 rounded-md px-6 py-2 text-md transition duration-200">
                        {{ selectedContent ? 'Update' : 'Add Content' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
<style scoped>
@keyframes burst {
    0% {
        transform: scale(1);
        opacity: 0.5;
    }

    70% {
        transform: scale(2.2);
        opacity: 0;
    }

    100% {
        opacity: 0;
    }
}

.animate-burst {
    animation: burst 1.8s ease-out infinite;
}
</style>
