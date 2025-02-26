<script setup>
    import Axios from 'axios';
    import { ref } from 'vue';

    const editingContent = ref(false);
    const isPlaying = ref(false);

    const thumbnail_url = ref('thumbnail_url')
    const content_url = ref('content_url')

    const form = ref({
        title: "",
        description: "",
        content_type: 1,
        content_url: null,
        thumbnail_url: null,
        hour: "",
        status: 2, 
    });

    const props = defineProps({
        selectedContent: Object | null,
        selectedModule: Object | null,
    })

    function editSelectedContent()  {
        if (!props.selectedContent?.id) return;
        editingContent.value = true;
        form.value = { ...props.selectedContent };
    };

    if(props.selectedModule) {
        editingContent.value = true;
    }
 
    function cancelEdit() {
        editingContent.value = false;
        form.value = {};
    };

    function handleFileUpload(field, event) {
        const file = event.target.files[0];
        if (file) {

            if(field === thumbnail_url.value){
                form.value['upload_thumbnail'] = file;
                form.value['create_thumbnail_url'] = URL.createObjectURL(file);
                return;
            }
            form.value['upload_content'] = file;
            form.value['create_content_url'] = URL.createObjectURL(file);
        }
    };
    
    function storeModuleContent() {
        const formData = new FormData();
        formData.append("course_id",props.selectedModule.course_id); 
        formData.append("course_module_id",props.selectedModule.id);
        formData.append("title", form.value.title);
        formData.append("description", form.value.description);
        formData.append("content_type", Number(form.value.content_type));
        formData.append("content_url", form.value.content_url);
        formData.append("thumbnail_url", form.value.thumbnail_url);
        formData.append("hour", form.value.hour);
        formData.append("status", Number(form.value.status));

        Axios
            .post("/api/courses/content", formData)
        .then(res => {});
    };
    
    function updateSelectedContent() {
        const formData = new FormData();

        formData.append("title", form.value.title);
        formData.append("description", form.value.description);
        formData.append("content_type", form.value.content_type);
        formData.append("content_url", form.value.upload_content);
        formData.append("thumbnail_url", form.value.upload_thumbnail);
        formData.append("hour", form.value.hour);
        formData.append("status", form.value.status);
        formData.append("sequence", form.value.sequence);

        Axios
            .post(`/api/courses/update-content/${props.selectedContent.id}`, formData)
            .then(res=> {
                editingContent.value = false;
            })
    };

    function deleteSelectedContent(id) {
        Axios
            .delete(`/api/courses/content/${id}`)
            .finally(()=>{
                props.selectedContent = null; 
            })
    };
