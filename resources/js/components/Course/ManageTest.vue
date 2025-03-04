<template>
    <div class="container mx-auto p-4">
        <div class="grid grid-cols-1 md:grid-cols-3">
            <!-- Left Panel: Paginated Test List -->
            <div class="col-span-1 bg-gray-50 p-4 shadow-md">
                <div
                    class="flex justify-between items-center border-b pb-2 mb-4"
                >
                    <h2 class="text-xl font-bold">Tests</h2>
                    <!-- Plus icon resets form for a new test -->
                    <i
                        @click="toggleNewTest"
                        class="fa-solid fa-plus text-lg text-gray-800 cursor-pointer"
                        title="New Test"
                    ></i>
                </div>
                <div
                    v-for="(test, index) in paginatedTests"
                    :key="test.id"
                    class="mb-2"
                >
                    <div
                        class="flex items-center justify-between p-2 bg-gray-100 rounded hover:bg-gray-200 cursor-pointer"
                        @click="onSelectTest(test)"
                    >
                        <div>
                            <!-- Display test number (across pages) and question preview -->
                            <h3 class="text-sm font-medium">
                                {{
                                    (currentPage - 1) * itemsPerPage +
                                    index +
                                    1
                                }}.
                                {{ test.question.slice(0, 50) }}
                                {{ test.question.length > 50 ? "..." : "" }}
                            </h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                @click.stop="openDeleteModal(test)"
                                class="text-red-500 hover:text-red-700"
                                title="Delete Test"
                            >
                                <i class="fa-solid fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Pagination Controls -->
                <div
                    v-if="tests.length > itemsPerPage"
                    class="mt-4 flex justify-center items-center"
                >
                    <!-- Previous Button -->
                    <button
                        @click="changePage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="px-4 py-1 text-lime-700 rounded-lg hover:text-lime-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <i class="fas fa-chevron-left"></i>
                        <!-- Chevron Left Icon -->
                    </button>

                    <!-- Next Button -->
                    <button
                        @click="changePage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="px-4 py-1 text-lime-700 rounded-lg hover:text-lime-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <i class="fas fa-chevron-right"></i>
                        <!-- Chevron Right Icon -->
                    </button>
                </div>
            </div>

            <!-- Right Panel: Test Meta Data Form -->
            <div class="col-span-1 md:col-span-2 bg-gray-50 p-4 shadow-md">
                <!-- Header for Existing Test -->
                <div
                    v-if="selectedTest"
                    class="flex items-center justify-between mb-4"
                >
                    <h2
                        @click="toggleCollapse"
                        class="text-lg font-bold text-gray-700 cursor-pointer"
                    >
                        {{ selectedTest.question }}
                    </h2>
                </div>
                <!-- Test Meta Data Form -->
                <form v-if="openCollapse" @submit.prevent class="space-y-6">
                    <!-- Question Field -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Question
                        </label>
                        <textarea
                            v-model="metaData.question"
                            @input="markUpdate('question')"
                            class="w-full mt-2 p-2 border rounded-lg focus:outline-none focus:ring-1 focus:ring-lime-700"
                            placeholder="Enter test question"
                        ></textarea>
                    </div>

                    <!-- Dynamic Choices List -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-gray-700"
                        >
                            Choices
                        </label>
                        <div
                            v-for="(choice, index) in metaData.choices"
                            :key="index"
                            class="flex items-center mt-2"
                        >
                            <!-- Radio button for selecting correct answer -->
                            <input
                                type="radio"
                                :name="'answer'"
                                class="mr-2"
                                width="4"
                                height="4"
                                :value="choice"
                                v-model="metaData.answer"
                                @change="markUpdate('answer')"
                            />
                            <!-- Choice input -->
                            <input
                                type="text"
                                v-model="metaData.choices[index]"
                                @input="markUpdate('choices')"
                                class="flex-1 p-2 border rounded-lg focus:outline-none focus:ring-1 focus:ring-lime-700"
                                placeholder="Enter choice"
                            />
                            <!-- Remove button (only if more than one choice exists) -->
                            <button
                                type="button"
                                v-if="metaData.choices.length > 1"
                                @click="removeChoice(index)"
                                class="ml-2 text-red-500 hover:text-red-700"
                                title="Remove choice"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <!-- Add Choice Button -->
                        <button
                            type="button"
                            @click="addChoice"
                            class="mt-2 px-4 py-2 text-lime-600 rounded-lg hover:text-lime-700 transition duration-200"
                        >
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <button
                        type="button"
                        v-if="openCollapse && readyForUpdate"
                        @click="updateTest"
                        class="w-full py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 transition duration-200"
                    >
                        Update Test
                    </button>

                    <!-- Create Test Button (only in new test mode) -->
                    <button
                        type="button"
                        v-if="isNewTestMode"
                        @click="storeTest"
                        class="w-full py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 transition duration-200"
                    >
                        Create Test
                    </button>
                </form>
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
import { useToast } from "vue-toastification";

const toast = useToast();

// State variables for tests
const tests = ref([]);
const loading = ref(true);
const selectedTest = ref(null);
const isNewTestMode = ref(false); // Flag for new test mode
const openCollapse = ref(true); // Toggle meta form collapse
const readyForUpdate = ref(false);

// Meta data for test creation/updating
const metaData = ref({
    question: "",
    choices: [],
    answer: null,
});

