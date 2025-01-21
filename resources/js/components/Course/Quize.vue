<template>
    <div class="max-w-xl mx-auto p-6 bg-white rounded-lg shadow-lg">
        <!-- Start Button -->
        <div v-if="quizStarted === false" class="text-center">
            <button
                @click="startQuiz"
                class="w-full py-2 bg-green-500 text-white rounded-lg hover:bg-green-600"
            >
                Start Quiz
            </button>
        </div>

        <!-- Quiz Questions -->
        <div v-else>
            <h1 class="text-3xl font-semibold text-center mb-6">Vue Quiz</h1>

            <!-- Show Questions until the final one -->
            <div
                v-if="
                    currentQuestionIndex < questions.length && result === null
                "
                class="mb-4"
            >
                <h3 class="text-xl font-semibold">
                    {{ questions[currentQuestionIndex].question }}
                </h3>

                <div
                    v-for="(answer, answerIndex) in questions[
                        currentQuestionIndex
                    ].answers"
                    :key="answerIndex"
                >
                    <label class="block">
                        <input
                            type="radio"
                            :name="'question' + currentQuestionIndex"
                            :value="answer"
                            v-model="userAnswers[currentQuestionIndex]"
                            class="mr-2"
                        />
                        {{ answer }}
                    </label>
                </div>

                <!-- Navigation buttons -->
                <div class="flex justify-between mt-4">
                    <button
                        v-if="currentQuestionIndex > 0"
                        @click="prevQuestion"
                        class="py-2 px-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                    >
                        Previous
                    </button>
                    <button
                        v-if="currentQuestionIndex < questions.length - 1"
                        @click="nextQuestion"
                        class="py-2 px-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                    >
                        Next
                    </button>
                </div>
            </div>

            <!-- Finish Button after the final question -->
            <div
                v-if="
                    currentQuestionIndex === questions.length - 1 &&
                    result === null
                "
                class="mt-4"
            >
                <button
                    @click="calculateResult"
                    class="w-full py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                >
                    Finish Quiz
                </button>
            </div>

            <!-- Show Result and Missed Questions -->
            <div v-if="result !== null" class="mt-6">
                <h3 class="text-2xl font-semibold">
                    You scored {{ result }} / {{ questions.length }}
                </h3>
                <p class="text-lg">Great job! Here are the missed questions:</p>
                <ul class="list-disc ml-4 mt-2">
                    <li v-for="(missed, index) in missedQuestions" :key="index">
                        <strong>{{ missed.question }}</strong
                        >: Your answer:
                        <span class="text-red-500">{{
                            missed.userAnswer
                        }}</span>
                        <br />
                        Correct answer:
                        <span class="text-green-500">{{
                            missed.correctAnswer
                        }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

// Define questions and answers
const questions = ref([
    {
        question: "What is the capital of France?",
        answers: ["Berlin", "Madrid", "Paris", "Rome"],
        correctAnswer: "Paris",
    },
    {
        question: "What is the largest planet in our solar system?",
        answers: ["Earth", "Jupiter", "Mars", "Saturn"],
        correctAnswer: "Jupiter",
    },
    {
        question: "Which programming language is used for building web pages?",
        answers: ["Python", "JavaScript", "C++", "Java"],
        correctAnswer: "JavaScript",
    },
]);

// Reactive states
const userAnswers = ref(Array(questions.value.length).fill(null));
const currentQuestionIndex = ref(0);
const quizStarted = ref(false);
const result = ref(null);
const missedQuestions = ref([]);

// Start the quiz
const startQuiz = () => {
    quizStarted.value = true;
    currentQuestionIndex.value = 0;
    result.value = null;
    missedQuestions.value = [];
};

// Go to the next question
const nextQuestion = () => {
    if (currentQuestionIndex.value < questions.value.length - 1) {
        currentQuestionIndex.value++;
    }
};

// Go to the previous question
const prevQuestion = () => {
    if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--;
    }
};

// Calculate the score and list missed questions
const calculateResult = () => {
    let score = 0;
    missedQuestions.value = [];

    questions.value.forEach((question, index) => {
        if (userAnswers.value[index] === question.correctAnswer) {
            score++;
        } else {
            missedQuestions.value.push({
                question: question.question,
                userAnswer: userAnswers.value[index],
                correctAnswer: question.correctAnswer,
            });
        }
    });

    result.value = score;
};
</script>

<style scoped>
/* Tailwind CSS will handle most of the styling. */
button {
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #2563eb;
}

h1,
h3 {
    font-family: "Arial", sans-serif;
}
</style>
