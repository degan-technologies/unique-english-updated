<script setup>
import Axios from "axios";
import { useToast } from "vue-toastification";
import { ref, computed, onMounted } from "vue";

const toast = useToast();

// State
const tests = ref([]);
const loading = ref(true);
const selectedTest = ref(null);
const isNewTestMode = ref(false);
const showTestForm = ref(true);
const hasChanges = ref(false);

// Form data
const testForm = ref({
    question: "",
    choices: ["", "", "", ""],
    answer: null
});

// Delete confirmation
const deleteDialog = ref({
    show: false,
    testId: null
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = 5;
const paginatedTests = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return tests.value.slice(start, start + itemsPerPage);
});
const totalPages = computed(() => Math.ceil(tests.value.length / itemsPerPage));

// Initialize new test
function initNewTest() {
    isNewTestMode.value = true;
    selectedTest.value = null;
    showTestForm.value = true;
    testForm.value = { question: "", choices: ["", "", "", ""], answer: null };
    hasChanges.value = false;
}

// Select a test to view/edit
function selectTest(test) {
    isNewTestMode.value = false;
    if (selectedTest.value?.id === test.id) {
        selectedTest.value = null;
    } else {
        selectedTest.value = test;
        testForm.value = {
            question: test.question,
            choices: test.choices ? [...test.choices] : ["", "", "", ""],
            answer: test.answer?.[0] || null
        };
        showTestForm.value = true;
        hasChanges.value = false;
    }
}

// Check for changes in form
function checkForChanges() {
    if (!selectedTest.value) return;

    hasChanges.value =
        testForm.value.question !== selectedTest.value.question ||
        JSON.stringify(testForm.value.choices) !== JSON.stringify(selectedTest.value.choices) ||
        testForm.value.answer !== selectedTest.value.answer?.[0];
}

// Add new choice
function addChoice() {
    testForm.value.choices.push("");
    checkForChanges();
}

// Remove choice
function removeChoice(index) {
    testForm.value.choices.splice(index, 1);
    if (testForm.value.choices.length === 0) {
        testForm.value.choices.push("");
    }
    if (testForm.value.answer === testForm.value.choices[index]) {
        testForm.value.answer = null;
    }
    checkForChanges();
}

// Create new test
async function createTest() {
    if (!validateForm()) return;

    try {
        const payload = {
            question: testForm.value.question,
            choices: testForm.value.choices.filter(c => c.trim()),
            answer: [testForm.value.answer]
        };

        const res = await Axios.post("/api/tests", payload);
        tests.value.push(res.data.data);
        selectedTest.value = res.data.data;
        isNewTestMode.value = false;
        toast.success("Test created successfully!");
    } catch (error) {
        toast.error("Failed to create test");
    }
}

// Update existing test
async function updateTest() {
    if (!hasChanges.value) return;
    if (!validateForm()) return;

    try {
        const payload = {
            question: testForm.value.question,
            choices: testForm.value.choices.filter(c => c.trim()),
            answer: [testForm.value.answer]
        };

        const res = await Axios.put(`/api/tests/${selectedTest.value.id}`, payload);
        tests.value = tests.value.map(t =>
            t.id === res.data.data.id ? res.data.data : t
        );
        selectedTest.value = res.data.data;
        hasChanges.value = false;
        toast.success("Test updated successfully!");
    } catch (error) {
        toast.error("Failed to update test");
    }
}

// Delete test
async function deleteTest() {
    try {
        await Axios.delete(`/api/tests/${deleteDialog.value.testId}`);
        tests.value = tests.value.filter(t => t.id !== deleteDialog.value.testId);
        if (selectedTest.value?.id === deleteDialog.value.testId) {
            selectedTest.value = null;
        }
        toast.success("Test deleted successfully!");
    } catch (error) {
        toast.error("Failed to delete test");
    } finally {
        deleteDialog.value.show = false;
    }
}

// Form validation
function validateForm() {
    if (!testForm.value.question.trim()) {
        toast.error("Question is required");
        return false;
    }

    const validChoices = testForm.value.choices.filter(c => c.trim());
    if (validChoices.length < 2) {
        toast.error("At least 2 choices are required");
        return false;
    }

    if (!testForm.value.answer) {
        toast.error("Please select the correct answer");
        return false;
    }

    return true;
}

