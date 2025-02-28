<template>
    <div class="container max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center text-lime-700">
            Manage Quiz
        </h1>

        <!-- Display Spinner while loading -->
        <div v-if="loading">
            <Spinner />
        </div>

        <!-- Quizzes Table -->
        <div v-else>
            <div class="bg-white overflow-hidden">
                <div class="overflow-x-auto">
                    <table
                        class="w-full border-collapse border border-gray-300"
                    >
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-3 border">#</th>
                                <th class="p-3 border">Question</th>
                                <th class="p-3 border">Choices</th>
                                <th class="p-3 border">Answer</th>
                                <th class="p-3 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(quiz, index) in paginatedData"
                                :key="quiz.id"
                                class="text-left"
                            >
                                <td class="p-3 border">
                                    {{
                                        (pagination.current_page - 1) *
                                            perPage +
                                        index +
                                        1
                                    }}
                                </td>
                                <td class="p-3 border">
                                    {{ quiz.question || "No question" }}
                                </td>
                                <td class="p-3 border">
                                    <select
                                        v-if="quiz.choice && quiz.choice.length"
                                        class="border-none px-2 py-1"
                                    >
                                        <option
                                            v-for="(choice, i) in quiz.choice"
                                            :key="i"
                                        >
                                            {{ choice }}
                                        </option>
                                    </select>
                                    <span v-else>-</span>
                                </td>
                                <td class="p-3 border">
                                    {{
                                        Array.isArray(quiz.answer)
                                            ? quiz.answer.join(", ")
                                            : quiz.answer
                                    }}
                                </td>
                                <td class="p-3 border space-x-2">
                                    <button
                                        @click="openEditModal(quiz)"
                                        class="text-lime-500 text-lg hover:text-lime-600 transition"
                                        title="Edit Quiz"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        @click="openDeleteModal(quiz)"
                                        class="text-red-500 text-lg hover:text-red-600 transition"
                                        title="Delete Quiz"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div
                v-if="pagination.last_page > 1"
                class="mt-4 flex justify-between items-center"
            >
                <button
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="bg-lime-700 text-white px-4 py-2 rounded disabled:opacity-50 transition"
                >
                    Previous
                </button>
                <span class="text-sm text-gray-500">
                    Page {{ pagination.current_page }} of
                    {{ pagination.last_page }}
                </span>
                <button
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="bg-lime-700 text-white px-4 py-2 rounded disabled:opacity-50 transition"
                >
                    Next
                </button>
            </div>
        </div>

        <!-- Edit Quiz Modal -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
                <h2 class="text-xl font-semibold mb-4">Edit Quiz</h2>
                <label class="block text-sm font-medium">Question:</label>
                <textarea
                    v-model="selectedQuiz.question"
                    class="w-full border p-2 rounded mt-1"
                ></textarea>
                <label class="block text-sm font-medium mt-3">
                    Select or Add a Choice:
                </label>
                <select
                    v-model="selectedQuiz.answer"
                    class="w-full border p-2 rounded mt-1"
                >
                    <option
                        v-for="(choice, index) in selectedQuiz.choice"
                        :key="index"
                        :value="choice"
                    >
                        {{ choice }}
                    </option>
                </select>
                <div class="mt-3">
                    <strong>Selected Answer:</strong>
                    <span>{{ selectedQuiz.answer }}</span>
                </div>
                <div class="flex justify-end mt-4">
                    <button
                        @click="showEditModal = false"
                        class="px-4 py-2 bg-gray-400 text-white rounded mr-2 transition hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateQuiz"
                        class="px-4 py-2 bg-lime-600 text-white rounded transition hover:bg-lime-700"
                    >
                        Update
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">
                <h2
                    class="text-xl font-semibold text-red-600 mb-4 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h2>
                <p class="text-gray-700 mb-6">
                    Are you sure you want to delete this quiz?
                </p>
                <div class="flex justify-between">
                    <button
                        @click="confirmDelete"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-500 flex items-center gap-2"
                    >
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button
                        @click="cancelDelete"
                        class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import Spinner from "../Layout/Spinner.vue";
