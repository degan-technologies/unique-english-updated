<template>
    <div class="container mx-auto p-6 max-w-4xl mt-10">
        <h1
            class="text-4xl font-bold mb-6 text-center bg-clip-text text-transparent bg-gradient-to-r from-lime-600 to-green-800"
        >
            Test Yourself
        </h1>

        <!-- Instructions Section -->
        <div
            class="bg-gradient-to-br from-lime-50 to-green-50 p-6 rounded-xl shadow-md mb-8 border-l-8 border-lime-500"
        >
            <h2 class="text-2xl font-semibold text-lime-800 mb-3">
                Instructions:
            </h2>
            <ul class="space-y-2 text-gray-700">
                <li class="flex items-start gap-2">
                    <span class="text-lime-600 mt-1">•</span>
                    <span
                        >Read each question carefully before selecting an
                        answer.</span
                    >
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lime-600 mt-1">•</span>
                    <span>Click on an option to select your answer.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lime-600 mt-1">•</span>
                    <span
                        >Use the "Next" and "Previous" buttons to
                        navigate.</span
                    >
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lime-600 mt-1">•</span>
                    <span
                        >Once you reach the last question, click "Finish Test"
                        to submit.</span
                    >
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lime-600 mt-1">•</span>
                    <span
                        >Your results and level assessment will be displayed at
                        the end.</span
                    >
                </li>
            </ul>
            <!-- Total Questions Display -->
            <div
                v-if="quizzes.length > 0"
                class="mt-4 flex items-center justify-center gap-2"
            >
                <span class="text-lg font-medium text-gray-700"
                    >Total Questions:</span
                >
                <span
                    class="px-3 py-1 bg-lime-100 text-lime-800 rounded-full text-lg font-bold"
                >
                    {{ quizzes.length }}
                </span>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div v-if="loading" class="text-center py-12">
            <Spinner class="w-16 h-16 mx-auto text-lime-600" />
            <p class="mt-4 text-gray-600">Loading questions...</p>
        </div>

        <div v-else>
            <!-- Question Display -->
            <div
                v-if="
                    currentQuestion !== null &&
                    quizzes.length > 0 &&
                    !testCompleted
                "
            >
                <div
                    class="bg-white p-8 rounded-2xl shadow-lg mb-6 border-2 border-lime-400"
                >
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <span
                                class="px-3 py-1 bg-lime-100 text-lime-800 rounded-full font-bold"
                            >
                                Question {{ currentQuestion + 1 }} of
                                {{ quizzes.length }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div
                                class="bg-lime-600 h-2.5 rounded-full"
                                :style="`width: ${
                                    ((currentQuestion + 1) / quizzes.length) *
                                    100
                                }%`"
                            ></div>
                        </div>
                    </div>

                    <h2 class="text-xl font-semibold mb-6 text-gray-800">
                        {{ quizzes[currentQuestion]?.question }}
                    </h2>

                    <!-- Choices with Radio Buttons -->
                    <div class="space-y-3">
                        <div
                            v-for="(choice, index) in quizzes[currentQuestion]
                                ?.choices"
                            :key="index"
                            class="p-4 border-2 rounded-xl cursor-pointer transition-all duration-300 flex items-center gap-4"
                            :class="{
                                'border-lime-500 bg-lime-50 shadow-md scale-[1.01]':
                                    userAnswers[currentQuestion] === choice,
                                'border-gray-200 hover:border-lime-300 hover:bg-gray-50':
                                    userAnswers[currentQuestion] !== choice,
                            }"
                            @click="selectAnswer(choice)"
                        >
                            <div class="flex items-center">
                                <div
                                    class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                    :class="{
                                        'border-lime-600 bg-lime-100':
                                            userAnswers[currentQuestion] ===
                                            choice,
                                        'border-gray-300':
                                            userAnswers[currentQuestion] !==
                                            choice,
                                    }"
                                >
                                    <div
                                        v-if="
                                            userAnswers[currentQuestion] ===
                                            choice
                                        "
                                        class="w-3 h-3 rounded-full bg-lime-600"
                                    ></div>
                                </div>
                            </div>
                            <span class="text-gray-700 text-lg">{{
                                choice
                            }}</span>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <button
                            :disabled="currentQuestion === 0"
                            @click="prevQuestion"
                            class="px-6 py-3 bg-gray-300 text-gray-700 rounded-xl hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                            Previous
                        </button>
                        <button
                            :disabled="!userAnswers[currentQuestion]"
                            @click="nextQuestion"
                            class="px-6 py-3 bg-lime-600 text-white rounded-xl hover:bg-lime-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                        >
                            {{
                                currentQuestion === quizzes.length - 1
                                    ? "Finish Test"
                                    : "Next"
                            }}
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Result Section -->
            <div v-if="testCompleted" class="relative">
                <div
                    class="bg-gradient-to-br from-white to-gray-50 p-8 rounded-3xl shadow-xl border-2 border-lime-300 backdrop-blur-sm"
                >
                    <!-- Confetti Effect -->
                    <canvas
                        v-if="showConfetti"
                        ref="confettiCanvas"
                        class="absolute top-0 left-0 w-full h-full pointer-events-none z-0"
                    ></canvas>

                    <div class="relative z-10">
                        <!-- Score Circle -->
                        <div class="flex justify-center mb-8">
                            <div class="relative w-48 h-48">
                                <svg
                                    class="w-full h-full"
                                    viewBox="0 0 100 100"
                                >
                                    <!-- Background circle -->
                                    <circle
                                        cx="50"
                                        cy="50"
                                        :r="45"
                                        fill="none"
                                        stroke="#e5e7eb"
                                        stroke-width="8"
                                    />
                                    <!-- Progress circle -->
                                    <circle
                                        cx="50"
                                        cy="50"
                                        :r="45"
                                        fill="none"
                                        :stroke="
                                            scorePercentage >= 70
                                                ? '#16a34a'
                                                : '#f59e0b'
                                        "
                                        stroke-width="8"
                                        stroke-linecap="round"
                                        :stroke-dasharray="`${
                                            (correctAnswers / quizzes.length) *
                                            283
                                        } 283`"
                                        transform="rotate(-90 50 50)"
                                    />
                                </svg>
                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center"
                                >
                                    <span
                                        class="text-4xl font-bold"
                                        :class="
                                            scorePercentage >= 70
                                                ? 'text-lime-600'
                                                : 'text-amber-500'
                                        "
                                    >
                                        {{ Math.round(scorePercentage * 100) }}%
                                    </span>
                                    <span class="text-gray-600">Score</span>
                                </div>
                            </div>
                        </div>

                        <!-- Result Content -->
                        <div class="text-center">
                            <div class="mb-6">
                                <div
                                    :class="`mx-auto w-20 h-20 rounded-full flex items-center justify-center text-4xl mb-4 ${resultLevelClass} ${
                                        scorePercentage >= 70
                                            ? 'bg-lime-100'
                                            : 'bg-amber-100'
                                    }`"
                                >
                                    <i :class="resultIcon"></i>
                                </div>
                                <h2
                                    class="text-3xl font-bold mb-2"
                                    :class="resultLevelClass"
                                >
                                    {{ resultLevel }}
                                </h2>
                                <p class="text-gray-600">
                                    You answered
                                    <span class="font-bold">{{
                                        correctAnswers
                                    }}</span>
                                    out of
                                    <span class="font-bold">{{
                                        quizzes.length
                                    }}</span>
                                    correctly.
                                </p>
                            </div>

                            <!-- Feedback Suggestion -->
                            <div
                                class="mt-6 p-5 bg-white rounded-xl shadow-inner border border-gray-200"
                            >
                                <p
                                    class="text-lg font-semibold text-gray-700 mb-2"
                                >
                                    Suggestion:
                                </p>
                                <p class="text-gray-600">
                                    {{ feedbackMessage }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div
                                class="mt-8 flex flex-col sm:flex-row justify-center gap-4"
                            >
                                <button
                                    @click="retakeTest"
                                    class="px-6 py-3 bg-gradient-to-r from-lime-500 to-green-600 text-white rounded-xl hover:shadow-lg transition-all transform focus:outline-none focus:ring-2 focus:ring-lime-400 focus:ring-opacity-50"
                                >
                                    <i class="fas fa-redo mr-2"></i> Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from "vue";
import axios from "axios";
import confetti from "canvas-confetti";
import Spinner from "../Layout/Spinner.vue";

const quizzes = ref([]);
const currentQuestion = ref(0);
const userAnswers = ref([]);
const loading = ref(true);
const testCompleted = ref(false);
const resultLevel = ref("");
const resultIcon = ref("");
const resultLevelClass = ref("");
const correctAnswers = ref(0);
const feedbackMessage = ref("");
const showConfetti = ref(false);
const confettiCanvas = ref(null);

const scorePercentage = computed(() => {
    return correctAnswers.value / quizzes.value.length;
});

// Define setLevel to update result properties
const setLevel = (level, icon, levelClass, feedback) => {
    resultLevel.value = level;
    resultIcon.value = icon;
    resultLevelClass.value = levelClass;
    feedbackMessage.value = feedback;
};

const fetchQuizzes = async () => {
    try {
        const response = await axios.get("/api/test");
        quizzes.value = response.data.data.map((quiz) => ({
            ...quiz,
            choices: Array.isArray(quiz.choices)
                ? quiz.choices
                : quiz.choices?.split(",").map((choice) => choice.trim()),
            answer: String(quiz.answer).trim(),
        }));
        userAnswers.value = new Array(quizzes.value.length).fill(null);
    } catch (error) {
        console.error("Error fetching quizzes:", error);
    } finally {
        loading.value = false;
    }
};

const prevQuestion = () => {
    if (currentQuestion.value > 0) {
        currentQuestion.value--;
    }
};

const nextQuestion = () => {
    if (currentQuestion.value < quizzes.value.length - 1) {
        currentQuestion.value++;
    } else {
        submitTest();
    }
};

const selectAnswer = (choice) => {
    userAnswers.value[currentQuestion.value] = choice;
};

const submitTest = async () => {
    testCompleted.value = true;

    // Calculate correct answers
    correctAnswers.value = quizzes.value.filter((quiz, index) => {
        return quiz.answer === userAnswers.value[index];
    }).length;

    // Determine result level dynamically
    const totalQuestions = quizzes.value.length;
    const percentage = correctAnswers.value / totalQuestions;

    if (percentage <= 0.4) {
        setLevel(
            "A1 - Beginner",
            "fas fa-sad-tear",
            "text-red-500",
            "You're just starting out. Keep practicing with basic vocabulary and simple sentences to build your foundation."
        );
    } else if (percentage <= 0.6) {
        setLevel(
            "A2 - Elementary",
            "fas fa-meh",
            "text-yellow-500",
            "You're making progress! Focus on expanding your vocabulary and practicing common phrases."
        );
    } else if (percentage <= 0.7) {
        setLevel(
            "B1 - Intermediate",
            "fas fa-smile",
            "text-green-500",
            "Good job! You're comfortable with everyday conversations. Try reading more complex texts now."
        );
    } else if (percentage <= 0.8) {
        setLevel(
            "B2 - Upper Intermediate",
            "fas fa-laugh",
            "text-blue-500",
            "Great work! You can handle most situations. Challenge yourself with native materials."
        );
    } else if (percentage <= 0.9) {
        setLevel(
            "C1 - Advanced",
            "fas fa-star",
            "text-purple-500",
            "Excellent! You're approaching fluency. Focus on nuance and specialized vocabulary."
        );
    } else {
        setLevel(
            "C2 - Proficient",
            "fas fa-award",
            "text-indigo-500",
            "Outstanding! You've mastered the language. Maintain your skills by engaging with complex materials."
        );
        triggerConfetti();
    }
};

const retakeTest = () => {
    currentQuestion.value = 0;
    testCompleted.value = false;
    userAnswers.value = new Array(quizzes.value.length).fill(null);
    showConfetti.value = false;
};

const triggerConfetti = () => {
    showConfetti.value = true;
    nextTick(() => {
        if (confettiCanvas.value) {
            const count = 3;
            const defaults = {
                origin: { y: 0.8 },
                spread: 80,
                startVelocity: 45,
            };

            function fire(particleRatio, opts) {
                confetti({
                    ...defaults,
                    ...opts,
                    particleCount: Math.floor(200 * particleRatio),
                    colors: [
                        "#16a34a",
                        "#4d7c0f",
                        "#84cc16",
                        "#a3e635",
                        "#bef264",
                    ],
                    shapes: ["circle", "square", "star"],
                    scalar: 1.2,
                });
            }

            // Triple burst
            fire(0.25, { spread: 26, startVelocity: 55 });
            fire(0.2, { spread: 60 });
            fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8 });
            fire(0.1, { spread: 120, startVelocity: 25, decay: 0.92 });
            fire(0.1, { spread: 120, startVelocity: 45 });
        }
    });
};

onMounted(fetchQuizzes);
</script>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.result-enter-active {
    animation: fadeIn 0.8s ease-out;
}

.progress-bar {
    transition: width 0.5s ease;
}

.circle-progress {
    transition: stroke-dasharray 1s ease;
}
</style>
