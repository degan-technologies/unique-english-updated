<template>
    <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            Manage Quiz
        </h2>

        <!-- Table for Quiz -->
        <table class="min-w-full table-auto border-collapse mb-6">
            <thead>
                <tr class="bg-lime-700 text-white">
                    <th class="px-6 py-3 text-left">Question</th>
                    <th class="px-6 py-3 text-left">Choices</th>
                    <th class="px-6 py-3 text-left">Correct Answer</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="quiz in quizzes"
                    :key="quiz.id"
                    class="border-b hover:bg-gray-50"
                >
                    <td class="px-6 py-4">{{ quiz.question }}</td>
                    <td class="px-6 py-4">
                        <ul>
                            <li
                                v-for="(choice, index) in quiz.choices"
                                :key="index"
                            >
                                {{ choice }}
                            </li>
                        </ul>
                    </td>
                    <td class="px-6 py-4">
                        {{ quiz.choices[quiz.correctAnswer] }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button
                            @click="editQuiz(quiz.id)"
                            class="text-blue-500 hover:text-blue-700 mr-2"
                        >
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button
                            @click="deleteQuiz(quiz.id)"
                            class="text-red-500 hover:text-red-700"
                        >
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Edit Quiz Modal (hidden by default) -->
        <div
            v-if="editMode"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center"
        >
            <div class="bg-white p-4 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-bold text-center text-lime-700 mt-4">
                    Edit Quiz
                </h2>
                <form @submit.prevent="updateQuiz">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700"
                            >Question</label
                        >
                        <input
                            v-model="editedQuiz.question"
                            type="text"
                            class="w-full mt-2 p-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700"
                            >Choices</label
                        >
                        <div class="space-y-2 mt-2">
                            <div
                                v-for="(choice, index) in editedQuiz.choices"
                                :key="index"
                            >
                                <input
                                    v-model="editedQuiz.choices[index]"
                                    type="text"
                                    class="w-full p-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                                    placeholder="Choice"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700"
                            >Correct Answer</label
                        >
                        <select
                            v-model="editedQuiz.correctAnswer"
                            class="w-full mt-2 p-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                        >
                            <option
                                v-for="(choice, index) in editedQuiz.choices"
                                :key="index"
                                :value="index"
                            >
                                {{ choice }}
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-between items-center">
                        <button
                            type="submit"
                            class="px-6 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 focus:outline-none focus:ring-2 focus:ring-lime-700"
                        >
                            Update
                        </button>
                        <button
                            @click="cancelEdit"
                            type="button"
                            class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

// Static quiz data
const quizzes = ref([
    {
        id: 1,
        question: "What is Vue.js?",
        choices: [
            "A JavaScript Framework",
            "A Database",
            "A CSS Library",
            "A Backend Language",
        ],
        correctAnswer: 0,
    },
    {
        id: 2,
        question: "What is the capital of France?",
        choices: ["Berlin", "Madrid", "Paris", "Rome"],
        correctAnswer: 2,
    },
    {
        id: 3,
        question: "Who invented JavaScript?",
        choices: [
            "Brendan Eich",
            "Linus Torvalds",
            "Tim Berners-Lee",
            "Guido van Rossum",
        ],
        correctAnswer: 0,
    },
]);

// Edit Mode
const editMode = ref(false);

// The quiz being edited
const editedQuiz = ref({});

// Edit Quiz function
const editQuiz = (id) => {
    const quizToEdit = quizzes.value.find((quiz) => quiz.id === id);
    if (quizToEdit) {
        editedQuiz.value = { ...quizToEdit }; // Copy the quiz data
        editMode.value = true;
    }
};

// Update Quiz function
const updateQuiz = () => {
    const index = quizzes.value.findIndex(
        (quiz) => quiz.id === editedQuiz.value.id
    );
    if (index !== -1) {
        quizzes.value[index] = { ...editedQuiz.value }; // Update the quiz data
        cancelEdit(); // Close the modal
    }
};

// Delete Quiz function
const deleteQuiz = (id) => {
    quizzes.value = quizzes.value.filter((quiz) => quiz.id !== id);
};

// Cancel Edit function (close modal)
const cancelEdit = () => {
    editMode.value = false;
    editedQuiz.value = {};
};
</script>

<style scoped>
/* Add custom styles here */
</style>
