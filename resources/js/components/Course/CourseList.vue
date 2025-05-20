<script setup>
import Axios from "axios";
import { ref, onMounted, watchEffect } from "vue";

const props = defineProps({
    selectedModules: Object,
    contentType: Object,
    completedLessons: Object,
    certify: Boolean,
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

function downloadCerteficate() {
    emit("downloadCertificate");
}

async function addCompletedLesson(id) {
    await Axios.get(`/api/completed-progress/${id}`) 
            .then(res =>{
                console.log(res.data.data);
            })
}

watchEffect(() => {
    collapsModuleId.value = props.contentType.id;
    selectedLessonId.value = props.contentType.lessonId;
    selectedQuizId.value = props.contentType.quizeId;
});

onMounted(() => {
    const saved = JSON.parse(localStorage.getItem("completedLessons")) || [];
    completedLessons.value = new Set(saved);
});

const isLessonCompleted = (lesson) => {
    return lesson?.courseContentProgress?.max_progress > 90;
};
</script>

<template>
    <div class="bg-white h-full p-2 rounded-b-lg">
        <div class="border-b mb-2 border-lime-700 text-lime-600">
            <h2 class="text-2xl leading-9 py-2 font-semibold">Course Lesson</h2>
        </div>
        <div v-for="(courseModule, moduleIndex) in selectedModules" :key="moduleIndex" class="mb-3 px-2">
            <div class="flex justify-between items-center">
                <button @click="toggleModuleLesson(courseModule.id)"
                    class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300">
                    <span class="font-semibold text-lg">{{
                        courseModule.title
                        }}</span>
                    <i :class="{
                        'rotate-90': collapsModuleId == courseModule.id,
                    }" class="fa-solid fa-angle-right text-xl transition-transform duration-300"></i>
                </button>
            </div>

            <div v-if="collapsModuleId == courseModule.id" class="mt-2">
                <ul class="list-none pl-0">
                    <li v-for="( courseContent, contentIndex ) in courseModule?.courseContents" 
                    :key="contentIndex"
                        @click="openLesson(courseModule, courseContent)"
                        class="flex items-center my-1 cursor-pointer border-b border-gray-100 w-full mx-auto gap-2 my-1 rounded-lg transition-colors duration-300"
                        :class="{
                            'bg-blue-100 text-blue-600 font-bold':
                                selectedLessonId === courseContent.id,
                            'hover:bg-gray-200':
                                selectedLessonId !== courseContent.id,
                        }">
                        <!-- Checkbox for completion status -->
                        <div class="w-6 h-6 flex items-center justify-center">
                            <input type="checkbox" 
                                :checked="isLessonCompleted(courseContent)"
                                @change="addCompletedLesson(courseContent.id)"
                                class="w-5 h-5 rounded border-gray-300 text-lime-600 focus:ring-lime-500 cursor-pointer"
                                @click.stop readonly />
                        </div>

                        <!-- Icon based on content type -->
                        <div class="w-10 rounded-sm text-center self-center">
                            <i :class="{
                                'fa-circle-play':
                                    courseContent.content_type == 1,
                                'fa-file-lines':
                                    courseContent.content_type == 2,
                                'fa-image': courseContent.content_type == 3,
                            }" class="fa-solid text-lg w-5 h-5"></i>
                        </div>

                        <!-- Lesson title -->
                        <div class="w-full">
                            <span class="self-senter">{{ courseContent.title }}</span>
                            <div v-if="courseContent.content_type == 1"
                                class="mb-1 flex flex-row gap-2 justify-between items-center">
                                <div>
                                    <p class="text-sm font-normal text-blue-700 py-1">
                                        {{
                                            courseContent?.courseContentProgress
                                                ?.video_progress
                                        }}/{{ courseContent.hour }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Quiz Items -->
                    <li v-for="( qMetaData, qMetaDataIndex ) in courseModule.QMetaDatas" 
                        :key="'qMetaData-' + qMetaDataIndex"
                        @click="openQuiz(courseModule, qMetaData)"
                        class="flex items-center cursor-pointer border-b border-gray-100 w-full mx-auto gap-2 my-1 rounded-lg transition-colors duration-300"
                        :class="{
                            'bg-blue-100 text-blue-600 font-bold':
                                selectedQuizId === qMetaData.id,
                            'hover:bg-gray-200':
                                selectedQuizId !== qMetaData.id,
                        }">
                        <!-- Checkbox for quiz completion -->
                        <div class="w-6 h-6 flex items-center self-center justify-center">
                            <input type="checkbox" :checked="qMetaData.is_completed"
                                class="w-5 h-5 rounded border-gray-300 text-lime-600 focus:ring-lime-500 cursor-pointer"
                                @click.stop readonly />
                        </div>

                        <div class="w-10 h-10 rounded-sm text-center self-center">
                            <i class="fa-solid fa-clipboard-list text-lg w-5 h-5"></i>
                        </div>
                        <span class="font-semibold self-center">Quiz {{ qMetaDataIndex + 1 }} -
                        </span>
                        <span class="">{{ qMetaData.title }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <button @click="downloadCerteficate()" :disabled="certify" :class="certify
                ? 'text-blue-500 hover:underline'
                : 'text-gray-400 cursor-not-allowed'
            " :title="certify
                    ? 'Complete all lessons and quizzes to download your certificate'
                    : 'Download your certificate'
                " class="leading-relaxed text-lg py-2 flex items-center">
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

/* Custom checkbox styling */
input[type="checkbox"] {
    -webkit-appearance: none;
    appearance: none;
    background-color: #fff;
    margin: 0;
    font: inherit;
    color: currentColor;
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid #d1d5db;
    border-radius: 0.25rem;
    display: grid;
    place-content: center;
}

input[type="checkbox"]::before {
    content: "";
    width: 0.65rem;
    height: 0.65rem;
    transform: scale(0);
    transition: 120ms transform ease-in-out;
    box-shadow: inset 1rem 1rem #16a34a;
    transform-origin: bottom left;
    clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
}

input[type="checkbox"]:checked::before {
    transform: scale(1);
}
</style>
