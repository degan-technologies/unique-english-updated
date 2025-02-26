<script setup>
    import Axios from 'axios';
    import { onUnmounted, ref } from 'vue';
    import { storeToRefs } from "pinia";

    import { useInstructorStore } from "@/store/useInstructorStore";

    const InstructorStore = useInstructorStore();
    const { selectedCourse, editCourseModule, courseId } = storeToRefs(InstructorStore);

    const title =  ref('');   
    const description =  ref('');

    const props = defineProps({
        actionType:String,
    });

    if(editCourseModule.value?.id) {
        title.value = editCourseModule.value.title;
        description.value= editCourseModule.value.description;
    }

    function addCourseModule() {

        Axios
            .post("/api/courses/module", {
            title: title.value,
            description: description.value,
            course_id: courseId.value,
            })
            .then(res => {
                title.value = ""; 
                description.value = "";
                courseId.value = null;
            })
            .catch(err=>{});
    };

    function updateCourseModule() {
        let data = {
                title: title.value,
                description: description.value,
            }

        Axios
            .patch(`/api/courses/module/${editCourseModule.value?.id}`, data)
            .then(res => {
                selectedCourse.value.courseModules = selectedCourse.value.courseModules.map(item => item =  item.id === res.data.data.id ? res.data.data : item);
                editCourseModule.value = false; 
            })
            .catch(err=>{});
    };

    onUnmounted(() => {
        title.value = "";
        description.value = "";

        courseId.value = null;
        editCourseModule.value = null;
    });
</script>
<template>
    <div  class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="w-10/12 sm:max-w-xl bg-white p-6 rounded-lg shadow-lg overflow-auto max-h-[85vh]">
            <h2 class="text-xl font-semibold mb-4">{{ actionType == 'STORE' ? 'Add Course Module' : 'Edit Module' }}</h2>

            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input v-model="title" type="text" 
                class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-purple-300" 
                placeholder="Module Title" required />
            <label class="block text-sm font-medium text-gray-700 mt-3">Description</label>
            <textarea v-model="description" 
                class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-purple-300" 
                placeholder="Module Description"></textarea>
            <div class="mt-4 flex justify-end">

            <button @click="actionType == 'STORE' ? courseId = null : editCourseModule = null" 
                class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">Cancel</button>
            <button @click="actionType == 'STORE' ? addCourseModule() : updateCourseModule()" 
                class="ml-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Save</button>
            </div>
        </div>
    </div>
</template>