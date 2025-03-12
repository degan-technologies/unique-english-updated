<script setup>
import { storeToRefs } from "pinia";
import { onMounted, ref, watch, watchEffect, computed, nextTick,onBeforeUnmount  } from "vue";
import { useRoute } from "vue-router";
import { UseStudentStore } from "@/store/UseStudentStore";

import QA from "@/components/Exam/QA.vue";
import TextEditor from "@/components/Layout/TextEditor.vue";
import CourseList from "@/components/Course/CourseList.vue";
import ReviewList from "@/components/Course/ReviewList.vue";

const studentStore = UseStudentStore();
const { courses, selectedCourseSlug } = storeToRefs(studentStore);
const route = useRoute();
selectedCourseSlug.value = route.query.slug;

const selectedCourse = ref(null);
const selectedModule = ref(null);
const selectedLesson = ref(null);
const volumeControlsVisible = ref(false);
const totalDuration = ref(0);
const watchedTime = ref(0);
const videoWatched = ref(false); // ✅ Will be true if watched at least 95%
const minWatchThreshold = 0.95; 

// Tab management
const activeTab = ref("qa");
const setActiveTab = (tab) => {
  activeTab.value = tab;
};

// Video playback state
const video = ref(null);
const isPlaying = ref(false);
const showControls = ref(true);
const progress = ref(0);
const currentTime = ref("0:00");
const totalTime = ref("0:00");
const volume = ref(1);
const isMuted = ref(false);
const playbackRate = ref("default");
const selectedQuality = ref("1080p");
let hideControlsTimeout = null;

// Resets the seek bar and current time to 0
const resetSeekBar = () => {
  if (video.value) {
    video.value.currentTime = 0;
  }
  progress.value = 0;
  currentTime.value = formatTime(0);
};

const togglePlayPause = () => {
  if (!video.value) return;
  if (video.value.paused) {
    video.value.play();
    isPlaying.value = true;
  } else {
    video.value.pause();
    isPlaying.value = false;
  }
};

const handleSeekInput = (event) => {
  if (!video.value) return;
  const seekTime = (event.target.value / 100) * video.value.duration;
  if (isNaN(seekTime) || seekTime < 0 || seekTime > video.value.duration) return;
  if (video.value.readyState < 2) {
    video.value.addEventListener("loadeddata", () => {
      video.value.currentTime = seekTime;
    }, { once: true });
    return;
  }
  video.value.currentTime = seekTime;
  // Verify seek position after a brief delay
  setTimeout(() => {
    if (Math.abs(video.value.currentTime - seekTime) > 1) {
      video.value.currentTime = seekTime;
    }
  }, 300);
};

const setSeekTime = (desiredTime) => {
  if (video.value.readyState < 3) {
    video.value.addEventListener("canplay", () => {
      video.value.currentTime = desiredTime;
    }, { once: true });
    return;
  }
  if (video.value.seekable.length > 0) {
    const seekableStart = video.value.seekable.start(0);
    const seekableEnd = video.value.seekable.end(0);
    if (desiredTime < seekableStart || desiredTime > seekableEnd) return;
  }
  video.value.currentTime = desiredTime;
};


const updateProgress = () => {
  if (!video.value || totalDuration.value === 0) return;

  const dur = totalDuration.value;
  const current = video.value.currentTime;

  if (!dur || dur <= 0) {
    progress.value = 0;
    currentTime.value = formatTime(current);
  } else {
    progress.value = (current / dur) * 100;
    watchedTime.value = current; // ✅ Track watched time
    currentTime.value = formatTime(current);
  }

  // ✅ Ensure video has reached at least 95% before marking it as watched
  if (!videoWatched.value && watchedTime.value >= dur * minWatchThreshold) {
    videoWatched.value = true;
  }
};

