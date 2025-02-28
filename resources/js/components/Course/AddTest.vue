<template>
    <div class="container max-w-2xl mx-auto p-5 bg-white">
        <h1 class="text-3xl font-bold mb-1 text-center text-lime-700">
            Create a New Test
        </h1>
        <form @submit.prevent="createTest" class="space-y-6">
            <!-- Question -->
            <div>
                <label
                    for="question"
                    class="block text-sm font-medium text-gray-700"
                >
                    Question
                </label>
                <textarea
                    v-model.trim="newTest.question"
                    class="mt-2 p-3 w-full border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                    placeholder="Enter your question"
                ></textarea>

                <p v-if="errors.question" class="text-red-500 text-sm mt-1">
                    {{ errors.question }}
                </p>
            </div>

            <!-- Choices -->
            <div>
                <label
                    for="choices"
                    class="block text-sm font-medium text-gray-700"
                >
                    Choices
                </label>
                <div class="space-y-2 mt-2">
                    <div
                        v-for="(choice, index) in newTest.choices"
                        :key="index"
                        class="flex items-center space-x-2"
                    >
                        <input
                            v-model="newTest.choices[index]"
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

            <!-- Answer -->
            <div>
                <label
                    for="answer"
                    class="block text-sm font-medium text-gray-700"
                >
                    Answer
                </label>
                <select
                    v-model="newTest.answer"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                >
                    <option value="" disabled>Select the correct answer</option>
                    <option
                        v-for="(choice, index) in newTest.choices"
                        :key="index"
                        :value="choice"
                    >
                        {{ choice }}
                    </option>
                </select>
                <p v-if="errors.answer" class="text-red-500 text-sm mt-1">
                    {{ errors.answer }}
                </p>
            </div>

            <div class="text-center">
                <button
                    type="submit"
                    class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-800"
                >
                    Create Test
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();

// State for storing form data and error messages
const newTest = ref({
    question: "",
    choices: ["", ""], // Start with two choices
    answer: "", // Answer will be a string (single choice selected)
});

const errors = ref({});

// Add a new choice input field
const addChoice = () => {
    if (newTest.value.choices.length < 6) {
        newTest.value.choices.push(""); // Add new empty input field for choice
    }
};

// Remove a choice input field (if needed)
const removeChoice = (index) => {
    if (newTest.value.choices.length > 2) {
        newTest.value.choices.splice(index, 1); // Remove selected choice
    }
};

// Create a new test
const createTest = async () => {
    errors.value = {}; // Clear previous errors

    // Validate question
    if (!newTest.value.question.trim()) {
        errors.value.question = "Question is required.";
    }

    // Validate choices
    if (newTest.value.choices.some((choice) => !choice.trim())) {
        errors.value.choices = "All choices must be filled.";
    } else if (newTest.value.choices.length < 2) {
        errors.value.choices = "You must provide at least two choices.";
    }

    // Check for duplicate choices
    const uniqueChoices = new Set(newTest.value.choices);
    if (uniqueChoices.size !== newTest.value.choices.length) {
        errors.value.choices = "Choices must be unique.";
    }

    // Validate answer (ensure it's selected and exists in choices)
    if (!newTest.value.answer.trim()) {
        errors.value.answer = "Answer is required.";
    } else if (!newTest.value.choices.includes(newTest.value.answer)) {
        errors.value.answer = "Answer must be one of the choices.";
    }

    // If there are errors, don't submit the form
    if (Object.keys(errors.value).length > 0) return;

    // Prepare test data for submission, wrap answer in an array
    const testData = {
        question: newTest.value.question,
        choices: newTest.value.choices,
        answer: [newTest.value.answer], // Send answer as an array
    };

    try {
        await axios.post("/api/tests", testData);
        newTest.value = { question: "", choices: ["", ""], answer: "" }; // Clear form after successful submission

        // Show a success toast notification
        toast.success("Test created successfully!");
    } catch (error) {
        console.error("Error creating test:", error);
        // Show an error toast notification
        toast.error("Error creating test.");
    }
};
</script>

<style scoped>
/* You can add additional styling if needed */
</style>
