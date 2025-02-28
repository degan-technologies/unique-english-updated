<template>
    <div class="container max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center text-lime-700">
            Manage Tests
        </h1>

        <!-- Display Tests or Spinner if loading -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <Spinner />
        </div>
        <div v-else>
            <table class="w-full border-collapse border border-gray-300">
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
                        v-for="(test, index) in paginatedTests"
                        :key="test.id"
                        class="text-left"
                    >
                        <td class="p-3 border">
                            {{ getQuestionNumber(index) }}
                        </td>
                        <td class="p-3 border">{{ test.question }}</td>
                        <td class="p-3 border">
                            <select
                                v-if="test.choices && test.choices.length"
                                class="border-none px-2 py-1 bg-white"
                            >
                                <option
                                    v-for="(choice, i) in test.choices"
                                    :key="i"
                                >
                                    {{ choice }}
                                </option>
                            </select>
                            <span v-else>-</span>
                        </td>
                        <td class="p-3 border">
                            {{
                                Array.isArray(test.answer)
                                    ? test.answer.join(", ")
                                    : test.answer
                            }}
                        </td>
                        <td class="p-3 border space-x-2">
                            <button
                                @click="editTest(test)"
                                class="text-lime-500 text-lg hover:text-lime-700 transition"
                                title="Edit Test"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                @click="openDeleteModal(test)"
                                class="text-red-500 text-lg hover:text-red-700 transition"
                                title="Delete Test"
                            >
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div
            v-if="tests.length > itemsPerPage"
            class="mt-4 flex justify-between items-center"
        >
            <button
                @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="px-4 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Previous
            </button>

            <span class="text-lg font-semibold text-gray-700">
                Page {{ currentPage }} of {{ totalPages }}
            </span>

            <button
                @click="changePage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="px-4 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Next
            </button>
        </div>

        <!-- Edit Test Modal -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
                <h2 class="text-xl font-semibold mb-4">Edit Test</h2>
                <label class="block text-sm font-medium">Question:</label>
                <textarea
                    v-model="editedTest.question"
                    class="w-full border p-2 rounded mt-1"
                ></textarea>
                <label class="block text-sm font-medium mt-3">
                    Select or Add a Choice:
                </label>
                <select
                    v-model="editedTest.answer"
                    class="w-full border p-2 rounded mt-1"
                >
                    <option
                        v-for="(choice, index) in editedTest.choices"
                        :key="index"
                        :value="choice"
                    >
                        {{ choice }}
                    </option>
                </select>
                <div class="mt-3">
                    <strong>Selected Answer:</strong>
                    <span>{{ editedTest.answer }}</span>
                </div>
                <div class="flex justify-end mt-4">
                    <button
                        @click="showEditModal = false"
                        class="px-4 py-2 bg-gray-400 text-white rounded mr-2 transition hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateTest"
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
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center"
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
                <h2
                    class="text-xl font-bold text-red-600 mb-4 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h2>
                <p class="text-gray-700 mb-6">
                    Are you sure you want to delete this test?
                </p>
                <div class="flex justify-between">
                    <button
                        @click="deleteTest"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-500 flex items-center gap-2"
                    >
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button
                        @click="showDeleteModal = false"
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

// State variables
const tests = ref([]);
const currentPage = ref(1);
const itemsPerPage = 10;
const loading = ref(true);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editedTest = ref({ id: null, question: "", choices: [], answer: "" });
const testToDelete = ref(null);

// Computed: paginated tests and total pages
const paginatedTests = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return tests.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => Math.ceil(tests.value.length / itemsPerPage));

// Get dynamic question number
const getQuestionNumber = (index) => {
    return (currentPage.value - 1) * itemsPerPage + index + 1;
};

// Fetch tests from API
const fetchTests = async () => {
    loading.value = true;
    try {
        const response = await axios.get("/api/tests");
        tests.value = response.data.data.map((test) => ({
            ...test,
            // Ensure answer is always an array; for display and editing we use its first element.
            answer: Array.isArray(test.answer) ? test.answer : [test.answer],
        }));
    } catch (error) {
        console.error(
            "Error fetching tests:",
            error.response?.data || error.message
        );
        toast.error("Error fetching tests.");
    } finally {
        loading.value = false;
    }
};

// Change page in pagination
const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Open Edit Modal and clone test data into editedTest.
// We set the answer to the first element from the answer array.
const editTest = (test) => {
    editedTest.value = {
        ...test,
        choices: test.choices ? [...test.choices] : [],
        answer: Array.isArray(test.answer) ? test.answer[0] : test.answer,
    };
    showEditModal.value = true;
};

// Update test function
const updateTest = async () => {
    try {
        // Prepare payload with answer sent as an array.
        const payload = {
            question: editedTest.value.question,
            choices: editedTest.value.choices,
            answer: [editedTest.value.answer],
        };
        console.log("Updating test with payload:", payload);
        await axios.put(`/api/tests/${editedTest.value.id}`, payload);
        toast.success("Test updated successfully!");
        showEditModal.value = false;
        await fetchTests();
    } catch (error) {
        console.error(
            "Error updating test:",
            error.response?.data || error.message
        );
        toast.error("Error updating test.");
    }
};

// Open Delete Modal
const openDeleteModal = (test) => {
    testToDelete.value = test.id;
    showDeleteModal.value = true;
};

// Delete test function
const deleteTest = async () => {
    try {
        await axios.delete(`/api/tests/${testToDelete.value}`);
        toast.success("Test deleted successfully!");
        showDeleteModal.value = false;
        await fetchTests();
    } catch (error) {
        console.error(
            "Error deleting test:",
            error.response?.data || error.message
        );
        toast.error("Error deleting test.");
    }
};

// Fetch tests on component mount
onMounted(fetchTests);
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
