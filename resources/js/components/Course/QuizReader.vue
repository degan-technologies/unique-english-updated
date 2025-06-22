<script setup>
import Axios from "axios";
import confetti from "canvas-confetti";
import Spinner from "@/components/Layout/Spinner.vue";
import { toast } from "vue3-toastify";
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from "vue";
import "vue3-toastify/dist/index.css";

const props = defineProps({
    quizData: {
        type: Object,
        required: true,
    },
});

const examData = ref(null);
const currentQuestion = ref(1);
const answers = ref([]);
const correctAnswers = ref(0);
const showResult = ref(false);
const loading = ref(true);
const confettiCanvas = ref(null);
const questions = ref([]);
const chekIncorects = ref(false);
const scorePercentage = ref(0);

const showHintForQuestion = ref(false);

const totalQuestions = computed(() => questions.value.length);
const quiz = computed(() => questions.value[currentQuestion.value - 1] || null);

const parseChoice = (choiceData) => {
    if (Array.isArray(choiceData)) {
        return choiceData;
    }
    try {
        return JSON.parse(choiceData);
    } catch (error) {
        console.warn("Invalid choice format:", choiceData);
        return [];
    }
};

watch(
    () => props.quizData,
    (newQuizData) => {
        if (newQuizData && newQuizData.questions) {
            questions.value = newQuizData.questions.map((q) => ({
                ...q,
                choice: parseChoice(q.choice),
            }));
            examData.value = newQuizData;
            loading.value = false;
        } else {
            console.error("No questions found inside quizData.");
            loading.value = false;
        }
    },
    { immediate: true }
);

const goToNext = () => {
    showHintForQuestion.value = false;
    if (currentQuestion.value < totalQuestions.value) {
        currentQuestion.value += 1;
    }
};

const goToPrevious = () => {
    if (currentQuestion.value > 1) {
        currentQuestion.value -= 1;
    }
    showHintForQuestion.value = false;
};

const submitQuiz = async () => {
    showResult.value = true;
    await nextTick();
    storeResult();
};

const retakeQuiz = () => {
    currentQuestion.value = 1;
    showResult.value = false;
    answers.value = new Array(quizData.value.length).fill(null);
    showHintForQuestion.value = false;
};

function storeResult() {
    const payload = {
        exam_id: examData.value?.id,
    };

    Axios.post("/api/results", payload).then((res) => {
        scorePercentage.value = res.data.mark;
        correctAnswers.value = res.data.correctAnswers;
        totalQuestions.value = res.data.totalQuation;

        if (scorePercentage.value >= 70) {
            launchConfetti();
        }
    });
}

const launchConfetti = () => {
    const count = 3;  
    const duration = 3000;  
    const interval = duration / count;

    let bursts = 0;
    const burstInterval = setInterval(() => {
        confetti({
            particleCount: 150,
            spread: 90,
            origin: { y: 0.6 },
            colors: ["#00e676", "#00b0ff", "#ff4081", "#ff9100", "#ffeb3b"],
            shapes: ["circle", "square", "star"],
            scalar: 1.2,
        });

        bursts++;
        if (bursts >= count) {
            clearInterval(burstInterval);
        }
    }, interval);
};

const toggleHint = () => {
    if (quiz.value && quiz.value.hint && quiz.value.hint.trim() !== "") {
        showHintForQuestion.value = !showHintForQuestion.value;
    } else {
        toast.info("No hint available for this question.");
    }
};

function submitAnswer(quizId, choice) {
    const payload = {
        quiz_id: quizId,
        choice: choice,
    };

    Axios.post("/api/answer/quiz", payload).then((res) => { });
}

function checkAnswer() {
    chekIncorects.value = true;
    currentQuestion.value = 1;
    correctAnswers.value = 0;
    showResult.value = false;
    showHintForQuestion.value = false;

    Axios.get(`/api/check/answer/${props.quizData.id}`).then((res) => {
        questions.value = res.data.data.map((q) => ({
            ...q,
            choice: parseChoice(q.choice),
        }));
    });
}
</script>

