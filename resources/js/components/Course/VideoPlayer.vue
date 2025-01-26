<template>
    <div class="max-w-full mx-auto my-8 p-4 h-full">
        <!-- Video Player Container -->
        <div
            class="relative w-full max-w-4xl mx-auto bg-black rounded-lg overflow-hidden shadow-lg border-4 border-lime-700"
            @mousemove="resetControlsTimeout"
            @mouseleave="startControlsHideTimer"
            @mouseenter="resetControlsTimeout"
            @click="togglePlayPause"
        >
            <!-- Video element -->
            <video
                ref="video"
                class="w-full h-[80vh] sm:h-[70vh] object-cover"
                :src="selectedQuality"
                @timeupdate="updateProgress"
                @loadedmetadata="updateTotalTime"
                @ended="handleVideoEnd"
                @play="handlePlay"
                @pause="handlePause"
            ></video>

            <!-- Controls Section -->
            <div
                v-show="showControls"
                class="absolute bottom-0 left-0 right-0 p-3 flex flex-wrap items-center justify-between bg-lime-700 bg-opacity-1 transition-opacity duration-300 z-20 gap-4"
                @click.stop
            >
                <!-- Play/Pause -->
                <button
                    @click="togglePlayPause"
                    class="text-white text-sm transition duration-300 flex items-center"
                    aria-label="Play/Pause"
                >
                    <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
                </button>

                <!-- Volume -->
                <div class="flex items-center relative group">
                    <button
                        @click="toggleMute"
                        class="text-white text-sm transition duration-300"
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
                    <!-- Volume Slider -->
                    <div class="ml-2 hidden sm:block">
                        <input
                            type="range"
                            :value="volume"
                            @input="changeVolume"
                            min="0"
                            max="1"
                            step="0.1"
                            class="range-slider w-3/4"
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
                        class="range-slider w-3/4"
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

                <!-- Playback Rate -->
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

                <!-- Fullscreen -->
                <button
                    @click="toggleFullscreen"
                    class="text-white text-sm transition duration-300 flex items-center"
                    aria-label="Fullscreen"
                >
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Q&A Section -->
    <QA />
</template>

<script setup>
import QA from "./QA.vue";
import { ref, onMounted } from "vue";

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
};

const handlePause = () => {
    isPlaying.value = false;
    showControls.value = true;
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
</style>
