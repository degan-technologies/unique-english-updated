<template>
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-center text-lime-700">
            Test Yourself
        </h1>

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
                        {{ currentQuestion + 1 }}:
                        {{ quizzes[currentQuestion]?.question }}
                    </h2>

                    <!-- Choices with Radio Buttons -->
                    <div
                        v-for="(choice, index) in quizzes[currentQuestion]
                            ?.choices"
                        :key="index"
                        class="mb-3 p-3 border rounded-md cursor-pointer transition-all duration-300 flex items-center gap-3"
                        :class="{
                            'border-lime-500 bg-lime-50 ':
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
import { ref, onMounted } from "vue";
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

// Fetch quiz questions
const fetchQuizzes = async () => {
    try {
        const response = await axios.get("/api/tests");
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

// Move to the previous question
const prevQuestion = () => {
    if (currentQuestion.value > 0) {
        currentQuestion.value--;
    }
};

// Move to the next question
const nextQuestion = () => {
    if (currentQuestion.value < quizzes.value.length - 1) {
        currentQuestion.value++;
    } else {
        submitTest(); // Automatically finish test on last question
    }
};

// Select an answer
const selectAnswer = (choice) => {
    userAnswers.value[currentQuestion.value] = choice;
};

// Submit the test
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

// Function to set level details
const setLevel = (level, icon, colorClass, message) => {
    resultLevel.value = level;
    resultIcon.value = icon;
    resultLevelClass.value = colorClass;
    feedbackMessage.value = message;
};

// Infinite confetti effect for C2
const triggerConfetti = () => {
    showConfetti.value = true;
    setInterval(() => {
        confetti.create(confettiCanvas.value, { resize: true })({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 },
        });
    }, 3000);
};

// Fetch quizzes on mount
onMounted(() => {
    fetchQuizzes();
});
</script>
