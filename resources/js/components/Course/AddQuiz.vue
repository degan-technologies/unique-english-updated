<template>
    <div class="p-5 max-w-2xl mx-auto bg-white rounded-lg">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-1">
            Add New Quiz
        </h2>

        <form @submit.prevent="submitQuiz" class="space-y-6">
            <!-- Question -->
            <div>
                <label class="block text-sm font-semibold text-gray-700">
                    Question
                </label>
                <textarea
                    v-model.trim="quizForm.question"
                    placeholder="Enter test question"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                ></textarea>
                <p v-if="errors.question" class="text-red-500 text-sm mt-1">
                    {{ errors.question }}
                </p>
            </div>

            <!-- Choices -->
            <div>
                <label class="block text-sm font-semibold text-gray-700">
                    Choices
                </label>
                <div class="space-y-2 mt-2">
                    <div
                        v-for="(choice, index) in quizForm.choice"
                        :key="index"
                        class="flex items-center space-x-2"
                    >
                        <input
                            v-model.trim="quizForm.choice[index]"
                            type="text"
                            class="w-full p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            placeholder="Enter choice"
                        />
                    </div>
                </div>
                <button
                    type="button"
                    @click="addChoice"
                    class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-800 mt-2"
                >
                    Add Choice
                </button>
                <p v-if="errors.choices" class="text-red-500 text-sm mt-1">
                    {{ errors.choices }}
                </p>
            </div>

            <!-- Correct Answer -->
            <div>
                <label class="block text-sm font-semibold text-gray-700">
                    Correct Answer
                </label>
                <select
                    v-model="quizForm.answer"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                >
                    <option value="" disabled>Select the correct answer</option>
                    <option
                        v-for="(choice, index) in quizForm.choice"
                        :key="index"
                        :value="choice"
                    >
                        {{ choice }}
                    </option>
                </select>
                <p
                    v-if="errors.correctAnswer"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ errors.correctAnswer }}
                </p>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button
                    type="submit"
                    class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-800 transition focus:outline-none focus:ring-2 focus:ring-lime-700"
                >
                    Add Quiz
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, defineEmits } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();
const emit = defineEmits(["quizAdded"]);

const quizForm = ref({
    question: "",
    choice: ["", ""], // Minimum 2 choices
    answer: "",
    question_type: "choice", // Default type (for the backend)
});

const errors = ref({});

// Add a new choice
const addChoice = () => {
    if (quizForm.value.choice.length < 6) {
        quizForm.value.choice.push("");
    }
};

// Remove a choice (if needed in the future)
const removeChoice = (index) => {
    if (quizForm.value.choice.length > 2) {
        quizForm.value.choice.splice(index, 1);
    }
};

// Handle form submission
const submitQuiz = async () => {
    errors.value = {};

    // Validate question
    if (!quizForm.value.question.trim()) {
        errors.value.question = "Question is required.";
    }

    // Validate choices
    if (quizForm.value.choice.some((choice) => choice.trim() === "")) {
        errors.value.choices = "All choices must be filled.";
    }

    // Check for duplicate choices
    const uniqueChoices = new Set(quizForm.value.choice);
    if (uniqueChoices.size !== quizForm.value.choice.length) {
        errors.value.choices = "Choices must be unique.";
    }

    if (!quizForm.value.answer) {
        errors.value.correctAnswer = "Select the correct answer.";
    }

    if (Object.keys(errors.value).length > 0) return;

    try {
        // Prepare payload
        const payload = {
            question: quizForm.value.question,
            choice: quizForm.value.choice,
            answer: quizForm.value.answer,
            question_type: quizForm.value.question_type,
        };

        const response = await axios.post("/api/Quize", payload);

        // Use toast to show success message
        toast.success(response.data.message || "Quiz added successfully!");

        // Emit event to parent component to refresh quiz list
        emit("quizAdded", response.data.data);

        // Reset form after submission
        quizForm.value = {
            question: "",
            choice: ["", ""],
            answer: "",
            question_type: "choice",
        };
    } catch (error) {
        // Use toast to show error message
        toast.error(error.response?.data.message || "Failed to add quiz");
    }
};
</script>
