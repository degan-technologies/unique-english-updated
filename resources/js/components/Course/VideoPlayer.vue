<template>
    <div class="h-full flex flex-col sm:flex-row gap-4 px-4">
        <!-- Video Player Section -->
        <div class="flex-1 flex flex-col gap-4">
            <div
                class="relative bg-black rounded-lg overflow-hidden shadow-lg border-4 border-lime-700"
                @mousemove="resetControlsTimeout"
                @mouseleave="startControlsHideTimer"
                @mouseenter="resetControlsTimeout"
                @click="togglePlayPause"
            >
                <video
                    ref="video"
                    class="w-full h-full object-cover"
                    :src="selectedQuality"
                    @timeupdate="updateProgress"
                    @loadedmetadata="updateTotalTime"
                    @ended="handleVideoEnd"
                    @play="handlePlay"
                    @pause="handlePause"
                ></video>

                <div
                    v-show="showControls"
                    class="absolute bottom-0 left-0 right-0 p-3 flex flex-wrap justify-evenly items-center bg-lime-700 bg-opacity-1 transition-opacity duration-300 z-20 gap-4"
                    @click.stop
                >
                    <!-- Play/Pause Button -->
                    <button
                        @click="togglePlayPause"
                        class="text-white text-sm transition duration-300 flex items-center"
                        aria-label="Play/Pause"
                    >
                        <i
                            :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"
                        ></i>
                    </button>

                    <div class="flex items-center relative group">
                        <!-- Volume Icon (Always Visible) -->
                        <button
                            @click="toggleMute"
                            class="text-white text-sm transition duration-300 p-2"
                            aria-label="Mute/Unmute"
                        >
                            <i
                                :class="
                                    isMuted
                                        ? 'fas fa-volume-mute'
                                        : 'fas fa-volume-up'
                                "
                            ></i>
                        </button>

                        <!-- Volume Slider (Hidden on Mobile, Appears on Hover for PC) -->
                        <div
                            class="absolute bottom-full left-1/2 mb-2 w-16 hidden group-hover:block md:group-hover:block"
                        >
                            <!-- Volume Percentage Display -->
                            <div
                                class="absolute bottom-full left-1/2 transform -translate-x-1/2 text-xs text-black mb-1"
                            >
                                {{ Math.round(volume * 100) }}%
                            </div>

                            <input
                                type="range"
                                :value="volume"
                                @input="changeVolume"
                                min="0"
                                max="1"
                                step="0.01"
                                class="range-slider w-4 h-32 transform -translate-x-1/2 rotate-90 scale-y-[1] py-2"
                                aria-label="Volume Control"
                            />
                        </div>
                    </div>

                    <!-- Seek Bar -->
                    <div class="flex-1 items-center w-1/3">
                        <input
                            type="range"
                            :value="progress"
                            @input="handleSeekInput"
                            min="0"
                            max="100"
                            step="0.01"
                            class="range-slider w-full"
                            aria-label="Seek Video"
                        />
                    </div>

                    <!-- Time Display -->
                    <div class="text-white text-sm whitespace-nowrap">
                        <span>{{ currentTime }}</span> /
                        <span>{{ totalTime }}</span>
                    </div>

                    <!-- Quality Selector -->
                    <select
                        v-model="selectedQuality"
                        @change="updateVideoQuality"
                        class="text-white text-sm transition duration-300 bg-lime-700 border-none p-1 rounded"
                    >
                        <option value="/video/Mehari.mp4">1080p</option>
                        <option value="/video/Mehari-720p.mp4">720p</option>
                        <option value="/video/Mehari-480p.mp4">480p</option>
                        <option value="Auto">Auto</option>
                    </select>

                    <!-- Playback Rate Selector -->
                    <select
                        v-model="playbackRate"
                        @change="changePlaybackRate"
                        class="text-white text-sm transition duration-300 bg-lime-700 border-none p-1 rounded hidden sm:block"
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
                        class="text-white text-sm transition duration-300 flex items-center"
                        aria-label="Fullscreen"
                    >
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>

            <div class="flex-1 bg-white rounded-lg shadow-md w-full">
                <div
                    class="flex justify-start rounded-lg p-2 shadow-md space-x-4"
                >
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
                        <i class="fas fa-pen"></i> Note
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
                    <ReviewList />
                </div>
            </div>
        </div>

        <!-- Course List Section (now on the right side) -->
        <div
            class="w-full sm:w-1/3 bg-gray-100 rounded-lg shadow-lg p-4 flex-shrink-0"
        >
            <CourseList />
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import QA from "./QA.vue";
import TextEditor from "../Layout/TextEditor.vue";
import CourseList from "./CourseList.vue";
import ReviewList from "./ReviewList.vue";