// Delete modal state
const showDeleteModal = ref(false);
const testToDelete = ref(null);

// Pagination state
const currentPage = ref(1);
const itemsPerPage = 5;
const paginatedTests = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return tests.value.slice(start, start + itemsPerPage);
});
const totalPages = computed(() => Math.ceil(tests.value.length / itemsPerPage));

// Toggle new test mode and reset meta data
function toggleNewTest() {
    isNewTestMode.value = true;
    selectedTest.value = null;
    openCollapse.value = true;
    // Initialize with four empty choices and no default answer selected
    metaData.value = { question: "", choices: ["", "", "", ""], answer: null };
    readyForUpdate.value = false;
}

// When a test is selected, load its data and exit new test mode
function onSelectTest(test) {
    isNewTestMode.value = false;
    if (selectedTest.value && selectedTest.value.id === test.id) {
        selectedTest.value = null;
    } else {
        selectedTest.value = { ...test };
        metaData.value = {
            question: test.question,
            choices: test.choices ? [...test.choices] : ["", "", "", ""],
            answer: test.answer ? test.answer[0] : null,
        };
        openCollapse.value = true;
        readyForUpdate.value = false;
    }
}

// Add a new empty choice field
function addChoice() {
    metaData.value.choices.push("");
    markUpdate("choices");
}

// Remove a choice at a given index
function removeChoice(index) {
    metaData.value.choices.splice(index, 1);
    if (!metaData.value.choices.length) {
        metaData.value.choices.push("");
    }
    if (metaData.value.answer === metaData.value.choices[index]) {
        metaData.value.answer = null;
    }
    markUpdate("choices");
}

// Mark that meta data has been changed (for update)
function markUpdate(field) {
    if (selectedTest.value) {
        if (
            field === "question" &&
            selectedTest.value.question !== metaData.value.question
        ) {
            readyForUpdate.value = true;
        }
        if (field === "choices") {
            const original = selectedTest.value.choices || [];
            if (
                JSON.stringify(original) !==
                JSON.stringify(metaData.value.choices)
            ) {
                readyForUpdate.value = true;
            }
        }
        if (
            field === "answer" &&
            selectedTest.value.answer[0] !== metaData.value.answer
        ) {
            readyForUpdate.value = true;
        }
    }
}

// Create a new test
async function storeTest() {
    if (!metaData.value.question.trim()) {
        toast.error("Test question is required");
        return;
    }
    if (
        !metaData.value.choices.length ||
        metaData.value.choices.some((ch) => !ch.trim())
    ) {
        toast.error("All choices must have a value");
        return;
    }
    const payload = {
        question: metaData.value.question,
        choices: metaData.value.choices,
        answer: [metaData.value.answer],
    };
    try {
        const response = await axios.post("/api/tests", payload);
        tests.value.push(response.data.data);
        selectedTest.value = response.data.data;
        isNewTestMode.value = false;
        openCollapse.value = true;
        toast.success("Test created successfully!");
    } catch (error) {
        toast.error("Failed to create test");
    }
}

// Update an existing test
async function updateTest() {
    if (!readyForUpdate.value) return;
    if (!metaData.value.question.trim()) {
        toast.error("Test question is required");
        return;
    }
    if (
        !metaData.value.choices.length ||
        metaData.value.choices.some((ch) => !ch.trim())
    ) {
        toast.error("All choices must have a value");
        return;
    }
    const payload = {
        question: metaData.value.question,
        choices: metaData.value.choices,
        answer: [metaData.value.answer],
    };
    try {
        const response = await axios.put(
            `/api/tests/${selectedTest.value.id}`,
            payload
        );
        tests.value = tests.value.map((t) =>
            t.id === response.data.data.id ? response.data.data : t
        );
        selectedTest.value = response.data.data;
        readyForUpdate.value = false;
        toast.success("Test updated successfully!");
    } catch (error) {
        toast.error("Failed to update test");
    }
}

// Toggle collapse of meta data form
function toggleCollapse() {
    openCollapse.value = !openCollapse.value;
}

// Change page for pagination
function changePage(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

// Open delete confirmation modal
function openDeleteModal(test) {
    testToDelete.value = test.id;
    showDeleteModal.value = true;
}

// Delete test
async function deleteTest() {
    try {
        await axios.delete(`/api/tests/${testToDelete.value}`);
        tests.value = tests.value.filter((t) => t.id !== testToDelete.value);
        toast.success("Test deleted successfully!");
        showDeleteModal.value = false;
        if (
            selectedTest.value &&
            selectedTest.value.id === testToDelete.value
        ) {
            selectedTest.value = null;
        }
    } catch (error) {
        toast.error("Error deleting test.");
    }
}

// Fetch tests from the API
async function fetchTests() {
    loading.value = true;
    try {
        const response = await axios.get("/api/tests");
        tests.value = response.data.data.map((test) => ({
            ...test,
            answer: Array.isArray(test.answer) ? test.answer : [test.answer],
        }));
    } catch (error) {
        toast.error("Error fetching tests.");
    } finally {
        loading.value = false;
    }
}

// On component mount, fetch tests and set initial new test mode
onMounted(() => {
    fetchTests();
    toggleNewTest();
});
</script>

<style scoped>
textarea {
    min-height: 100px;
}
</style>
