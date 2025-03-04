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
                <p class="text-sm text-gray-500">Manage Tests</p>
            </div>
        </header>

        <!-- Tab Navigation -->
        <div class="my-6">
            <div class="flex border-b">
                <button
                    :class="tabClass('manageTest')"
                    @click="currentPage = 'manageTest'"
                    class="p-2"
                >
                    Manage Test
                </button>
                <button
                    :class="tabClass('metaDataForm')"
                    @click="currentPage = 'metaDataForm'"
                    class="p-2"
                >
                    Meta Data Form
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-6 py-6">
            <!-- ManageTest Page -->
            <div v-if="currentPage === 'manageTest'">
                <ManageTest />
            </div>

            <!-- MetaDataForm Page -->
            <div v-if="currentPage === 'metaDataForm'">
                <MetaDataForm />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import ManageTest from "../Course/ManageTest.vue";
import MetaDataForm from "@/components/Quize/MetaDataForm.vue";

// Active page for tab navigation
const currentPage = ref("manageTest");

// Function to return dynamic classes for tabs
const tabClass = (tab) => {
    return currentPage.value === tab
        ? "border-b-2 border-lime-700 text-lime-700"
        : "text-gray-700";
};
</script>