import { useToast } from "vue-toastification";

const toast = useToast();

const perPage = 10;
const allQuizzes = ref([]);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: perPage,
});
const loading = ref(true);

// Modal state variables
const showEditModal = ref(false);
const showDeleteModal = ref(false);

// Selected quiz for editing
const selectedQuiz = ref({
    id: null,
    question: "",
    question_type: "choice", // Force valid question_type
    choice: [],
    answer: "",
});

// Quiz to delete
const quizToDelete = ref(null);

// Computed paginated data
const paginatedData = computed(() => {
    const start = (pagination.value.current_page - 1) * perPage;
    return allQuizzes.value.slice(start, start + perPage);
});

// Helper: Parse choices (in case stored as JSON)
const parseChoices = (choice) => {
    if (!choice) return [];
    try {
        return typeof choice === "string" ? JSON.parse(choice) : choice;
    } catch (error) {
        console.error("Invalid choice format:", choice);
        return [];
    }
};

// Fetch quizzes from API
const fetchQuizzes = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/Quize`);
        const quizzesData = response.data.data.map((quiz) => ({
            ...quiz,
            choice: parseChoices(quiz.choice),
            answer: quiz.answer || "",
        }));
        allQuizzes.value = quizzesData;
        pagination.value.current_page = 1;
        pagination.value.last_page = Math.ceil(
            allQuizzes.value.length / perPage
        );
    } catch (error) {
        console.error(
            "Error fetching quizzes:",
            error.response?.data || error.message
        );
        toast.error("Error fetching quizzes.");
    } finally {
        loading.value = false;
    }
};

// Open edit modal for a quiz
const openEditModal = (quiz) => {
    selectedQuiz.value = {
        ...quiz,
        question_type: "choice",
        choice: quiz.choice ? parseChoices(quiz.choice) : [],
        answer: quiz.answer || "",
    };
    showEditModal.value = true;
};

// Open delete confirmation modal for a quiz
const openDeleteModal = (quiz) => {
    quizToDelete.value = quiz;
    showDeleteModal.value = true;
};

// Confirm deletion of a quiz
const confirmDelete = async () => {
    if (!quizToDelete.value) return;
    try {
        await axios.delete(`/api/Quize/${quizToDelete.value.id}`);
        toast.success("Quiz deleted successfully!");
        await fetchQuizzes();
        showDeleteModal.value = false;
        quizToDelete.value = null;
    } catch (error) {
        console.error(
            "Error deleting quiz:",
            error.response?.data || error.message
        );
        toast.error("Error deleting quiz.");
    }
};

// Cancel deletion
const cancelDelete = () => {
    showDeleteModal.value = false;
    quizToDelete.value = null;
};

// Update quiz (edit)
const updateQuiz = async () => {
    if (!selectedQuiz.value) {
        console.error("No quiz selected for updating.");
        return;
    }
    try {
        // Prepare payload with forced question_type and answer as string
        const payload = {
            question_type: "choice",
            question: selectedQuiz.value.question,
            choice: selectedQuiz.value.choice,
            answer: selectedQuiz.value.answer,
        };
        console.log("Updating quiz with payload:", payload);
        await axios.put(`/api/Quize/${selectedQuiz.value.id}`, payload);
        toast.success("Quiz updated successfully!");
        await fetchQuizzes();
        showEditModal.value = false;
    } catch (error) {
        console.error(
            "Error updating quiz:",
            error.response?.data || error.message
        );
        toast.error("Error updating quiz.");
    }
};

// Change page in pagination
const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        pagination.value.current_page = page;
    }
};

// Fetch quizzes on component mount
onMounted(fetchQuizzes);
</script>

<style scoped>
textarea {
    min-height: 100px;
}
select {
    min-height: 40px;
}
button {
    transition: background-color 0.3s ease;
}
</style>
