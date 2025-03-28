<script setup>
    import { ref, computed, onMounted, nextTick, watch } from "vue";
    import Axios from "axios";
    import confetti from "canvas-confetti";
    import Spinner from "../Layout/Spinner.vue";
    import { toast } from "vue3-toastify";
    import 'vue3-toastify/dist/index.css';

    import { useAppStore } from "@/store/useAppStore";
    const appStore = useAppStore();
    const authUser = appStore.authUser; 

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
 
    const showHintForQuestion = ref(false);

    const totalQuestions = computed(() => questions.value.length);
    const quiz = computed(() => questions.value[currentQuestion.value - 1] || null);

    const scorePercentage = computed(() =>
        totalQuestions.value > 0
            ? (correctAnswers.value / totalQuestions.value) * 100
            : 0
    );

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
        if (quiz.value && quiz.value.answer.includes(answers.value[currentQuestion.value - 1])) {
            correctAnswers.value += 1;
        }
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
        if (quiz.value && quiz.value.answer.includes(answers.value[currentQuestion.value - 1])) {
            correctAnswers.value += 1;
        }
        showResult.value = true;
        await nextTick();
        if (scorePercentage.value >= 70) {
            launchConfetti();
        }
        storeResult();
    };

    const retakeQuiz = () => {
        currentQuestion.value = 1;
        correctAnswers.value = 0;
        showResult.value = false;
        answers.value = new Array(quizData.value.length).fill(null);
        showHintForQuestion.value = false;
    };

    const storeResult = async () => {
        try {
            const payload = {
                result: correctAnswers.value,
                exam_id: examData.value?.id,
            };

            const response = await Axios.post("/api/results", payload);
            console.log("Result saved:", response.data);
            toast.success("Quiz result saved successfully!");
        } catch (error) {
            if (error.response && error.response.status === 409) {
                console.log("Result already exists:", error.response.data);
                toast.info("Your quiz result is already recorded.");
            } else {
                console.error("Error saving result:", error);
                toast.error("There was an error saving your quiz result.");
            }
        }
    };


    const launchConfetti = () => {
        const myCanvas = confettiCanvas.value;
        if (!myCanvas) return;
        const confettiInstance = confetti.create(myCanvas, { resize: true });
        let count = 0;
        const interval = setInterval(() => {
            if (count >= 3) {
                clearInterval(interval);
                return;
            }
            confettiInstance({
                particleCount: 200,
                spread: 70,
                origin: { y: 0.6 },
            });
            count++;
        }, 1000);
    };

    const toggleHint = () => {
        if (quiz.value && quiz.value.hint && quiz.value.hint.trim() !== "") {
            showHintForQuestion.value = !showHintForQuestion.value;
        } else {
            toast.info("No hint available for this question.");
        }
    };
</script>

<template>
    <div class="container mx-auto p-6">
        <Spinner v-if="loading" />
        <div v-else>

            <!-- Exam Information -->
            <div v-if="examData"
                class="bg-gradient-to-r from-lime-300 to-lime-500 p-6 rounded-lg shadow-md mb-8 text-gray-800">
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
                                        answers[currentQuestion - 1] === choice,
                                    'hover:border-lime-500 hover:bg-gray-100':
                                        answers[currentQuestion - 1] !== choice,
                                },
                            ]">
                                <input type="radio" v-model="answers[currentQuestion - 1]" :value="choice"
                                    class="form-radio h-4 w-4 text-lime-500" />
                                <span class="text-lg text-gray-700">{{ choice }}</span>
                            </label>
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
                class="bg-white p-6 h-96 rounded-lg shadow-lg border border-lime-500">
                <canvas ref="confettiCanvas" class="absolute inset-0 pointer-events-none w-full h-full"></canvas>
                <transition name="fade">
                    <div class="z-10 text-center">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">
                            Quiz Completed 🎉
                        </h2>
                        <p class="mt-4 text-lg text-gray-700">
                            You answered
                            <span class="font-bold">{{ correctAnswers }}</span>
                            out of
                            <span class="font-bold">{{ totalQuestions }}</span>
                            correctly.
                        </p>
                        <button v-if="scorePercentage < 70" @click="retakeQuiz"
                            class="mt-6 px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all transform hover:scale-105">
                            Retake Quiz 🔄
                        </button>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>