// ✅ Triggered when video ends
const handleVideoEnd = () => {
  if (watchedTime.value >= totalDuration.value * minWatchThreshold) {
    videoWatched.value = true;
  }
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${mins}:${secs < 10 ? "0" : ""}${secs}`;
};

const resetControlsTimeout = () => {
  showControls.value = true;
  if (hideControlsTimeout) clearTimeout(hideControlsTimeout);
  startControlsHideTimer();
};

const startControlsHideTimer = () => {
  hideControlsTimeout = setTimeout(() => {
    if (isPlaying.value) showControls.value = false;
  }, 3000);
};

const handlePlay = () => {
  isPlaying.value = true;
  resetControlsTimeout();
  updateProgress();
};

const handlePause = () => {
  isPlaying.value = false;
  showControls.value = true;
  updateProgress();
};

const changeVolume = (event) => {
  if (!video.value) return;
  volume.value = event.target.value;  // Directly set the value
  video.value.volume = volume.value;
};


const toggleMute = () => {
  if (!video.value) return;
  video.value.muted = !video.value.muted;
  isMuted.value = video.value.muted;
};

const changePlaybackRate = () => {
  if (!video.value || playbackRate.value === "default") return;
  video.value.playbackRate = parseFloat(playbackRate.value);
};

const toggleFullscreen = () => {
  if (!video.value) return;
  if (document.fullscreenElement) {
    document.exitFullscreen();
  } else {
    video.value.requestFullscreen();
  }
};
const changeQuality = (newQuality) => {
  if (!selectedLesson.value || !selectedLesson.value.course_content_url) {
    console.error("Selected lesson or URL is missing.");
    return;
  }

  let currentUrl = new URL(selectedLesson.value.course_content_url);
  let params = new URLSearchParams(currentUrl.search);

  // Debugging the initial URL and current params
  console.log("Current URL:", currentUrl.toString());
  console.log("Current Params:", [...params.entries()]);

  // Update the quality parameter based on the selection
  if (newQuality === "Auto") {
    params.delete("quality"); // Remove quality param to default to original
    console.log("Removed quality param for Auto");
  } else {
    params.set("quality", newQuality); // Set the selected quality
    console.log("Updated quality param:", newQuality);
  }

  let newUrl = `${currentUrl.origin}${currentUrl.pathname}?${params.toString()}`;
  console.log("New URL with quality set:", newUrl); // Debugging the updated URL

  selectedLesson.value.course_content_url = newUrl;

  // Check if the video element exists and update it
  if (video.value) {
    video.value.src = newUrl; // Set the new video source
    console.log("Video source updated to:", newUrl); // Debugging the video source change
    video.value.load(); // Reload the video element
    resetSeekBar(); // Reset seek bar (if necessary)
    video.value.play(); // Play the video
    console.log("Video playback started.");
  } else {
    console.error("Video element not found.");
  }
};



const lessonContentUrl = computed({
  get() {
    return selectedLesson.value ? selectedLesson.value.course_content_url : "";
  },
  set(newValue) {
    if (selectedLesson.value) {
      selectedLesson.value.course_content_url = newValue;
    }
  }
});

// Computed streaming URL for the lesson's video
const streamingLessonUrl = computed(() => {
  if (!selectedLesson.value || !selectedLesson.value.course_content_url) return "";
  const parts = selectedLesson.value.course_content_url.split("/");
  const filename = parts[parts.length - 1];
  return `http://127.0.0.1:8000/api/coursecontent/stream/video/${filename}`;
});

const hasSeeked = ref(false);

function openedLesson(moduleId, contentId) {
  selectedModule.value = selectedCourse.value.courseModules.find(
    (courseModule) => courseModule.id == moduleId
  );
  selectedLesson.value = selectedModule.value.courseContents.find(
    (courseContent) => courseContent.id == contentId
  );
  if (selectedLesson.value && video.value) {
    video.value.src = streamingLessonUrl.value;
    video.value.load();
    resetSeekBar();
    video.value.play();
  }
}

