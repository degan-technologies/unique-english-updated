<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import Axios from 'axios';

const MIN_WATCH_THRESHOLD = 0.95;
const PROGRESS_UPDATE_INTERVAL = 5000; // Update progress every 5 seconds

// Video element ref
const video = ref(null);

// Video controls
const isPlaying = ref(false);
const showControls = ref(true);
const volume = ref(1);
const isMuted = ref(false);
const playbackRate = ref("1.0");
const isLoading = ref(false);
const volumeControlsVisible = ref(false);
const totalDuration = ref(0);
const watchedTime = ref(0);
const videoWatched = ref(false);
const bufferedPercentage = ref(0);
let hideControlsTimeout = null;
let progressUpdateInterval = null;

const progress = ref(0);
const currentTime = ref("0:00");
const totalTime = ref("0:00");

const props = defineProps({
    selectedLesson: {
        type: Object,
        required: true,
    },
});

const streamVideo = computed(() => props.selectedLesson.course_content_url);

// Initialize player
const initPlayer = () => {
    if (video.value) {
        // Set initial volume
        video.value.volume = volume.value;
        video.value.muted = isMuted.value;
        video.value.playbackRate = parseFloat(playbackRate.value);

        // Start progress tracking
        startProgressTracking();
    }
};

// Progress tracking
const startProgressTracking = () => {
    // Clear existing interval if any
    if (progressUpdateInterval) clearInterval(progressUpdateInterval);

    // Update progress immediately
    updateVideoProgress();

    // Set up periodic updates
    progressUpdateInterval = setInterval(updateVideoProgress, PROGRESS_UPDATE_INTERVAL);
};

const updateVideoProgress = () => {
    if (!video.value || !totalDuration.value) return;

    const current = video.value.currentTime;
    watchedTime.value = current;
    currentTime.value = formatTime(current);
    progress.value = (current / totalDuration.value) * 100;

    // Update buffered percentage
    updateBufferedPercentage();

    // Save progress if significant change
    if (current > 0 && current % 30 < 0.5) { // Every ~30 seconds
        storeContentProgress();
    }
};

const updateBufferedPercentage = () => {
    if (video.value && video.value.buffered.length > 0) {
        const bufferedEnd = video.value.buffered.end(video.value.buffered.length - 1);
        bufferedPercentage.value = (bufferedEnd / totalDuration.value) * 100;
    }
};

const resetControlsTimeout = () => {
    showControls.value = true;
    if (hideControlsTimeout) clearTimeout(hideControlsTimeout);
    startControlsHideTimer();
};

const togglePlayPause = () => {
    if (!video.value) return;
    if (video.value.paused) {
        video.value.play().catch(e => console.error("Play error:", e));
    } else {
        video.value.pause();
    }
};

const toggleMute = () => {
    if (!video.value) return;
    video.value.muted = !video.value.muted;
    isMuted.value = video.value.muted;
};

const handleSeekInput = (event) => {
    if (!video.value) return;
    const seekTime = (event.target.value / 100) * video.value.duration;

    if (isNaN(seekTime)) return;

    // Immediately update progress display
    progress.value = event.target.value;
    currentTime.value = formatTime(seekTime);

    if (video.value.readyState < 2) {
        video.value.addEventListener(
            "loadeddata",
            () => { video.value.currentTime = seekTime; },
            { once: true }
        );
        return;
    }

    video.value.currentTime = seekTime;
    isLoading.value = false;
};

const handleVideoEnd = () => {
    if (watchedTime.value >= totalDuration.value * MIN_WATCH_THRESHOLD) {
        videoWatched.value = true;
    }
    storeContentProgress();
};

const handlePlay = () => {
    isPlaying.value = true;
    isLoading.value = false;
    resetControlsTimeout();
};

const handlePause = () => {
    isPlaying.value = false;
    showControls.value = true;
    storeContentProgress();
};

const changeVolume = (event) => {
    if (!video.value) return;
    volume.value = event.target.value;
    video.value.volume = volume.value;
    isMuted.value = volume.value === 0;
};

const handleTimeUpdate = () => {
    updateVideoProgress();
};

const handleWaiting = () => {
    isLoading.value = true;
};

const handleCanPlay = () => {
    isLoading.value = false;
};

