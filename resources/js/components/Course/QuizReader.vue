<template>
    <div class="container mx-auto p-6">
        <Spinner v-if="loading" />

        <div v-else>
            <!-- Instruction Section -->
            <div class="bg-gray-100 p-6 rounded-lg shadow-md mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">
                    Instructions
                </h3>
                <p class="text-lg text-gray-700">
                    Answer the following questions and click "Finish Quiz" at
                    the end. You can move to the next question by clicking
                    "Next" and go back to previous questions with "Previous".
                </p>
            </div>

            <!-- Quiz Section -->
            <div v-if="!showResult">
                <div
                    v-if="quiz"
                    class="bg-white p-6 rounded-lg shadow-lg border border-lime-500"
                >
                    <h2 class="text-xl font-semibold text-gray-800">
                        {{ currentQuestion }}. {{ quiz.question }}
                    </h2>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="(choice, index) in quiz.choice"
                            :key="index"
                        >
                            <label
                                :class="[
                                    'flex items-center gap-3 p-3 border rounded-md cursor-pointer transition-all',
                                    {
                                        'border-lime-500 bg-lime-50':
                                            answers[currentQuestion - 1] ===
                                            choice,
                                        'hover:border-lime-500 hover:bg-gray-100':
                                            answers[currentQuestion - 1] !==
                                            choice,
                                    },
                                ]"
                            >
                                <input
                                    type="radio"
                                    v-model="answers[currentQuestion - 1]"
                                    :value="choice"
                                    class="form-radio h-4 w-4 text-lime-500"
                                />
                                <span class="text-lg text-gray-700">{{
                                    choice
                                }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <button
                            @click="goToPrevious"
                            :disabled="currentQuestion === 1"
                            class="px-5 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>

                        <button
                            v-if="currentQuestion < totalQuestions"
                            @click="goToNext"
                            :disabled="!answers[currentQuestion - 1]"
                            class="px-5 py-2 bg-lime-700 text-white rounded-md hover:bg-lime-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>

                        <button
                            v-if="currentQuestion === totalQuestions"
                            @click="submitQuiz"
                            :disabled="!answers[currentQuestion - 1]"
                            class="px-5 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all transform hover:scale-105"
                        >
                            Finish Quiz
                        </button>
                    </div>
                </div>
            </div>

            <!-- Result Section -->
            <div
                v-if="showResult"
                class="mt-8 bg-white p-8 rounded-lg shadow-lg text-center relative overflow-hidden"
            >
                <canvas
                    ref="confettiCanvas"
                    class="absolute inset-0 pointer-events-none"
                ></canvas>

                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    <i
                        v-if="scorePercentage >= 70"
                        class="fas fa-trophy text-yellow-400"
                    ></i>
                    Quiz Completed <i class="fas fa-trophy"></i> 🎉
                </h2>
                <p class="mt-4 text-lg text-gray-700">
                    You answered
                    <span class="font-bold">{{ correctAnswers }}</span> out of
                    <span class="font-bold">{{ totalQuestions }}</span>
                    correctly.
                </p>
                <p class="mt-2 text-lg text-red-500 font-semibold">
                    Missed Questions: {{ totalQuestions - correctAnswers }}
                </p>
                <p
                    class="mt-4 text-xl font-bold"
                    :class="{
                        'text-lime-700 animate-bounce': scorePercentage >= 70,
                        'text-red-600 animate-shake': scorePercentage < 70,
                    }"
                >
                    Status:
                    {{ scorePercentage >= 70 ? "Passed ✅" : "Failed ❌" }}
                </p>

                <div
                    v-if="scorePercentage >= 70"
                    class="mt-6 text-xl font-semibold text-lime-700"
                >
                    <p>Congratulations! 🎉 You passed the quiz! 🏆</p>
                </div>

                <button
                    v-if="scorePercentage < 70"
                    @click="retakeQuiz"
                    class="mt-6 px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all transform hover:scale-105"
                >
                    Retake Quiz <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import axios from "axios";
import confetti from "canvas-confetti";
import Spinner from "../Layout/Spinner.vue";

const quizData = ref([]);
const currentQuestion = ref(1);
const answers = ref([]);
const correctAnswers = ref(0);
const showResult = ref(false);
const loading = ref(true);
const confettiCanvas = ref(null);

const totalQuestions = computed(() => quizData.value.length);
const quiz = computed(() => quizData.value[currentQuestion.value - 1]);
const scorePercentage = computed(
    () => (correctAnswers.value / totalQuestions.value) * 100
);

const fetchQuiz = async () => {
    try {
        const response = await axios.get("/api/Quize");
        quizData.value = response.data.data.map((item) => ({
            ...item,
            choice: Array.isArray(item.choice)
                ? item.choice
                : JSON.parse(item.choice),
            answer: Array.isArray(item.answer)
                ? item.answer
                : JSON.parse(item.answer),
        }));
        answers.value = new Array(quizData.value.length).fill(null);
    } catch (error) {
        console.error("Error fetching quiz data:", error);
    } finally {
        loading.value = false;
    }
};

const goToNext = () => {
    if (answers.value[currentQuestion.value - 1] === quiz.value.answer[0]) {
        correctAnswers.value += 1;
    }
    if (currentQuestion.value < totalQuestions.value) {
        currentQuestion.value += 1;
    }
};

const goToPrevious = () => {
    if (currentQuestion.value > 1) {
        currentQuestion.value -= 1;
    }
};

const submitQuiz = async () => {
    if (answers.value[currentQuestion.value - 1] === quiz.value.answer[0]) {
        correctAnswers.value += 1;
    }
    showResult.value = true;

    await nextTick();
    if (scorePercentage.value >= 70) {
        launchConfetti();
    }
};

const retakeQuiz = () => {
    currentQuestion.value = 1;
    correctAnswers.value = 0;
    showResult.value = false;
    answers.value = new Array(quizData.value.length).fill(null);
};

// 🎉 Confetti Effect
const launchConfetti = () => {
    const myCanvas = confettiCanvas.value;
    if (!myCanvas) return;

    const confettiInstance = confetti.create(myCanvas, { resize: true });
    setInterval(() => {
        confettiInstance({
            particleCount: 200,
            spread: 70,
            origin: { y: 0.6 },
        });
    }, 2000);
};

onMounted(fetchQuiz);
</script>

<style scoped>
canvas {
    width: 100%;
    height: 100%;
}
</style>
