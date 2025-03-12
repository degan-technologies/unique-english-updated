<script setup>
import { ref, watch, onMounted } from "vue";

const collapsModuleId = ref(1);
const selectedLessonId = ref(null);
const completedLessons = ref(new Set()); // Store completed lessons
const videoRef = ref(null); // Video reference

const props = defineProps({
  selectedCourse: Object,
});

const emit = defineEmits(["openedLesson"]);

// Toggle module lesson expansion
function toggleModuleLesson(id) {
  collapsModuleId.value = collapsModuleId.value === id ? null : id;
}

// Open a lesson and emit event
function openLesson(moduleId, contentId) {
  selectedLessonId.value = contentId;
  emit("openedLesson", moduleId, contentId);
}

// Mark lesson as completed when the video finishes
function markLessonCompleted() {
  if (selectedLessonId.value) {
    completedLessons.value.add(selectedLessonId.value);
    localStorage.setItem("completedLessons", JSON.stringify([...completedLessons.value]));
  }
}

// Watch for lesson changes and attach video event listener
watch(selectedLessonId, () => {
  if (videoRef.value) {
    videoRef.value.removeEventListener("ended", markLessonCompleted);
    videoRef.value.addEventListener("ended", markLessonCompleted);
  }
});

// Load completed lessons from localStorage on mount
onMounted(() => {
  const savedLessons = JSON.parse(localStorage.getItem("completedLessons")) || [];
  completedLessons.value = new Set(savedLessons);
});
</script>

<template>
  <div class="bg-white p-4 py-8 rounded-b-lg">
    <div class="border-b mb-2 border-lime-700 text-lime-600">
      <h2 class="text-2xl leading-9 py-4 font-semibold">Course Lesson</h2>
    </div>

    <div
      v-for="(courseModule, courseModuleIndex) in selectedCourse?.courseModules"
      :key="courseModuleIndex"
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
            v-for="(courseContent, courseContentIndex) in courseModule?.courseContents"
            :key="courseContentIndex"
            @click="openLesson(courseModule.id, courseContent.id)"
            class="text-gray-700 flex leading-relaxed text-lg py-2 cursor-pointer items-center my-1 rounded-lg transition-all duration-300"
            :class="{
              'bg-blue-100 text-blue-600 font-bold': selectedLessonId === courseContent.id, // Highlight active lesson
              'hover:bg-gray-200': selectedLessonId !== courseContent.id, // Normal hover effect
            }"
          >
            <i
              :class="{
                'fa-circle-play': courseContent.content_type == 1,
                'fa-file-lines': courseContent.content_type == 2,
                'fa-image': courseContent.content_type == 3,
              }"
              class="fa-solid px-8 text-lg w-5 h-5"
            ></i>
            {{ courseContent.title }}

            <!-- ✅ Tick icon for completed lessons -->
            <i
              v-if="completedLessons.has(courseContent.id)"
              class="fa-solid fa-check-circle text-green-500 ml-4"
            ></i>
          </li>
        </ul>
      </div>
    </div>

    <!-- Video Player -->
    <video ref="videoRef" controls class="w-full mt-4">
      <source src="your-video-source.mp4" type="video/mp4" />
      Your browser does not support the video tag.
    </video>
  </div>
</template>

<style scoped>
/* Styling for selected lesson */
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