const updateTotalTime = () => {
  if (!video.value || !video.value.duration) return;
  totalDuration.value = video.value.duration;
  totalTime.value = formatTime(video.value.duration);
};
watchEffect(() => {
  if (!courses.value) return;
  const foundCourse = courses.value.find(item => item.slug === selectedCourseSlug.value);
  if (!foundCourse) return;
  if (!selectedCourse.value || selectedCourse.value.slug !== foundCourse.slug) {
    selectedCourse.value = foundCourse;
    if (!hasSeeked.value) {
      selectedModule.value = selectedCourse.value.courseModules?.[0];
      selectedLesson.value = selectedModule.value?.courseContents?.[0];
    }
  }
});

watch(() => route.query.slug, () => {
  selectedCourseSlug.value = route.query.slug;
  selectedCourse.value = courses.value.find(
    (course) => course.slug === selectedCourseSlug.value
  );
});

// Reset seek bar when the streaming URL changes
watch(streamingLessonUrl, (newVal, oldVal) => {
  if (newVal && newVal !== oldVal && video.value) {
    resetSeekBar();
  }
});
// Keyboard controls for volume and seek
const handleKeyDown = (e) => {
  if (!video.value) return;
  let currentVolume = Number(volume.value);
  if (!isFinite(currentVolume)) {
    currentVolume = 0.5; // fallback to a default volume if not a number
  }
  
  if (e.key === 'ArrowUp') {
    // Increase volume by 5%
    e.preventDefault();
    let newVolume = Math.min(currentVolume + 0.05, 1);
    if (isFinite(newVolume)) {
      volume.value = newVolume;
      video.value.volume = newVolume;
    }
  } else if (e.key === 'ArrowDown') {
    // Decrease volume by 5%
    e.preventDefault();
    let newVolume = Math.max(currentVolume - 0.05, 0);
    if (isFinite(newVolume)) {
      volume.value = newVolume;
      video.value.volume = newVolume;
    }
  } else if (e.key === 'ArrowRight') {
    // Seek forward 5 seconds
    e.preventDefault();
    video.value.currentTime = Math.min(video.value.currentTime + 5, video.value.duration);
  } else if (e.key === 'ArrowLeft') {
    // Seek backward 5 seconds
    e.preventDefault();
    video.value.currentTime = Math.max(video.value.currentTime - 5, 0);
  }  else if (e.key === ' ' || e.code === 'Space') {
    e.preventDefault();
    togglePlayPause();
  }
};

onMounted(() => {
  studentStore.fetchCourses();
  if (!video.value) {
    console.error("Video element not found in onMounted!");
    return;
  }
  video.value.volume = volume.value;
  video.value.playbackRate = parseFloat(playbackRate.value) || 1.0;
  video.value.addEventListener("loadedmetadata", updateTotalTime);
  video.value.addEventListener("timeupdate", updateProgress);
  window.addEventListener("keydown", handleKeyDown);
  video.value.load();
});
onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeyDown);
});
</script>