</script>
<template>
    <div class="my-4 border w-full p-4">
        <button 
            @click="selectedContent = null ; isPlaying = false"
            class="mb-4 text-gray-500 font-normal p-1 rounded-full hover:bg-gray-200 transition-all">
            Back 
        </button>

        <div v-if="!editingContent"
            class="w-full flex items-start justify-start p-4">
            <div class="relative h-64">
                <div v-if="!isPlaying" class="relative cursor-pointer" @click="isPlaying = true">
                    <img :src="selectedContent.thumbnail_url" alt="Content Thumbnail"
                        class="object-cover relative h-64 aspect-video rounded-md shadow-md">

                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 rounded-md">
                        <div class="p-4 bg-lime-500 rounded-full animate-breathe flex items-center justify-center">
                            <i class="fas fa-play-circle text-white text-lg"></i>
                        </div>
                    </div>
                </div>

                <div v-else class="object-cover relative w-full aspect-video rounded-md shadow-md">
                    <iframe v-if="selectedContent.content_url.includes('youtube.com') || selectedContent.content_url.includes('youtu.be')" 
                        :src="selectedContent.content_url + '?autoplay=1'" 
                        class="h-64 rounded-md shadow-md border" 
                        frameborder="0" allowfullscreen>
                    </iframe>

                    <video v-else controls autoplay class="h-64 rounded-md shadow-md border">
                        <source :src="selectedContent.content_url" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>

        <div v-if="!editingContent"
            class="flex flex-row px-4 gap-4">
            <div class="h-full border-2 rounded-full flex justify-center item-center self-center ">
                <i
                    :class="{
                        'fa-circle-play': selectedContent.content_type == 1,
                        'fa-file-lines': selectedContent.content_type == 2,
                        'fa-image': selectedContent.content_type == 3,
                    }"
                    class="fa-solid text-2xl" >
                </i>
            </div>
            <div>
                <div class="flex justify-start gap-4 my-2  w-full">
                    <h3 class="text-lg font-bold text-gray-800">{{ selectedContent.title }}</h3>
                    <div class="flex gap-3">
                        <button 
                            @click="editSelectedContent()"
                            class="border p-1 px-2 bg-slate-50 rounded-sm text-lime-700">
                            <i class="fas fa-edit text-sm"></i> Edit
                        </button>

                        <button 
                            @click="deleteSelectedContent(selectedContent.id)"
                            class="border p-1 px-2 bg-slate-50 rounded-sm text-slate-700">
                            <i class="fas fa-trash text-sm"></i> Delete
                        </button>
                    </div>
                </div>
                <p class="text-gray-700 text-justify">{{ selectedContent.description }}</p>
            </div>
        </div>
        
        <div v-if="editingContent" class="mt-4 p-4 bg-white ">
            <h4 class="text-lg font-semibold mb-4 text-start text-gray-800 ">{{selectedModule ? 'Add module Content':'Edit module Content'}}</h4>
            <form class="grid grid-cols-1 md:grid-cols-4 gap-3 ">
                <div class="col-span-3 space-y-6">
                    <div class="col-span-2">
                    <label class="block text-md font-medium">Title <span class="text-red-500">*</span></label>
                    <input v-model="form.title" type="text"
                        class=" w-full border p-2 text-md rounded-md focus:ring-2 focus:ring-lime-700 focus:outline-none" required>
                    </div>
                   
                    <div class="flex flex-row gap-4">
                        <div>
                            <img :src="form.create_thumbnail_url ? form.create_thumbnail_url : form?.thumbnail_url" 
                                alt="Course Thumbnail"  
                                class="h-64 rounded-t-lg object-cover transform transition-transform duration-300 shadow-lg hover:shadow-xl">
                            <label class="block text-md font-medium text-gray-600 mb-1">Upload Thumbnail</label>
                            <input
                                type="file"
                                @change="handleFileUpload(thumbnail_url, $event)"
                                class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    </div>

                    <div>
                                <iframe 
                        :src="form.create_content_url ? form.create_content_url : form?.content_url + '?autoplay=1'" 
                        class="h-64 rounded-md shadow-md border" 
                        frameborder="0" allowfullscreen>
                    </iframe>

                            <label class="block text-md font-medium text-gray-600 mb-1">Content URl</label>
                            <input
                                type="file"
                                @change="handleFileUpload(content_url, $event)"
                                class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    </div>

                    </div>
              
                    <div class="col-span-2">
                    <label class="block text-md font-medium">Description <span class="text-red-500">*</span></label>
                    <textarea v-model="form.description" placeholder="Write a brief description..."
                        class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-blue-500" minlength="10"></textarea>
                    </div>
                </div>
             <div class="col-span-1 space-y-6">
                <div>
                    <label class="block text-md font-medium">Content Type</label>
                    <select v-model="form.content_type"
                        class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option :value="1">📹 Video</option>
                        <option :value="2">📄 PDF</option>
                        <option :value="3">🖼️ Image</option>
                    </select>
                </div>
                <div>
                    <label class="block text-md font-medium">Duration</label>
                    <input v-model="form.hour" type="time"
                        class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500" required/>
                </div>
                <div>
                    <label class="block text-md font-medium">Status</label>
                    <select 
                        v-model="form.status"
                        class="w-full p-2 border rounded-md  text-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option :value="1">✔️ Published</option>
                        <option :value="2">📝 Draft</option>
                        <option :value="3">📦 Archived</option>
                    </select>
                </div>
             </div>

                <div class="col-span-2 flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="selectedModule ?  storeModuleContent() : updateSelectedContent()" 
                        class="text-white bg-lime-700 rounded-md px-4 py-1 text-md">
                        Update
                    </button>
                    <button 
                        type="button" 
                        @click="cancelEdit()" 
                        class="bg-gray-500 text-white rounded-md px-4 py-1 text-md">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>