const activeTab = ref(null); // Default to Q&A tab
const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const video = ref(null);
const isPlaying = ref(false);
const showControls = ref(true);
const progress = ref(0);
const currentTime = ref("0:00");
const totalTime = ref("0:00");
const volume = ref(1);
const isMuted = ref(false);
const playbackRate = ref("default"); // Default placeholder
const selectedQuality = ref("/video/Mehari.mp4");

let hideControlsTimeout = null;

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
    video.value.currentTime = seekTime;
    progress.value = (seekTime / video.value.duration) * 100;
    currentTime.value = formatTime(seekTime);
};

const updateProgress = () => {
    if (!video.value) return;
    progress.value = (video.value.currentTime / video.value.duration) * 100;
    currentTime.value = formatTime(video.value.currentTime);
};

const updateTotalTime = () => {
    if (!video.value) return;
    totalTime.value = formatTime(video.value.duration || 0);
};

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
    volume.value = event.target.value;
    video.value.volume = volume.value;
};

const toggleMute = () => {
    if (!video.value) return;
    video.value.muted = !video.value.muted;
    isMuted.value = video.value.muted;
};

const changePlaybackRate = () => {
    if (!video.value || playbackRate.value === "default") return;
    video.value.playbackRate = playbackRate.value;
};

const toggleFullscreen = () => {
    if (!video.value) return;
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        video.value.requestFullscreen();
    }
};

const updateVideoQuality = () => {
    if (!video.value) return;
    video.value.src = selectedQuality.value;
    video.value.load();
    video.value.play();
};

onMounted(() => {
    video.value.volume = volume.value;
    video.value.playbackRate = parseFloat(playbackRate.value) || 1.0;
    if (video.value) {
        video.value.addEventListener("timeupdate", updateProgress);
    }
    updateTotalTime();
});
</script>

<style scoped>
.range-slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 4px;
    background: #ffffff;
    border-radius: 4px;
    outline: none;
}

.range-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 10px;
    height: 10px;
    background: #f4f3f3;
    border-radius: 50%;
    cursor: pointer;
}

.range-slider::-moz-range-thumb {
    width: 10px;
    height: 10px;
    background: #f4f3f3;
    border-radius: 50%;
    cursor: pointer;
}

.tab-button {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    text-align: center;
    transition: background-color 0.3s, transform 0.2s;
    border-radius: 0.375rem;
}

.tab-button:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: scale(1.05);
}

.tab-button:focus {
    outline: none;
}

.tab-button.bg-lime-800 {
    background-color: #4caf50;
}

.tab-button.text-lime-300 {
    color: #b5e48c;
}

.tab-button.active {
    background-color: #4caf50;
    color: white;
}

button {
    background-color: transparent;
    border: none;
    cursor: pointer;
}

button:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

button:focus {
    outline: none;
}

.flex-1 {
    display: flex;
    flex-direction: column;
}

.sm\:flex-row {
    display: flex;
    flex-direction: row;
}

.gap-4 {
    gap: 1rem;
}

.px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
}

.h-full {
    height: 100%;
}

.sm\:w-1\/3 {
    width: 33.333333%;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

/* Responsive Layout: Switch Video Player and Course List */
@media (max-width: 640px) {
    .sm\:flex-row {
        flex-direction: column-reverse;
    }

    .sm\:w-1\/3 {
        width: 100%;
    }
}
</style>