// Change page
function changePage(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

// Fetch tests
async function fetchTests() {
    loading.value = true;
    try {
        const res = await Axios.get("/api/tests");
        tests.value = res.data.data.map(t => ({
            ...t,
            answer: Array.isArray(t.answer) ? t.answer : [t.answer]
        }));
    } catch (error) {
        toast.error("Failed to load tests");
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchTests();
    initNewTest();
});
</script>

<template>
    <div class="min-h-screen overflow-hidden bg-gray-100">
        <header class="bg-white shadow p-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Test Management</h1> 
                 <nav class="text-gray-500 text-sm mb-4">
                    <ol class="list-reset flex">
                        <li>
                            <a href="#" class="hover:text-blue-500">Dashboard</a>
                        </li>
                        <li>
                            <span class="mx-2">/</span>
                        </li>
                        <li>Tests</li>
                    </ol>
                </nav>
            </div> 
        </header>

        <!-- Delete Confirmation Dialog -->
        <div v-if="deleteDialog.show" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-red-100 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                </div>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this test?</p>
                <div class="flex justify-end gap-3">
                    <button @click="deleteDialog.show = false"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                        Cancel
                    </button>
                    <button @click="deleteTest" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mb-6">
            <nav class="flex border-b">
                <button  
                    class="px-4 py-2 font-medium focus:outline-none border-lime-700 text-lime-700 border-b-2" >
                    Tests
                </button>    
            </nav>
        </div>
        <div class="flex flex-col md:flex-row gap-6 mt-6">
            <!-- Left Panel - Tests List -->
            <div class="md:w-1/3 bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Tests</h2>
                    <button @click="initNewTest" class="p-2 text-green-600 hover:bg-green-50 rounded-full">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <div class="space-y-2">
                    <div v-for="(test, index) in paginatedTests" :key="test.id" class="group">
                        <div @click="selectTest(test)"
                            class="flex items-center justify-between p-3 rounded-lg cursor-pointer transition-colors"
                            :class="{
                                'bg-green-50': selectedTest?.id === test.id,
                                'hover:bg-gray-50': selectedTest?.id !== test.id
                            }">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-lime-100 flex items-center justify-center text-lime-600">
                                    <i class="fas fa-question text-sm"></i>
                                </div>
                                <h3 class="font-medium text-gray-800 line-clamp-1">
                                    {{ (currentPage - 1) * itemsPerPage + index + 1 }}. {{ test.question.slice(0, 50)
                                    }}{{ test.question.length > 50 ? "..." : "" }}
                                </h3>
                            </div>
                            <button v-if="selectedTest?.id === test.id"
                                @click.stop="deleteDialog = { show: true, testId: test.id }"
                                class="text-red-500 hover:text-red-700 p-1">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex justify-center items-center gap-4 mt-4">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                        class="w-8 h-8 flex items-center justify-center rounded-full disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-100">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    <span class="text-sm text-gray-500">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                        class="w-8 h-8 flex items-center justify-center rounded-full disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-100">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Right Panel - Test Form -->
            <div class="md:w-2/3 bg-white rounded-lg border border-gray-200 p-6">
                <div v-if="showTestForm">
                    <!-- Form Header -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-semibold">
                            {{ isNewTestMode ? 'Create New Test' : 'Edit Test' }}
                        </h2>
                        <button v-if="!isNewTestMode" @click="showTestForm = false"
                            class="p-2 text-gray-500 hover:bg-gray-50 rounded-full">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Test Form -->
                    <form @submit.prevent="isNewTestMode ? createTest() : updateTest()" class="space-y-4">
                        <!-- Question -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Question *</label>
                            <textarea v-model="testForm.question" @input="checkForChanges"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                                placeholder="Enter your question" rows="3" required></textarea>
                        </div>

                        <!-- Choices -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Choices *</label>
                            <div v-for="(choice, index) in testForm.choices" :key="index"
                                class="flex items-center mb-2">
                                <input type="radio" :name="'answer'" class="mr-2" :value="choice"
                                    v-model="testForm.answer" @change="checkForChanges" :disabled="!choice.trim()" />
                                <input type="text" v-model="testForm.choices[index]" @input="checkForChanges"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                                    placeholder="Enter choice" required />
                                <button v-if="testForm.choices.length > 1" type="button" @click="removeChoice(index)"
                                    class="ml-2 text-red-500 hover:text-red-700 p-1">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                            <button type="button" @click="addChoice"
                                class="mt-2 px-3 py-1 text-sm text-lime-600 hover:bg-lime-50 rounded">
                                <i class="fas fa-plus mr-1"></i> Add Choice
                            </button>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button v-if="!isNewTestMode" @click="showTestForm = false" type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" :disabled="!hasChanges && !isNewTestMode"
                                class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ isNewTestMode ? 'Create Test' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Test Details View -->
                <div v-else class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold">{{ selectedTest.question }}</h2>
                        <button @click="showTestForm = true" class="p-2 text-lime-600 hover:bg-lime-50 rounded-full">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>

                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-700">Choices:</h3>
                        <ul class="space-y-2 pl-4">
                            <li v-for="(choice, index) in selectedTest.choices" :key="index" class="flex items-start">
                                <span class="mr-2 mt-1">
                                    <i v-if="selectedTest.answer?.includes(choice)"
                                        class="fas fa-check-circle text-green-500"></i>
                                    <i v-else class="fas fa-circle text-gray-300"></i>
                                </span>
                                <span :class="{ 'font-medium text-green-600': selectedTest.answer?.includes(choice) }">
                                    {{ choice }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>