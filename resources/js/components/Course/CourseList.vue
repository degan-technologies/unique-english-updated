<script setup>
    import { ref } from "vue";

    const collapsModuleId = ref(1);
    console.log('sdkjfksdhfj');

    const props = defineProps({
        selectedCourse:Object,
    })

    const emit = defineEmits(['openedLesson'])

    function toggleModuleLesson(id) {
        if(collapsModuleId.value == id){
            return collapsModuleId.value = null;
        }
        collapsModuleId.value = id;
    };
    function openLesson(moduleId, contentId) {
        emit('openedLesson', moduleId, contentId);
    }
</script>

<template>
    <div class="bg-white p-4 py-8 rounded-b-lg ">
         <div class="border-b mb-2  border-lime-700 text-lime-600">
            <h2 class="text-2xl leading-9 py-4 font-semibold"> Course Lesson </h2>
        </div>
        <div
            v-for="(courseModule, courseModuleIndex) in selectedCourse?.courseModules"
            :key="courseModuleIndex"
            class="mb-3 px-2" >
            <div class="flex justify-between items-center">
                <button
                    @click="toggleModuleLesson(courseModule.id)"
                    class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300" >
                    <span class="font-semibold text-lg">{{
                        courseModule.title
                    }}</span>
                    <i
                        :class="[
                            { 'rotate-90': collapsModuleId == courseModule.id },
                        ]"
                        class="fa-solid fa-angle-right text-xl transition-transform duration-300" > </i>
                </button>
            </div>

            <div v-if="collapsModuleId == courseModule.id" class="ml-4 mt-2">
                <ul class="list-none pl-0">
                    <li
                        v-for="( courseContent, courseContentIndex ) in courseModule?.courseContents"
                        :key="courseContentIndex"
                        @click="openLesson(courseModule.id, courseContent.id )"
                        class="text-gray-700 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1" >
                        <i 
                            :class="{
                                'fa-circle-play':courseContent.content_type == 1,
                                'fa-file-lines':courseContent.content_type == 2,
                                'fa-image':courseContent.content_type == 3,
                            }"
                            class="fa-solid px-8 text-lg w-5 h-5"></i>
                        {{ courseContent.title }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.course-details-container {
    max-height: calc(100vh - 48px);
}

.bg-green-500:hover,
.bg-blue-500:hover,
.bg-gray-500:hover {
    transition: background-color 0.3s ease;
}

.bg-green-500 {
    background-color: #38a169;
}

.bg-blue-500 {
    background-color: #3182ce;
}

.bg-gray-500 {
    background-color: #6b7280;
}
</style>