<template>
  <div v-if="selectedCourseSlug" class="grid w-[90%] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr] gap-8 relative mt-24 bg-slate-50">
    <div class="flex-1 flex flex-col gap-4">
      <div
    class="relative bg-black rounded-lg overflow-hidden border-2 border-lime-700"
    @mousemove="resetControlsTimeout"
    @mouseleave="startControlsHideTimer"
    @mouseenter="resetControlsTimeout"
    @click="togglePlayPause"
  >
    <!-- Video Element (using Video.js if needed, but here it's a standard HTML5 video) -->
    <video
      ref="video"
      class="w-full h-full object-cover pointer-events-none"
      :src="streamingLessonUrl"
      @timeupdate="updateProgress"
      @loadedmetadata="updateTotalTime"
      @ended="handleVideoEnd"
      @play="handlePlay"
      @pause="handlePause"
      playsinline
    >
      Your browser does not support the video tag.
    </video>

    <!-- Custom Controls Overlay -->
    <div
      v-show="showControls"
      class="absolute bottom-0 left-0 right-0 p-3 bg-lime-700 bg-opacity-100 transition-opacity duration-300 z-20 gap-4"
      @click.stop
    >
      <!-- MOBILE LAYOUT: below sm breakpoint -->
      <div class="flex flex-col gap-2 sm:hidden">
        <!-- Row 1: Volume & Time Display -->
        <div class="flex items-center justify-between">
          <!-- Volume Controls -->
          <div
            class="relative flex items-center overflow-hidden transition-all duration-300"
            :class="volumeControlsVisible ? 'w-40' : 'w-12'"
            @mouseenter="volumeControlsVisible = true"
            @mouseleave="volumeControlsVisible = false"
          >
            <button
              @click="toggleMute"
              class="text-white p-2"
              aria-label="Mute/Unmute"
            >
              <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
            </button>
            <div v-if="volumeControlsVisible" class="flex items-center space-x-2 ml-2">
              <span class="text-xs text-white">{{ Math.round(volume * 100) }}%</span>
              <input
                type="range"
                :value="volume"
                @input="changeVolume"
                min="0"
                max="1"
                step="0.01"
                class="w-16 h-1 rounded-md bg-gray-300"
                aria-label="Volume Control"
              />
            </div>
          </div>
          <!-- Time Display -->
          <div class="text-white text-sm whitespace-nowrap">
            <span>{{ currentTime }}</span> / <span>{{ totalTime }}</span>
          </div>
        </div>
        <!-- Row 2: Seek Bar -->
        <div>
          <input
            type="range"
            :value="progress"
            @input="handleSeekInput"
            min="0"
            max="100"
            step="0.01"
            class="w-full h-1 bg-white rounded-md transition-all duration-300"
            aria-label="Seek Video"
          />
        </div>
        <!-- Row 3: Quality, Playback Rate, and Fullscreen -->
        <div class="flex items-center justify-end gap-2">
          <select
            @change="changeQuality($event.target.value)"
            class="text-white text-sm bg-lime-700 p-1 rounded"
          >
            <option value="Auto">Auto</option>
            <option value="1080p">1080p</option>
            <option value="720p">720p</option>
            <option value="480p">480p</option>
          </select>
          <select
            v-model="playbackRate"
            @change="changePlaybackRate"
            class="text-white text-sm bg-lime-700 p-1 rounded"
          >
            <option disabled value="default">1x</option>
            <option value="0.5">0.5x</option>
            <option value="1.0">1x</option>
            <option value="1.5">1.5x</option>
            <option value="2.0">2x</option>
          </select>
          <button
            @click="toggleFullscreen"
            class="text-white flex items-center p-2"
            aria-label="Fullscreen"
          >
            <i class="fas fa-expand"></i>
          </button>
        </div>
      </div>

      <!-- DESKTOP LAYOUT: sm and above -->
      <div class="hidden sm:flex sm:items-center sm:gap-4">
        <!-- Play/Pause Button -->
        <button
          @click="togglePlayPause"
          class="text-white text-sm flex items-center"
          aria-label="Play/Pause"
        >
          <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
        </button>
        <!-- Volume Controls & Seek Bar Container -->
        <div class="flex flex-1 items-center gap-4">
          <!-- Volume Controls -->
          <div
            class="relative flex items-center overflow-hidden transition-all duration-300"
            :class="volumeControlsVisible ? 'w-40' : 'w-12'"
            @mouseenter="volumeControlsVisible = true"
            @mouseleave="volumeControlsVisible = false"
          >
            <button
              @click="toggleMute"
              class="text-white p-2"
              aria-label="Mute/Unmute"
            >
              <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
            </button>
            <div v-if="volumeControlsVisible" class="flex items-center space-x-2 ml-2">
              <span class="text-xs text-white">{{ Math.round(volume * 100) }}%</span>
              <input
                type="range"
                :value="volume"
                @input="changeVolume"
                min="0"
                max="1"
                step="0.01"
                class="w-16 h-1 rounded-md bg-gray-300"
                aria-label="Volume Control"
              />
            </div>
          </div>
          <!-- Seek Bar -->
          <div class="flex-1">
            <input
              type="range"
              :value="progress"
              @input="handleSeekInput"
              min="0"
              max="100"
              step="0.01"
              class="w-full h-1 bg-white rounded-md transition-all duration-300"
              aria-label="Seek Video"
            />
          </div>
        </div>
        <!-- Time Display -->
        <div class="text-white text-sm whitespace-nowrap">
          <span>{{ currentTime }}</span> / <span>{{ totalTime }}</span>
        </div>
        <!-- Quality Selection -->
        <select
          @change="changeQuality($event.target.value)"
          class="text-white text-sm bg-lime-700 p-1 rounded"
        >
          <option value="Auto">Auto</option>
          <option value="1080p">1080p</option>
          <option value="720p">720p</option>
          <option value="480p">480p</option>
        </select>
        <!-- Playback Rate Selection -->
        <select
          v-model="playbackRate"
          @change="changePlaybackRate"
          class="text-white text-sm bg-lime-700 p-1 rounded"
        >
          <option disabled value="default">1x</option>
          <option value="0.5">0.5x</option>
          <option value="1.0">1x</option>
          <option value="1.5">1.5x</option>
          <option value="2.0">2x</option>
        </select>
        <!-- Fullscreen Button -->
        <button
          @click="toggleFullscreen"
          class="text-white flex items-center p-2"
          aria-label="Fullscreen"
        >
          <i class="fas fa-expand"></i>
        </button>
      </div>
    </div>
  </div>

      <div class="bg-white p-4 py-8 rounded-b-lg">
        <div class="flex justify-between">
          <div class="flex flex-col">
            <div v-if="videoWatched">
  <span class="text-green-500 font-bold">✔ Lesson Completed</span>
</div>
            <p class="text-lg text-gray-600 py-2">{{ selectedLesson?.title }}</p>
            <p class="text-2xl text-blue-600">{{ selectedModule?.title }}</p>
          </div>

          <!-- Global Progress Circle -->
          <div class="relative mt-2">
            <div
              class="w-16 h-16 bg-sky-700 rounded-full flex items-center justify-center"
              role="progressbar"
              aria-valuemin="0"
              aria-valuemax="100" >
              <span class="text-lg font-bold text-white"> 0 % </span>
            </div>
            <p class="text-center text-sm mt-2 text-gray-700"> Progress: 0 % </p>
          </div>
        </div>
        <div class="flex justify-start mt-8 border-b-2 border-gray-200 pb-4 gap-4">
          <button
            @click="setActiveTab('qa')"
            class="tab-button text-black px-4 py-2 rounded-lg text-lg font-semibold"
            :class="{
                'bg-lime-800 text-white': activeTab === 'qa',
                'hover:bg-lime-500': activeTab !== 'qa',
            }"
          >
            <i class="fas fa-question"></i> Q&A
          </button>
          <button
            @click="setActiveTab('editor')"
            class="tab-button text-black px-4 py-2 rounded-lg text-lg font-semibold"
            :class="{
                'bg-lime-800 text-white': activeTab === 'editor',
                'hover:bg-lime-500': activeTab !== 'editor',
            }"
          >
            <i class="fas fa-pen"></i> Notes
          </button>
          <button
            @click="setActiveTab('reviews')"
            class="tab-button text-black px-4 py-2 rounded-lg text-lg font-semibold"
            :class="{
                'bg-lime-800 text-white': activeTab === 'reviews',
                'hover:bg-lime-500': activeTab !== 'reviews',
            }"
          >
            <i class="fas fa-star"></i> Reviews
          </button>
        </div>

        <!-- Tab Content -->
        <div v-if="activeTab === 'qa'" class="pt-4">
          <QA />
        </div>
        <div v-if="activeTab === 'editor'" class="mt-4">
          <TextEditor />
        </div>
        <div v-if="activeTab === 'reviews'" class="mt-4">
          <ReviewList  
            :feedBacks="selectedCourse?.feedBacks"
            :averageRating="selectedCourse?.averageRating"
            :starDistribution="selectedCourse?.starDistribution" />
        </div>
      </div>
    </div>

    <!-- Course List Section (now on the right side) -->
    <div class="">
      <CourseList 
        v-if="selectedCourse"
        :selectedCourse="selectedCourse"
        @openedLesson="openedLesson"/>
    </div>
  </div>
</template>
