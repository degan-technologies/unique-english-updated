<template>
    <div class="w-full min-h-screen flex items-center justify-center p-6">
        <div
            class="w-full max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-2xl"
        >
            <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">
                English Level Test
            </h1>

            <!-- Question Section -->
            <div v-if="!submitted" class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-700">
                        Question {{ currentQuestionIndex + 1 }} of
                        {{ questions.length }}
                    </h2>
                    <p class="text-lg text-gray-800 mt-4">
                        {{ currentQuestion.text }}
                    </p>
                </div>
                <div class="mt-4 space-y-4">
                    <label
                        v-for="(option, index) in currentQuestion.options"
                        :key="index"
                        class="block p-4 border rounded-lg cursor-pointer hover:bg-lime-100 transition-all"
                    >
                        <input
                            type="radio"
                            :name="'question-' + currentQuestionIndex"
                            :value="index"
                            v-model="answers[currentQuestionIndex]"
                            class="mr-2"
                        />
                        {{ option }}
                    </label>
                </div>

                <div class="flex justify-between mt-6">
                    <button
                        @click="prevQuestion"
                        :disabled="currentQuestionIndex === 0"
                        class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 disabled:bg-gray-300 transition-all"
                    >
                        Previous
                    </button>
                    <button
                        v-if="currentQuestionIndex < questions.length - 1"
                        @click="nextQuestion"
                        class="px-6 py-3 bg-lime-500 text-white rounded-lg hover:bg-lime-600 transition-all"
                    >
                        Next
                    </button>
                    <button
                        v-else
                        @click="submitTest"
                        class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all"
                    >
                        Submit
                    </button>
                </div>
            </div>

            <!-- Result Section -->
            <div v-else class="text-center space-y-6">
                <h2
                    class="text-3xl font-bold text-gray-800 animate-fade-in"
                    :class="score >= 12 ? 'text-green-500' : 'text-blue-500'"
                >
                    {{ resultMessage }}
                </h2>
                <p class="text-lg text-gray-700">
                    Your English Level: <strong>{{ englishLevel }}</strong>
                </p>
                <p class="text-gray-600">
                    You scored <strong>{{ score }}</strong> out of
                    <strong>{{ questions.length }}</strong
                    >.
                </p>
                <p
                    v-if="score >= 12"
                    class="text-xl text-green-600 font-semibold"
                >
                    🎉 Congratulations! Keep up the great work! 🎉
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

// Questions Data
const questions = ref([
    {
        text: "Choose the correct sentence:",
        options: [
            "She go to school.",
            "She goes to school.",
            "She going to school.",
        ],
        correctAnswerIndex: 1,
    },
    {
        text: "The weather ______ very hot today.",
        options: ["is", "are", "am"],
        correctAnswerIndex: 0,
    },
    {
        text: "What is the synonym of 'happy'?",
        options: ["Sad", "Joyful", "Angry"],
        correctAnswerIndex: 1,
    },
    {
        text: "He _______ been working all day.",
        options: ["has", "have", "is"],
        correctAnswerIndex: 0,
    },
    {
        text: "If I ______ rich, I would travel the world.",
        options: ["was", "were", "am"],
        correctAnswerIndex: 1,
    },
    {
        text: "Which is an example of passive voice?",
        options: [
            "She is painting the wall.",
            "The wall is being painted by her.",
            "She painted the wall yesterday.",
        ],
        correctAnswerIndex: 1,
    },
    {
        text: "Find the mistake: 'I didn't went to the party.'",
        options: ["'I' is wrong", "'didn't' is wrong", "'went' is wrong"],
        correctAnswerIndex: 2,
    },
    {
        text: "Meaning of 'ambiguous':",
        options: ["Clear", "Uncertain", "Boring"],
        correctAnswerIndex: 1,
    },
    {
        text: "Example of subjunctive sentence:",
        options: [
            "She works every day.",
            "If I were you, I would apologize.",
            "He is playing football.",
        ],
        correctAnswerIndex: 1,
    },
    {
        text: "Choose the correct verb form: 'He _____ already eaten.'",
        options: ["has", "have", "is"],
        correctAnswerIndex: 0,
    },
    {
        text: "Which is an adverb?",
        options: ["Quickly", "House", "Bright"],
        correctAnswerIndex: 0,
    },
    {
        text: "Identify the preposition: 'The book is on the table.'",
        options: ["book", "on", "table"],
        correctAnswerIndex: 1,
    },
    {
        text: "What does 'bilingual' mean?",
        options: [
            "Can speak two languages",
            "Speaks very fast",
            "Writes poetry",
        ],
        correctAnswerIndex: 0,
    },
    {
        text: "Choose the correct spelling:",
        options: ["Recieve", "Receive", "Recive"],
        correctAnswerIndex: 1,
    },
    {
        text: "What is a synonym for 'important'?",
        options: ["Trivial", "Crucial", "Ordinary"],
        correctAnswerIndex: 1,
    },
]);

// State
const currentQuestionIndex = ref(0);
const answers = ref(Array(questions.value.length).fill(null));
const submitted = ref(false);

// Current Question
const currentQuestion = computed(
    () => questions.value[currentQuestionIndex.value]
);

// Navigation
const prevQuestion = () => {
    if (currentQuestionIndex.value > 0) currentQuestionIndex.value--;
};

const nextQuestion = () => {
    if (currentQuestionIndex.value < questions.value.length - 1)
        currentQuestionIndex.value++;
};

// Submit Test
const submitTest = () => {
    submitted.value = true;
};

// Calculate Score
const score = computed(() => {
    return answers.value.reduce((total, answer, index) => {
        if (answer === questions.value[index].correctAnswerIndex)
            return total + 1;
        return total;
    }, 0);
});

// Determine English Level
const englishLevel = computed(() => {
    if (score.value <= 5) return "A1 (Beginner)";
    if (score.value <= 8) return "A2 (Elementary)";
    if (score.value <= 11) return "B1 (Intermediate)";
    if (score.value <= 13) return "B2 (Upper Intermediate)";
    if (score.value <= 14) return "C1 (Advanced)";
    return "C2 (Proficient)";
});

// Result Message
const resultMessage = computed(() => {
    if (score.value >= 12) return "Excellent Job!";
    if (score.value >= 8) return "Good Effort!";
    return "Keep Practicing!";
});
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 1.5s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
