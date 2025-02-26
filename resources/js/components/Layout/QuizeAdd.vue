<template>
    <div class="p-6 max-w-2xl mx-auto bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            Add New Quiz
        </h2>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Question -->
            <div>
                <label
                    for="question"
                    class="block text-sm font-semibold text-gray-700"
                    >Question</label
                >
                <input
                    v-model="quiz.question"
                    type="text"
                    id="question"
                    placeholder="Enter quiz question"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                    :class="{ 'border-red-500': formErrors.question }"
                />
                <p v-if="formErrors.question" class="text-red-500 text-sm mt-1">
                    {{ formErrors.question }}
                </p>
            </div>

            <!-- Choices -->
            <div>
                <label class="block text-sm font-semibold text-gray-700"
                    >Choices</label
                >
                <div class="space-y-2 mt-2">
                    <div class="flex items-center">
                        <input
                            v-model="quiz.choices[0]"
                            type="text"
                            placeholder="Choice 1"
                            class="w-full p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            :class="{ 'border-red-500': formErrors.choices }"
                        />
                        <button
                            type="button"
                            @click="removeChoice(0)"
                            class="ml-2 text-red-500 hover:text-red-700"
                        >
                            Remove
                        </button>
                    </div>
                    <div class="flex items-center">
                        <input
                            v-model="quiz.choices[1]"
                            type="text"
                            placeholder="Choice 2"
                            class="w-full p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            :class="{ 'border-red-500': formErrors.choices }"
                        />
                        <button
                            type="button"
                            @click="removeChoice(1)"
                            class="ml-2 text-red-500 hover:text-red-700"
                        >
                            Remove
                        </button>
                    </div>
                    <div class="flex items-center">
                        <input
                            v-model="quiz.choices[2]"
                            type="text"
                            placeholder="Choice 3"
                            class="w-full p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            :class="{ 'border-red-500': formErrors.choices }"
                        />
                        <button
                            type="button"
                            @click="removeChoice(2)"
                            class="ml-2 text-red-500 hover:text-red-700"
                        >
                            Remove
                        </button>
                    </div>
                    <div class="flex items-center">
                        <input
                            v-model="quiz.choices[3]"
                            type="text"
                            placeholder="Choice 4"
                            class="w-full p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                            :class="{ 'border-red-500': formErrors.choices }"
                        />
                        <button
                            type="button"
                            @click="removeChoice(3)"
                            class="ml-2 text-red-500 hover:text-red-700"
                        >
                            Remove
                        </button>
                    </div>
                </div>
                <p v-if="formErrors.choices" class="text-red-500 text-sm mt-1">
                    {{ formErrors.choices }}
                </p>
            </div>

            <!-- Correct Answer -->
            <div>
                <label class="block text-sm font-semibold text-gray-700"
                    >Correct Answer</label
                >
                <select
                    v-model="quiz.correctAnswer"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                    :class="{ 'border-red-500': formErrors.correctAnswer }"
                >
                    <option value="" disabled>Select the correct answer</option>
                    <option
                        v-for="(choice, index) in quiz.choices"
                        :key="index"
                        :value="index"
                    >
                        {{ choice }}
                    </option>
                </select>
                <p
                    v-if="formErrors.correctAnswer"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ formErrors.correctAnswer }}
                </p>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button
                    type="submit"
                    class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-800 focus:outline-none focus:ring-2 focus:ring-lime-700 transition-colors"
                >
                    Add Quiz
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from "vue";

const quiz = ref({
    question: "",
    choices: ["", "", "", ""],
    correctAnswer: null,
});

const formErrors = ref({
    question: "",
    choices: "",
    correctAnswer: "",
});

const removeChoice = (index) => {
    quiz.value.choices.splice(index, 1);
    quiz.value.choices.push("");
};

const validateForm = () => {
    formErrors.value = {
        question: "",
        choices: "",
        correctAnswer: "",
    };

    let isValid = true;

    // Validate Question
    if (!quiz.value.question) {
        formErrors.value.question = "Question is required.";
        isValid = false;
    }

    // Validate Choices
    if (quiz.value.choices.some((choice) => choice.trim() === "")) {
        formErrors.value.choices = "All choices are required.";
        isValid = false;
    }

    // Validate Correct Answer
    if (quiz.value.correctAnswer === null) {
        formErrors.value.correctAnswer = "You must select the correct answer.";
        isValid = false;
    }

    return isValid;
};

const handleSubmit = () => {
    if (!validateForm()) {
        return;
    }

    // Submit the quiz data
    console.log("New Quiz Added:", quiz.value);
    alert("Quiz added successfully!");
    quiz.value = {
        question: "",
        choices: ["", "", "", ""],
        correctAnswer: null,
    }; // Reset form after submission
};
</script>

<style scoped>
/* Add custom styles here */
</style>
