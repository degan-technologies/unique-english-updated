<script setup>
import { ref, onMounted, watch, computed,watchEffect } from "vue";
import Axios from "axios";
import { useAppStore } from "@/store/useAppStore";

const appStore = useAppStore();
const authUser = appStore.authUser;

const props = defineProps({
  selectedCourse: Object,
  completedLessons: {
    type: Object, // expecting a Set
    default: () => new Set()
  }
});

const emit = defineEmits(["openedLesson", "openedQuiz", "updateProgress", "downloadCertificate"]);

const collapsModuleId = ref(null);
const selectedLessonId = ref(null);
const selectedQuizId = ref(null);
const completedLessons = ref(new Set());
const completedQuizzes = ref(new Set());
const progressRecords = ref({});
const downloadCertificate = ref(false);

const minWatchThreshold = 95;

function toggleModuleLesson(id) {
  collapsModuleId.value = collapsModuleId.value === id ? null : id;
}

function openLesson(moduleId, contentId) {
  selectedLessonId.value = contentId;
  selectedQuizId.value = null;
  emit("openedLesson", moduleId, contentId);
}

function openQuiz(moduleId, quizId) {
  selectedQuizId.value = quizId;
  selectedLessonId.value = null;
  emit("openedQuiz", moduleId, quizId);
}

async function fetchAllProgress() {
  try {
    const lessonResponse = await Axios.get("/api/coursecontent/progress", {
      params: { user_id: authUser.id }
    });

    lessonResponse.data.data.forEach(record => {
      progressRecords.value[record.course_content_id] = record;
      if (Number(record.progress) >= minWatchThreshold) {
        completedLessons.value.add(record.course_content_id);
      }
    });

    const quizResponse = await Axios.get("/api/results", {
      params: { user_id: authUser.id }
    });

    quizResponse.data.data.forEach(record => {
      completedQuizzes.value.add(record.quiz_id);
    });

    updateOverallProgress();
  } catch (error) {
    console.error("Error fetching progress records:", error);
  }
}

const updateOverallProgress = () => {
  if (!props.selectedCourse || !props.selectedCourse.courseModules) return 0;

  let totalItems = 0;
  let completedItems = 0;

  props.selectedCourse.courseModules.forEach(module => {
    if (module.courseContents) {
      totalItems += module.courseContents.length;
      completedItems += module.courseContents.filter(content => completedLessons.value.has(content.id)).length;
    }

    if (module.QMetaDatas) {
      totalItems += module.QMetaDatas.length;
      completedItems += module.QMetaDatas.filter(quiz => completedQuizzes.value.has(quiz.id)).length;
    }
  });

  const progress = totalItems > 0 ? Math.round((completedItems / totalItems) * 100) : 0;
  emit("updateProgress", progress);
  return progress;
 
};

const overallProgress = computed(() => updateOverallProgress());


onMounted(() => {
  fetchAllProgress();
  const saved = JSON.parse(localStorage.getItem("completedLessons")) || [];
  completedLessons.value = new Set(saved);
});

watch([completedLessons, completedQuizzes], () => {
  updateOverallProgress();
});

watchEffect(() => {
  updateOverallProgress();
});

</script>

<template>
  <div class="bg-white p-4 py-8 rounded-b-lg">
    <div class="border-b mb-2 border-lime-700 text-lime-600">
      <h2 class="text-2xl leading-9 py-4 font-semibold">Course Lesson</h2>
    </div>
    <div
      v-for="(courseModule, moduleIndex) in props.selectedCourse?.courseModules"
      :key="moduleIndex"
      class="mb-3 px-2"
    >
      <div class="flex justify-between items-center">
        <button
          @click="toggleModuleLesson(courseModule.id)"
          class="text-blue-500 hover:text-blue-700 text-lg w-full text-left p-3 rounded-lg flex items-center justify-between bg-gray-100 hover:bg-gray-200 transition-colors duration-300"
        >
          <span class="font-semibold text-lg">{{ courseModule.title }}</span>
          <i
            :class="{ 'rotate-90': collapsModuleId == courseModule.id }"
            class="fa-solid fa-angle-right text-xl transition-transform duration-300"
          ></i>
        </button>
      </div>

      <div v-if="collapsModuleId == courseModule.id" class="ml-4 mt-2">
    <ul class="list-none pl-0">
      <li
        v-for="(courseContent, contentIndex) in courseModule?.courseContents"
        :key="contentIndex"
        @click="openLesson(courseModule.id, courseContent.id)"
        class="flex items-center py-2 cursor-pointer rounded-lg transition-colors duration-300"
        :class="{
          'bg-blue-100 text-blue-600 font-bold': selectedLessonId === courseContent.id,
          'hover:bg-gray-200': selectedLessonId !== courseContent.id,
        }"
      >
     <!-- Always reserve space for the tick icon -->
<span
  v-if="completedLessons.has(courseContent.id)"
  class="text-green-500 font-bold mr-2"
>✔</span>
<span
  v-else
  class="text-green-500 font-bold mr-2"
  style="visibility: hidden;"
>✔</span>

<!-- Icon based on content type -->
<i
  :class="{
    'fa-circle-play': courseContent.content_type == 1,
    'fa-file-lines': courseContent.content_type == 2,
    'fa-image': courseContent.content_type == 3,
  }"
  class="fa-solid text-lg w-5 h-5 mr-2"
/>

<!-- Lesson title -->
<span>{{ courseContent.title }}</span>

      </li>
   <!-- Quiz Items -->
        <li
        v-for="(qMetaData, qMetaDataIndex) in courseModule.QMetaDatas"
        :key="'qMetaData-' + qMetaDataIndex"
        @click="openQuiz(courseModule.id, qMetaData.id)"
        class="flex items-center py-2 cursor-pointer my-1 transition-colors duration-300"
        :class="{
          'bg-blue-100 text-blue-600 font-bold': selectedQuizId === qMetaData.id,
          'hover:bg-gray-200': selectedQuizId !== qMetaData.id,
        }"
      >
       <!-- Show check mark if quiz is completed, else reserve space with an invisible check mark -->
<span
  v-if="completedQuizzes.has(qMetaData.id)"
  class="text-green-500 font-bold mr-2"
>✔</span>
<span
  v-else
  class="text-green-500 font-bold mr-2"
  style="visibility: hidden;"
>✔</span>
        <i class="fa-solid fa-clipboard-list text-lg w-5 h-5 mr-2"></i>
        <span class="font-semibold">Quiz {{ qMetaDataIndex + 1 }} - </span>
        <span class="ml-2">{{ qMetaData.title }}</span>
      </li>

    </ul>
  </div>
    </div>
    <button
  @click="$emit('downloadCertificate', true)"
  :disabled="overallProgress < 100"
  :class="overallProgress === 100 
    ? 'text-blue-500 hover:underline'
    : 'text-gray-400 cursor-not-allowed'"
  :title="overallProgress < 100 
    ? 'Complete all lessons and quizzes to download your certificate'
    : 'Download your certificate'"
  class="leading-relaxed text-lg py-2 flex items-center"
>
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