const changePlaybackRate = () => {
    if (!video.value || !playbackRate.value) return;
    video.value.playbackRate = parseFloat(playbackRate.value);
};

const startControlsHideTimer = () => {
    hideControlsTimeout = setTimeout(() => {
        if (isPlaying.value) showControls.value = false;
    }, 3000);
};

function storeContentProgress() {
    if (!video.value || !totalDuration.value) return;
    const current = video.value.currentTime;
    const newProgress = new Date(current * 1000).toISOString().substr(11, 8);

    Axios.post("/api/coursecontent/progress", {
        course_content_id: props.selectedLesson.id,
        progress: newProgress,
        duration_watched: current,
        total_duration: totalDuration.value,
        is_completed: videoWatched.value
    }).catch(error => console.error("Progress save error:", error));
}

const updateTotalTime = () => {
    if (!video.value?.duration) return;
    isLoading.value = false;
    totalDuration.value = video.value.duration;
    totalTime.value = formatTime(video.value.duration);

    if (props.selectedLesson?.courseContentProgress?.current_time) {
        video.value.currentTime = props.selectedLesson.courseContentProgress.current_time;
    }

    // Initialize player after metadata is loaded
    initPlayer();
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs < 10 ? "0" : ""}${secs}`;
};

const handleKeyDown = (event) => {
    if (!video.value) return;

    switch (event.key) {
        case ' ':
            event.preventDefault();
            togglePlayPause();
            break;
        case 'ArrowRight':
            video.value.currentTime += 5;
            break;
        case 'ArrowLeft':
            video.value.currentTime -= 5;
            break;
        case 'm':
            toggleMute();
            break;
        case 'ArrowUp':
            volume.value = Math.min(1, volume.value + 0.1);
            video.value.volume = volume.value;
            break;
        case 'ArrowDown':
            volume.value = Math.max(0, volume.value - 0.1);
            video.value.volume = volume.value;
            break;
    }
};

onMounted(() => {
    document.addEventListener("keydown", handleKeyDown);
});

onBeforeUnmount(() => {
    document.removeEventListener("keydown", handleKeyDown);
    if (hideControlsTimeout) clearTimeout(hideControlsTimeout);
    if (progressUpdateInterval) clearInterval(progressUpdateInterval);
});
</script>

<template>
    <div class="video-container relative bg-black h-96 rounded-lg overflow-hidden border-2 border-lime-700" tabindex="0"
        @mousemove="resetControlsTimeout" @mouseleave="startControlsHideTimer" @mouseenter="resetControlsTimeout"
        @click="togglePlayPause">
        <video ref="video" class="w-full h-full object-contain" :src="streamVideo"
            :poster="props.selectedLesson?.thumbnail_url" @loadedmetadata="updateTotalTime" @ended="handleVideoEnd"
            @play="handlePlay" @pause="handlePause" @waiting="handleWaiting" @canplay="handleCanPlay"
            @timeupdate="handleTimeUpdate" @progress="updateBufferedPercentage" playsinline>
            Your browser does not support the video tag.
        </video>

        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="spinner">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"></div>
            </div>
        </div>

        <!-- Controls Overlay -->
        <div v-show="showControls"
            class="absolute bottom-0 left-0 right-0 pl-2 pr-2 pb-1 bg-lime-700 transition-opacity duration-300 z-20 gap-2"
            @click.stop>
            <!-- Seek Bar with buffered indicator -->
            <div class="relative w-full h-4">
            <!-- Buffered Bar -->
            <div class="absolute top-1/2 left-0 h-1 w-full bg-gray-400 rounded-md transform -translate-y-1/2">
                <div class="bg-gray-400 h-1 rounded-md" :style="{ width: bufferedPercentage + '%' }"></div>
            </div>

            <!-- Progress Bar -->
            <div class="absolute top-1/2 left-0 h-1 w-full bg-transparent rounded-md z-10 transform -translate-y-1/2">
                <div class="bg-blue-500 h-1 rounded-md" :style="{ width: progress + '%' }"></div>
            </div>

            <!-- Seek Input -->
            <input 
                type="range"
                :value="progress"
                @input="handleSeekInput"
                min="0"
                max="100"
                step="0.01"
                class="absolute top-1/2 left-0 h-1 w-full bg-transparent rounded-md z-10 transform -translate-y-1/2"
                aria-label="Seek Video"
            />
            </div>

            <!-- MOBILE CONTROLS -->
            <div class="flex flex-col sm:hidden" style="padding: 4px 0">
                <div class="flex items-center justify-between px-2">
                    <button @click="togglePlayPause" class="text-white p-1" aria-label="Play/Pause">
                        <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
                    </button>

                    <div class="flex items-center space-x-1">
                        <button @click="toggleMute" class="text-white p-1" aria-label="Mute/Unmute">
                            <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                        </button>
                        <input type="range" :value="volume" @input="changeVolume" min="0" max="1" step="0.01"
                            class="w-12 h-1 rounded-md bg-gray-300" aria-label="Volume Control" />
                    </div>

                    <span class="text-white text-xs whitespace-nowrap mx-2">
                        {{ currentTime }}/{{ totalTime }}
                    </span>

                    <select @change="changePlaybackRate" v-model="playbackRate"
                        class="text-white text-xs bg-lime-700 p-1 rounded mr-1" style="max-width: 60px">
                        <option value="0.5">0.5x</option>
                        <option value="1.0">1x</option>
                        <option value="1.5">1.5x</option>
                        <option value="2.0">2x</option>
                    </select>
                </div>
            </div>

            <!-- DESKTOP CONTROLS -->
            <div class="hidden sm:flex sm:items-center sm:gap-4">
                <button @click="togglePlayPause" class="text-white text-sm flex items-center" aria-label="Play/Pause">
                    <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
                </button>

                <div class="flex-1 flex items-center gap-4">
                    <div class="relative flex items-center overflow-hidden transition-all duration-300"
                        :class="volumeControlsVisible ? 'w-40' : 'w-12'" @mouseenter="volumeControlsVisible = true"
                        @mouseleave="volumeControlsVisible = false">
                        <button @click="toggleMute" class="text-white p-2" aria-label="Mute/Unmute">
                            <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                        </button>
                        <div v-if="volumeControlsVisible" class="flex items-center space-x-1 ml-2">
                            <span class="text-xs text-white">
                                {{ Math.round(volume * 100) }}%
                            </span>
                            <input type="range" :value="volume" @input="changeVolume" min="0" max="1" step="0.01"
                                class="w-16 h-1 rounded-md bg-gray-300" aria-label="Volume Control" />
                        </div>
                    </div>
                </div>

                <div class="text-white text-sm whitespace-nowrap">
                    <span>{{ currentTime }}</span> / <span>{{ totalTime }}</span>
                </div>

                <select v-model="playbackRate" @change="changePlaybackRate"
                    class="text-white text-sm bg-lime-700 p-1 rounded">
                    <option value="0.5">0.5x</option>
                    <option value="1.0">1x</option>
                    <option value="1.5">1.5x</option>
                    <option value="2.0">2x</option>
                </select>
            </div>
        </div>
    </div>
</template>

<style scoped>
.video-container {
    position: relative;
}

.video-container:focus {
    outline: none;
}

.spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* Custom range input styling */
input[type="range"] {
    -webkit-appearance: none;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    cursor: pointer;
}

/* Buffered progress styling */
.buffered-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 2px;
    background-color: rgba(255, 255, 255, 0.3);
    z-index: 1;
}
.seek-range {
  -webkit-appearance: none;
  appearance: none;
  background: transparent;
  cursor: pointer;
  padding: 0;
  margin: 0;
}

/* Thumb styling */
.seek-range::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 12px;
  height: 12px;
  background: #3b82f6;
  border-radius: 50%;
  cursor: pointer;
  margin-top: -5px; /* perfectly center the thumb on 1px bar */
  position: relative;
  z-index: 30;
}

.seek-range::-moz-range-thumb {
  width: 12px;
  height: 12px;
  background: #3b82f6;
  border-radius: 50%;
  cursor: pointer;
  border: none;
}

/* Hide native track */
.seek-range::-webkit-slider-runnable-track {
  height: 1px;
  background: transparent;
}
.seek-range::-moz-range-track {
  height: 1px;
  background: transparent;
}
</style>