<template>
    <div class="container mx-auto sm:p-6">
        <Spinner v-if="loading" />
        <div v-else>
            <!-- Exam Information -->
            <div v-if="examData"
                class="bg-gradient-to-r from-lime-300 to-lime-500 p-2 sm:p-6 rounded-lg shadow-md mb-8 text-gray-800">
                <h2 class="text-3xl font-bold mb-2">{{ examData.title }}</h2>
                <p class="mb-4 text-lg">
                    <strong>Instruction: </strong>
                    <span>{{ examData.instruction }}</span>
                </p>
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold">Total Questions:</span>
                    <span class="px-3 py-1 bg-white text-lime-600 rounded-full text-lg font-semibold">
                        {{ totalQuestions }}
                    </span>
                </div>
            </div>

            <!-- Quiz Question Section -->
            <div v-if="!showResult && questions.length > 0">
                <div v-if="quiz" class="bg-white p-6 rounded-lg shadow-lg border border-lime-500">
                    <!-- Question header with hint button -->
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-800">
                            {{ currentQuestion }}: {{ quiz.question }}
                        </h2>
                        <button @click="toggleHint" class="p-2 focus:outline-none" title="Show Hint">
                            <span class="text-2xl"><i class="fa-solid fa-lightbulb text-blue-500 text-2xl"></i></span>
                        </button>
                    </div>

                    <!-- Display hint if toggled and available -->
                    <div v-if="showHintForQuestion && quiz.hint"
                        class="mt-2 p-3 border-l-4 border-yellow-500 bg-yellow-50 text-yellow-700">
                        Hint: {{ quiz.hint }}
                    </div>

                    <!-- Answer Choices -->
                    <div class="mt-4 space-y-3">
                        <div v-for="(choice, index) in quiz.choice" :key="index">
                            <label :class="[
                                'flex items-center gap-3 p-3 border rounded-md cursor-pointer transition-all',
                                {
                                    'border-lime-500 bg-lime-50':
                                        answers[currentQuestion - 1] ===
                                        choice,
                                    'hover:border-lime-500 hover:bg-gray-100':
                                        answers[currentQuestion - 1] !==
                                        choice,
                                },
                            ]">
                                <input type="radio" v-model="answers[currentQuestion - 1]" :value="choice"
                                    @input="submitAnswer(quiz.id, choice)" class="form-radio h-4 w-4 text-lime-500" />
                                <span class="text-lg text-gray-700">{{
                                    choice
                                    }}</span>
                            </label>
                        </div>
                        <div v-if="chekIncorects && !quiz.checkAnswer"
                            class="w-full bg-rose-300 text-gray-500 rounded-md">
                            <p class="p-4">Incorrect Answer</p>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-6 flex justify-between">
                        <button @click="goToPrevious" :disabled="currentQuestion === 1"
                            class="px-5 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            Previous
                        </button>
                        <button v-if="currentQuestion < totalQuestions" @click="goToNext"
                            :disabled="!answers[currentQuestion - 1]"
                            class="px-5 py-2 bg-lime-700 text-white rounded-md hover:bg-lime-800 disabled:opacity-50 disabled:cursor-not-allowed">
                            Next
                        </button>
                        <button v-if="currentQuestion === totalQuestions" @click="submitQuiz"
                            :disabled="!answers[currentQuestion - 1]"
                            class="px-5 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all transform hover:scale-105">
                            Finish Quiz
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="!loading && questions.length === 0" class="text-center text-lg text-gray-700">
                No quiz questions found. 📭
            </div>

            <!-- Result Section -->
            <div v-if="showResult"
                class="bg-gradient-to-br from-indigo-100 via-purple-100 to-blue-100 py-4 sm:p-8 rounded-2xl sm:shadow-xl border sm:border-2 border-lime-400 result-container relative mt-8 w-full max-w-2xl mx-auto flex items-center justify-center overflow-hidden min-h-[400px] backdrop-blur-sm">
                <canvas ref="confettiCanvas" class="absolute inset-0 pointer-events-none w-full h-full"></canvas>

                <div class="z-10 text-center w-full">
                    <transition name="fade" mode="out-in">
                        <div v-if="scorePercentage >= 70" key="success">
                            <div class="mb-6">
                                <div
                                    class="w-24 h-24 mx-auto bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <h2
                                class="text-4xl font-bold text-gray-800 mb-4 bg-clip-text text-transparent bg-gradient-to-r from-green-500 to-blue-600">
                                Congratulations! 🎉
                            </h2>
                            <p class="text-xl text-gray-700 mb-2">
                                You scored
                                <span class="font-bold text-2xl text-green-600">{{ scorePercentage }}%</span>
                            </p>
                            <p class="text-lg text-gray-600 mb-6">
                                You answered
                                <span class="font-bold">{{
                                    correctAnswers
                                    }}</span>
                                out of
                                <span class="font-bold">{{
                                    totalQuestions
                                    }}</span>
                                correctly.
                            </p>
                            <div class="animate-bounce mt-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-yellow-500"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>
                        </div>
                        <div v-else key="retry">
                            <div class="mb-6">
                                <div
                                    class="w-24 h-24 mx-auto bg-gradient-to-r from-rose-400 to-pink-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                                Quiz Completed
                            </h2>
                            <p class="text-xl text-gray-700 mb-2">
                                You scored
                                <span class="font-bold text-2xl text-rose-600">{{ scorePercentage }}%</span>
                            </p>
                            <p class="text-lg text-gray-600 mb-6">
                                You answered
                                <span class="font-bold">{{
                                    correctAnswers
                                    }}</span>
                                out of
                                <span class="font-bold">{{
                                    totalQuestions
                                    }}</span>
                                correctly.
                            </p>
                            <p class="text-lg text-gray-600 mb-6">
                                Keep practicing to improve your score!
                            </p>
                        </div>
                    </transition>

                    <div class="flex gap-4 justify-center mt-8">
                        <button v-if="scorePercentage < 70" @click="retakeQuiz"
                            class="px-6 py-3 bg-gradient-to-r from-rose-500 to-pink-600 text-white rounded-lg hover:shadow-lg transition-all transform focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-opacity-50">
                            Retake Quiz
                        </button>
                        <button @click="checkAnswer()"
                            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:shadow-lg transition-all transform focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50">
                            Check Answers
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.result-container {
    background-image: radial-gradient(circle at 10% 20%,
            rgba(200, 255, 200, 0.2) 0%,
            rgba(200, 220, 255, 0.2) 90%);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }

    50% {
        transform: translateY(-10px);
    }

    100% {
        transform: translateY(0px);
    }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>
