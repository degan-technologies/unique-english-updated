<template>
    <div class="min-h-screen bg-gray-100 text-gray-800">
        <!-- Header -->
        <header
            class="bg-white shadow-md px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="text-3xl font-bold text-lime-700">
                    Exam Management
                </h1>
                <p class="text-sm text-gray-500">Manage Quizzes and Tests</p>
            </div>
        </header>

        <!-- Tab Navigation -->
        <div class="my-6">
            <div class="flex border-b">
                <button
                    :class="tabClass('addQuiz')"
                    @click="currentPage = 'addQuiz'"
                    class="p-2"
                >
                    Add Quiz
                </button>
                <button
                    :class="tabClass('manageQuiz')"
                    @click="currentPage = 'manageQuiz'"
                    class="p-2"
                >
                    Manage Quizzes
                </button>
                <button
                    :class="tabClass('addTest')"
                    @click="currentPage = 'addTest'"
                    class="p-2"
                >
                    Add Test
                </button>
                <button
                    :class="tabClass('manageTest')"
                    @click="currentPage = 'manageTest'"
                    class="p-2"
                >
                    Manage Tests
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-6 py-6 min-h-screen">
            <!-- Add Quiz Page -->
            <div
                v-if="currentPage === 'addQuiz'"
                class="p-3 max-w-2xl mx-auto bg-white rounded-lg shadow-lg"
            >
                <AddQuiz @quiz-added="handleQuizAdded" />
                <button
                    @click="currentPage = 'manageQuiz'"
                    class="bg-gray-300 px-4 py-2 rounded mt-3"
                >
                    Back to List
                </button>
            </div>

            <!-- Manage Quizzes Page -->
            <div v-if="currentPage === 'manageQuiz'">
                <ManageQuiz />
            </div>

            <!-- Add Test Page -->
            <MetaDataForm v-if="currentPage === 'addTest'" />
            <!-- <div
                v-if="currentPage === 'addTest'"
                class="p-6 max-w-2xl mx-auto bg-white rounded-lg shadow-lg"
            >
                <AddTest @test-added="handleTestAdded" />
                
                <button
                    @click="currentPage = 'manageTest'"
                    class="bg-gray-300 px-4 py-2 rounded mt-3"
                >
                    Back to List
                </button>
            </div> -->

            <!-- Manage Tests Page -->
            <div v-if="currentPage === 'manageTest'">
                <ManageTest />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import AddQuiz from "../Course/AddQuiz.vue";
import ManageQuiz from "../Course/ManageQuiz.vue";
import AddTest from "../Course/AddTest.vue";
import MetaDataForm from "@/components/Quize/MetaDataForm.vue";
import ManageTest from "../Course/ManageTest.vue";

// Active page for tab navigation
const currentPage = ref("addTest");

// Function to return dynamic classes for tabs
const tabClass = (tab) => {
    return currentPage.value === tab
        ? "border-b-2 border-lime-700 text-lime-700"
        : "text-gray-700";
};

// When a quiz is added, switch to Manage Quizzes view.
const handleQuizAdded = (newQuiz) => {
    currentPage.value = "manageQuiz";
};

// When a test is added, switch to Manage Tests view.
const handleTestAdded = (newTest) => {
    currentPage.value = "manageTest";
};
</script>

<style scoped>
/* Additional styles if needed */
</style>
