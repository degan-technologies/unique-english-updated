<template>
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-4 text-center text-lime-700">
            Test Yourself
        </h1>

        <!-- Instructions Section -->
        <div class="bg-gray-100 p-4 rounded-lg mb-6 border-l-4 border-lime-500">
            <h2 class="text-lg font-semibold text-lime-700">Instructions:</h2>
            <ul class="list-disc pl-5 text-gray-700">
                <li>
                    Read each question carefully before selecting an answer.
                </li>
                <li>Click on an option to select your answer.</li>
                <li>Use the "Next" and "Previous" buttons to navigate.</li>
                <li>
                    Once you reach the last question, click "Finish Test" to
                    submit.
                </li>
                <li>
                    Your results and level assessment will be displayed at the
                    end.
                </li>
            </ul>
            <!-- Total Questions Display -->
            <div
                v-if="quizzes.length > 0"
                class="text-center mb-4 text-gray-700"
            >
                <p><strong>Total Questions:</strong> {{ quizzes.length }}</p>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div v-if="loading" class="text-center">
            <Spinner />
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
                    class="bg-white p-6 rounded-lg shadow-lg mb-6 border border-lime-500"
                >
                    <h2 class="text-xl font-semibold mb-4">
                        Question {{ currentQuestion + 1 }} of
                        {{ quizzes.length }}:
                        {{ quizzes[currentQuestion]?.question }}
                    </h2>

                    <!-- Choices with Radio Buttons -->
                    <div
                        v-for="(choice, index) in quizzes[currentQuestion]
                            ?.choices"
                        :key="index"
                        class="mb-3 p-3 border rounded-md cursor-pointer transition-all duration-300 flex items-center gap-3"
                        :class="{
                            'border-lime-500 bg-lime-50':
                                userAnswers[currentQuestion] === choice,
                            'hover:border-lime-500 hover:bg-gray-100':
                                userAnswers[currentQuestion] !== choice,
                        }"
                        @click="selectAnswer(choice)"
                    >
                        <input
                            type="radio"
                            :name="`question_${currentQuestion}`"
                            :value="choice"
                            v-model="userAnswers[currentQuestion]"
                            class="w-5 h-5"
                        />
                        <span class="text-gray-700">{{ choice }}</span>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-6">
                        <button
                            :disabled="currentQuestion === 0"
                            @click="prevQuestion"
                            class="px-5 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <button
                            :disabled="!userAnswers[currentQuestion]"
                            @click="nextQuestion"
                            class="px-5 py-2 bg-lime-700 text-white rounded-md hover:bg-lime-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{
                                currentQuestion === quizzes.length - 1
                                    ? "Finish Test"
                                    : "Next"
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Result Section -->
            <div v-if="testCompleted" class="mt-6 flex justify-center relative">
                <div
                    class="bg-white p-6 rounded-lg shadow-lg w-full md:w-2/3 lg:w-1/2 border border-lime-500"
                >
                    <div class="text-center text-4xl font-semibold">
                        <i
                            :class="resultIcon"
                            class="mr-3 animate__animated animate__flipInX"
                        ></i>
                        <div
                            class="mt-4 text-xl font-bold"
                            :class="resultLevelClass"
                        >
                            Your Level: {{ resultLevel }}
                        </div>
                        <div class="mt-4 text-lg">
                            <p>
                                You scored
                                <span class="font-bold">{{
                                    correctAnswers
                                }}</span>
                                out of {{ quizzes.length }}.
                            </p>
                        </div>
                        <!-- Feedback Suggestion -->
                        <div class="mt-4 p-4 bg-gray-100 rounded-md">
                            <p class="text-gray-700 font-semibold">
                                Suggestion:
                            </p>
                            <p class="text-gray-600">{{ feedbackMessage }}</p>
                        </div>
                    </div>

                    <!-- Confetti Effect -->
                    <canvas
                        v-if="showConfetti"
                        ref="confettiCanvas"
                        class="absolute top-0 left-0 w-full h-full pointer-events-none"
                    ></canvas>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";
import confetti from "canvas-confetti"; // Import Confetti library
import Spinner from "../Layout/Spinner.vue"; // Import Spinner component

const quizzes = ref([]);
const currentQuestion = ref(0);
const userAnswers = ref([]); // Track user answers
const loading = ref(true);
const testCompleted = ref(false);
const resultLevel = ref("");
const resultIcon = ref("");
const resultLevelClass = ref("");
const correctAnswers = ref(0);
const feedbackMessage = ref(""); // Store feedback message
const showConfetti = ref(false);
const confettiCanvas = ref(null);

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
    const scorePercentage = correctAnswers.value / totalQuestions;

    if (scorePercentage <= 0.4) {
        setLevel(
            "A1 - Beginner",
            "fas fa-sad-tear text-red-500",
            "text-red-500",
            "You need more practice."
        );
    } else if (scorePercentage <= 0.6) {
        setLevel(
            "A2 - Elementary",
            "fas fa-meh text-yellow-500",
            "text-yellow-500",
            "You're improving!"
        );
    } else if (scorePercentage <= 0.7) {
        setLevel(
            "B1 - Intermediate",
            "fas fa-smile text-green-500",
            "text-green-500",
            "Good job!"
        );
    } else if (scorePercentage <= 0.8) {
        setLevel(
            "B2 - Upper Intermediate",
            "fas fa-laugh text-blue-500",
            "text-blue-500",
            "Great work!"
        );
    } else if (scorePercentage <= 0.9) {
        setLevel(
            "C1 - Advanced",
            "fas fa-star text-purple-500",
            "text-purple-500",
            "Excellent!"
        );
    } else {
        setLevel(
            "C2 - Proficient",
            "fas fa-award text-indigo-500",
            "text-indigo-500",
            "Outstanding!"
        );
        triggerConfetti();
    }
};

const triggerConfetti = () => {
    showConfetti.value = true;
    nextTick(() => {
        if (confettiCanvas.value) {
            const myConfetti = confetti.create(confettiCanvas.value, {
                resize: true,
                useWorker: true,
            });
            myConfetti({
                particleCount: 1000,
                spread: 80,
                origin: { y: 0.8 },
            });
        }
    });
};

onMounted(fetchQuizzes);
</script>
