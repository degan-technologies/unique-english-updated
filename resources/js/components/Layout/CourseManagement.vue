<script setup>
import Axios from 'axios';
import { storeToRefs } from 'pinia';
import { useRoute, useRouter } from "vue-router";
import { ref, computed, onMounted, watch } from 'vue';

import { useAppStore } from '@/store/useAppStore';
import { useInstructorStore } from '@/store/useInstructorStore';

import AddBook from '@/components/Book/AddBook.vue';
import courses from '@/components/Course/courses.vue';
import AddCourse from '@/components/Course/AddCourse.vue';
import bookmanagment from '@/components/Book/bookmanagment.vue';
import CourseModule from "@/components/Course/CourseModule/CourseModule.vue";

const route = useRoute();
const router = useRouter();

const appStore = useAppStore();
const InstructorStore = useInstructorStore();
const { analytics, selectedCourse, courseEditTab, courseModuleTab } = storeToRefs(InstructorStore);
const { authUser, frontLang } = storeToRefs(appStore);

const bookTab = ref('Books');
const courseTab = ref('Courses');
const addcourseTab = ref('addcourse');
const addbookTab = ref('addbook');

const activeTab = ref(courseTab.value);

const searchQuery = ref('');
const searchItem = ref(courseTab.value);

const selectedAction = ref(route.query.selectedAction);

computed(() => {
    activeTab.value = route?.query?.currentActiveTab ? route.query.currentActiveTab : courseTab.value;
});

watch(
    () => route?.query,
    () => {
        activeTab.value = route?.query?.currentActiveTab ? route.query.currentActiveTab : courseTab.value;
        selectedAction.value = route?.query?.selectedAction;
    },
    { immediate: true }
);

onMounted(() => {
    if (!route.query.currentActiveTab) {
        activeTab.value = courseTab.value;
    }
});

function selectedTab(tab) {
    if (tab === courseTab.value || tab === addcourseTab.value) {
        searchItem.value = courseTab.value;
    } else {
        searchItem.value = bookTab.value;
    }

    router.push({
        name: 'instructor',
        query: {
            currentTab: route.query.currentTab,
            currentActiveTab: tab,
        }
    });

    activeTab.value = tab;
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 text-gray-800">
        <!-- Header -->
        <header class="bg-white shadow px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-lime-700">Book Management</h1>
                <p class="text-sm text-gray-500">Manage Courses &amp; Books</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center">
                <nav class="text-sm"
                    aria-label="Breadcrumb">
                    <ol class="list-reset flex text-gray-600">
                        <li>
                            <a href="#"
                                class="hover:underline">Home</a>
                        </li>
                        <li>
                            <span class="mx-2">/</span>
                        </li>
                        <li class="font-medium">{{ activeTab }}</li>
                    </ol>
                </nav>
                <div class="ml-6 relative">
                    <input type="text"
                        v-model="searchQuery"
                        :placeholder="`Search  ${searchItem}...`"
                        class="border border-gray-300 rounded-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-500"
                            fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M12.9 14.32a8 8 0 111.414-1.414l4.243 4.243a1 1 0 01-1.414 1.414l-4.243-4.243zM8 14a6 6 0 100-12 6 6 0 000 12z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>
            </div>
        </header>
        <div class="container mx-auto px-6 py-6">
            <div>
                <div class="mb-6 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8"
                        aria-label="Tabs">
                        <button @click="selectedTab(courseTab)"
                            :class="{
                                'border-lime-700 text-lime-700': activeTab === courseTab,
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== courseTab,
                            }"
                            class=" 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none'">
                            Courses
                        </button>
                        <button @click="selectedTab(bookTab)"
                            :class="{
                                'border-lime-700 text-lime-700': activeTab === bookTab,
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== bookTab,
                            }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                            Books
                        </button>
                    </nav>
                </div>
                <div v-if="!selectedCourse"
                    class="max-w-7xl mx-auto mt-4">
                    <!-- Header: Analytics Dashboard and Add Buttons in one row -->
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                        <!-- Analytics Dashboard + Data -->
                        <aside
                            class="flex flex-col sm:flex-row items-center gap-4 bg-white shadow rounded-lg p-3 w-full md:w-2/3">
                            <h3 class="text-lg font-semibold text-lime-700 whitespace-nowrap">
                                Analytics Dashboard
                            </h3>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center bg-lime-50 px-3 py-2 rounded">
                                    <p class="text-xs font-medium text-gray-600 mr-1">Total {{activeTab}}</p>
                                    <p class="text-lg font-bold text-lime-700">{{ analytics.total }}</p>
                                </div>
                                <div class="flex items-center bg-green-50 px-3 py-2 rounded">
                                    <p class="text-xs font-medium text-gray-600 mr-1">New Today</p>
                                    <p class="text-lg font-bold">{{ analytics.newToday }}</p>
                                </div>
                            </div>
                        </aside>

                        <!-- Add Buttons -->
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <button v-if="activeTab === courseTab"
                                @click="selectedTab(addcourseTab)"
                                class="bg-lime-700 hover:bg-lime-800 text-white px-3 py-2 rounded transition shadow text-sm">
                                Add Course
                            </button>
                            <button v-if="activeTab === bookTab"
                                @click="selectedTab(addbookTab)"
                                class="bg-lime-700 hover:bg-lime-800 text-white px-3 py-2 rounded transition shadow text-sm">
                                Add Book
                            </button>
                        </div>
                    </div>

                    <!-- Main Content: Courses/Books/Forms Expand Fully -->
                    <div class="rounded-lg w-full max-w-full">
                        <div class="overflow-x-auto w-full">
                            <div v-if="activeTab === courseTab"
                                class="w-full">
                                <courses :searchQuery="searchQuery" />
                            </div>
                            <div v-else-if="activeTab === bookTab"
                                class="w-full">
                                <bookmanagment :searchQuery="searchQuery" />
                            </div>
                            <div v-if="activeTab === addcourseTab"
                                class="space-y-4 p-4 w-full">
                                <AddCourse />
                            </div>
                            <div v-else-if="activeTab === addbookTab"
                                class="space-y-4 p-4 w-full">
                                <AddBook />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="selectedCourse">
                    <div v-if="selectedAction === courseModuleTab"
                        class="bg-white p-6">
                        <div>
                            <CourseModule />
                        </div>
                    </div>
                    <div v-if="selectedAction === courseEditTab"
                        class="w-full h-full bg-white p-6">
                        <div class="relative w-full  mx-auto  p-6 gap-6">
                            <AddCourse />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
