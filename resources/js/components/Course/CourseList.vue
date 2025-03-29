<script setup>
    import { ref, onMounted, watch, computed, watchEffect } from "vue";

    const props = defineProps({
        selectedModules: Object,
        contentType: Object,
        completedLessons: Object
    });

    const emit = defineEmits(["openedLesson", "openedQuiz", "downloadCertificate"]);

    const selectedLessonId = ref(null);
    const collapsModuleId = ref(null);
    const selectedQuizId = ref(null);
    const completedLessons = ref(new Set());

    function toggleModuleLesson(id) {
        collapsModuleId.value = collapsModuleId.value === id ? null : id;
    }

    function openLesson(module, lesson) {
        selectedLessonId.value = lesson.id;
        selectedQuizId.value = null;
        emit("openedLesson", module, lesson);
    }

    function openQuiz(module, qMetaData) {
        selectedQuizId.value = qMetaData.id;
        selectedLessonId.value = null;
        emit("openedQuiz", module, qMetaData);
    }
    
    watchEffect(()=>{
        collapsModuleId.value = props.contentType.id;
        selectedLessonId.value = props.contentType.lessonId;
        selectedQuizId.value = props.contentType.quizeId;
    })
    
    onMounted(() => {
        const saved = JSON.parse(localStorage.getItem("completedLessons")) || [];
        completedLessons.value = new Set(saved);
    });
</script>

<template>
    <div class="bg-white h-full p-2  rounded-b-lg">
        <div class="border-b mb-2 border-lime-700 text-lime-600">
            <h2 class="text-2xl leading-9 py-2 font-semibold">Course Lesson</h2>
        </div>
        <div v-for="(courseModule, moduleIndex) in selectedModules" :key="moduleIndex" class="mb-3 px-2">
            <div class="flex justify-between items-center">
                <button @click="toggleModuleLesson(courseModule.id)"
                    class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300">
                    <span class="font-semibold text-lg">{{ courseModule.title }}</span>
                    <i :class="{ 'rotate-90': collapsModuleId == courseModule.id }"
                        class="fa-solid fa-angle-right text-xl transition-transform duration-300"></i>
                </button>
            </div>

            <div v-if="collapsModuleId == courseModule.id" class=" mt-2">
                <ul class="list-none pl-0">
                    <li v-for="(courseContent, contentIndex) in courseModule?.courseContents" :key="contentIndex"
                        @click="openLesson(courseModule, courseContent)"
                        class="flex items-center cursor-pointer border-b border-gray-100 w-full mx-auto gap-2 my-1 rounded-lg transition-colors duration-300"
                        :class="{
                            'bg-blue-100 text-blue-600 font-bold': selectedLessonId === courseContent.id,
                            'hover:bg-gray-200': selectedLessonId !== courseContent.id,
                        }">

                        <!-- Icon based on content type -->
                        <div>
                            <div v-if="courseContent.content_type !== 1"
                                class="w-10 h-10 rounded-sm text-center self-center">
                                <i :class="{
                                    'fa-file-lines': courseContent.content_type == 2,
                                    'fa-image': courseContent.content_type == 3,
                                }" class="fa-solid text-lg  self-center py-2" />

                            </div>
                            <div v-else class="w-10 h-10 rounded-sm self-center shadow-sm">
                                <img class="w-10 h-10 rounded object-cover" :src="courseContent.thumbnail_url"
                                    alt="image">
                            </div>
                        </div>

                        <!-- Lesson title -->
                        <div class="w-full">
                            <span class="self-senter">{{ courseContent.title }}</span>
                            <div 
                                v-if="courseContent.content_type == 1"  
                                class="mb-1 flex flex-row gap-2 justify-between items-center">
                                <div>
                                    <p class="text-sm font-normal text-blue-700 py-1">{{ courseContent?.courseContentProgress?.video_progress }}/{{ courseContent.hour }}</p>
                                </div>
                                <!-- courseContent?.courseContentProgress?.max_progress -->
                                <div class=" rounded-lg self-center">
                                    <p  
                                        v-if="courseContent?.courseContentProgress?.max_progress > 90" 
                                        class="text-sm p-1 font-normal text-lime-600 ">Completed</p>
                                </div>
                            </div>
                        </div>

                    </li>

                    <!-- Quiz Items -->
                    <li v-for="(qMetaData, qMetaDataIndex) in courseModule.QMetaDatas"
                        :key="'qMetaData-' + qMetaDataIndex"
                        @click="openQuiz(courseModule, qMetaData)"
                        class="flex items-center cursor-pointer border-b border-gray-100 w-full mx-auto gap-2 my-1 rounded-lg transition-colors duration-300"
                       :class="{
                            'bg-blue-100 text-blue-600 font-bold': selectedQuizId === qMetaData.id,
                            'hover:bg-gray-200': selectedQuizId !== qMetaData.id,
                        }"
                        >
                        <!-- Show check mark if quiz is completed, else reserve space with an invisible check mark -->
                        <div class="w-10 h-10 rounded-sm text-center self-center ">
                            <i class="fa-solid fa-clipboard-list text-lg self-center  py-2"></i>
                        </div>
                        <span class="font-semibold self-center">Quiz {{ qMetaDataIndex + 1 }} - </span>
                        <span class="">{{ qMetaData.title }}</span>
                    </li>

                </ul>
            </div>
        </div>
        <button @click="$emit('downloadCertificate', true)" :disabled="overallProgress < 100" :class="overallProgress === 100
            ? 'text-blue-500 hover:underline'
            : 'text-gray-400 cursor-not-allowed'" :title="overallProgress < 100
            ? 'Complete all lessons and quizzes to download your certificate'
            : 'Download your certificate'" class="leading-relaxed text-lg py-2 flex items-center">
            <i class="fas fa-certificate text-teal-500 mr-2 ml-2"></i>
            <strong>Certificate of Completion</strong>
        </button>
    </div>
</template>

<style scoped>
    /* TailwindCSS is used for most styling. The scoped style below is for minor adjustments if needed. */
    .bg-blue-100 {
        background-color: #ebf8ff;
    }

    .text-blue-600 {
        color: #3182ce;
    }

    .text-green-500 {
        color: #38a169;
    }
</style>
