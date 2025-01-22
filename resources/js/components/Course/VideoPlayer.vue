<template>
    <div
        class="flex flex-col items-center justify-center max-w-full w-full relative rounded-lg overflow-hidden bg-transparent"
        style="
            background-image: url('/images/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        "
    >
        <video
            ref="video"
            class="w-4/5 h-[80vh] bg-transparent object-contain border-4 border-[#1db954] shadow-lg rounded-lg"
            :src="selectedQuality"
            @timeupdate="updateProgress"
            @loadedmetadata="updateTotalTime"
            @ended="handleVideoEnd"
        ></video>

        <!-- Controls -->
        <div
            class="flex items-center justify-between w-4/5 bg-black bg-opacity-80 p-2 absolute bottom-0"
        >
            <!-- Play/Pause Button -->
            <button
                @click="togglePlayPause"
                class="bg-transparent border-none text-white text-xl cursor-pointer mx-1"
            >
                <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
            </button>

            <!-- Seek Bar -->
            <input
                type="range"
                class="flex-1 mx-1 cursor-pointer bg-gray-600 rounded h-1"
                :value="progress"
                @input="handleSeekInput"
                min="0"
                max="100"
                step="0.01"
            />

            <!-- Time Display -->
            <span class="text-white text-xs"
                >{{ currentTime }} / {{ totalTime }}</span
            >

            <!-- Volume Control -->
            <input
                type="range"
                class="w-20 mx-1 cursor-pointer"
                :value="volume"
                @input="changeVolume"
                min="0"
                max="1"
                step="0.1"
            />

            <!-- Quality Selector -->
            <select
                @change="changeQuality"
                class="bg-gray-800 text-white border-none px-2 py-1 rounded cursor-pointer"
            >
                <option
                    v-for="quality in qualities"
                    :key="quality.label"
                    :value="quality.src"
                >
                    {{ quality.label }}
                </option>
            </select>

            <!-- Fullscreen Button -->
            <button
                @click="toggleFullscreen"
                class="bg-transparent border-none text-white text-xl cursor-pointer mx-1"
            >
                <i class="fas fa-expand"></i>
            </button>
        </div>
    </div>
    <div
        class="mt-10 w-full lg:w-4/5 mx-auto bg-white bg-opacity-95 p-6 rounded-lg shadow-lg"
    >
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">
            Q&A Section
        </h2>

        <!-- Ask a Question -->
        <div class="mb-8">
            <input
                v-model="newQuestion"
                type="text"
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 mb-4 transition duration-300"
                placeholder="Ask a question..."
            />
            <button
                @click="addQuestion"
                class="bg-green-500 text-white px-6 py-3 rounded-lg shadow hover:bg-green-600 transition duration-300 w-full lg:w-auto"
            >
                Submit Question
            </button>
        </div>

        <!-- List Questions and Answers -->
        <div
            v-for="(qa, index) in questions"
            :key="index"
            class="mb-6 border-b border-gray-200 pb-4"
        >
            <!-- Display Question -->
            <p class="text-lg font-semibold text-gray-800">
                {{ qa.question }}
            </p>

            <!-- List of Answers -->
            <div class="ml-4 mt-3">
                <p
                    v-for="(answer, aIndex) in qa.answers"
                    :key="aIndex"
                    class="text-gray-700 bg-gray-100 p-2 rounded-md mb-2"
                >
                    - {{ answer }}
                </p>
            </div>

            <!-- Reply Section -->
            <div class="mt-3">
                <button
                    @click="toggleReplyField(index)"
                    class="text-blue-500 underline hover:text-blue-700 transition duration-300"
                >
                    Reply
                </button>

                <!-- Reply Input -->
                <div v-if="qa.showReplyField" class="mt-3">
                    <input
                        v-model="qa.newAnswer"
                        type="text"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 mb-3 transition duration-300"
                        placeholder="Write your answer..."
                    />
                    <button
                        @click="addAnswer(index)"
                        class="bg-blue-500 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-600 transition duration-300 w-full lg:w-auto"
                    >
                        Submit Answer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

// Video player state
const video = ref(null);
const isPlaying = ref(false);
const progress = ref(0);
const currentTime = ref("0:00");
const totalTime = ref("0:00");
const volume = ref(1);
const isSeeking = ref(false); // Prevent conflicts during seeking

// Quality selection (with local video URL)
const qualities = ref([{ label: "720p", src: "/video/Mehari.mp4" }]);
const selectedQuality = ref(qualities.value[0].src);

// Toggle play/pause
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

// Handle seek bar input
const handleSeekInput = (event) => {
    if (!video.value) return;

    // Calculate seek time
    const seekTime = (event.target.value / 100) * video.value.duration;

    // Update the video's current time
    video.value.currentTime = seekTime;

    // Update progress and current time immediately
    progress.value = (seekTime / video.value.duration) * 100;
    currentTime.value = formatTime(seekTime);

    isSeeking.value = true; // Temporarily disable `@timeupdate` during seek
};

// Sync progress bar and playback
const updateProgress = () => {
    if (!video.value || isSeeking.value) return;

    // Update progress percentage and time
    progress.value = (video.value.currentTime / video.value.duration) * 100;
    currentTime.value = formatTime(video.value.currentTime);
};

// Reset seeking state
const resetSeekingState = () => {
    isSeeking.value = false;
};

// Change volume
const changeVolume = (event) => {
    if (!video.value) return;
    volume.value = event.target.value;
    video.value.volume = volume.value;
};

// Format time for display
const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs < 10 ? "0" : ""}${secs}`;
};

// Update total video time when metadata is loaded
const updateTotalTime = () => {
    if (!video.value) return;
    totalTime.value = formatTime(video.value.duration);
};

// Change quality
const changeQuality = (event) => {
    selectedQuality.value = event.target.value;
    const isPlayingNow = isPlaying.value;

    // Restart video after quality change
    video.value.pause();
    video.value.load();
    if (isPlayingNow) video.value.play();
};

// Handle video end
const handleVideoEnd = () => {
    isPlaying.value = false;
    progress.value = 0;
    currentTime.value = "0:00";
};

// Toggle fullscreen
const toggleFullscreen = () => {
    if (!video.value) return;
    if (document.fullscreenElement) {
        document.exitFullscreen();
    } else {
        video.value.requestFullscreen();
    }
};

// Q&A Section state
const questions = ref([]);
const newQuestion = ref("");

// Add a new question
const addQuestion = () => {
    if (newQuestion.value.trim() === "") return;
    questions.value.push({
        question: newQuestion.value.trim(),
        answers: [],
        newAnswer: "",
        showReplyField: false,
    });
    newQuestion.value = "";
};

// Toggle reply field visibility
const toggleReplyField = (index) => {
    questions.value[index].showReplyField =
        !questions.value[index].showReplyField;
};

// Add an answer to a question
const addAnswer = (index) => {
    const qa = questions.value[index];
    if (qa.newAnswer.trim() === "") return;
    qa.answers.push(qa.newAnswer.trim());
    qa.newAnswer = "";
    qa.showReplyField = false;
};
</script>

<style>
/* Add your styles here if needed */
</style>
