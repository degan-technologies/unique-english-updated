<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { useRoute, useRouter } from "vue-router";
import { ref, computed, onMounted, watch } from "vue";

import { useAppStore } from "@/store/useAppStore";
import { useInstructorStore } from "@/store/useInstructorStore";

import AddBook from "@/components/Book/AddBook.vue";
import courses from "@/components/Course/courses.vue";
import AddCourse from "@/components/Course/AddCourse.vue";
import bookmanagment from "@/components/Book/bookmanagment.vue";
import CourseModule from "@/components/Course/CourseModule/CourseModule.vue";

const route = useRoute();
const router = useRouter();

const appStore = useAppStore();
const InstructorStore = useInstructorStore();
const { analytics, selectedCourse, courseEditTab, courseModuleTab } =
    storeToRefs(InstructorStore);
const { authUser, frontLang } = storeToRefs(appStore);

const bookTab = ref("Books");
const courseTab = ref("Courses");
const addcourseTab = ref("addcourse");
const addbookTab = ref("addbook");

const activeTab = ref(courseTab.value);

const searchQuery = ref("");
const searchItem = ref(courseTab.value);

const selectedAction = ref(route.query.selectedAction);

computed(() => {
    activeTab.value = route?.query?.currentActiveTab
        ? route.query.currentActiveTab
        : courseTab.value;
});

watch(
    () => route?.query,
    () => {
        activeTab.value = route?.query?.currentActiveTab
            ? route.query.currentActiveTab
            : courseTab.value;
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
        name: "instructor",
        query: {
            currentTab: route.query.currentTab,
            currentActiveTab: tab,
        },
    });

    activeTab.value = tab;
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 text-gray-800">
        <!-- Header -->

        <header
            class="bg-white shadow px-6 py-4 flex-col flex lg:flex-row iteems-left lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-lime-700">
                    Book Management
                </h1>
                <p class="text-xs sm:text-sm text-gray-500">
                    Manage Courses &amp; Books
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center">
                <nav class="text-sm" aria-label="Breadcrumb">
                    <ol class="list-reset flex text-gray-600">
                        <li>
                            <a href="#" class="hover:underline">Home</a>
                        </li>
                        <li>
                            <span class="mx-2">/</span>
                        </li>
                        <li class="font-medium">{{ activeTab }}</li>
                    </ol>
                </nav>

                <div class="ml-6 relative">
                    <input type="text" v-model="searchQuery" :placeholder="`Search  ${searchItem}...`"
                        class="border border-gray-300 rounded-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M12.9 14.32a8 8 0 111.414-1.414l4.243 4.243a1 1 0 01-1.414 1.414l-4.243-4.243zM8 14a6 6 0 100-12 6 6 0 000 12z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </span>
                </div>
            </div>
            <!-- Add Buttons -->
            <div class="w-fit">
                <button v-if="activeTab === courseTab" @click="selectedTab(addcourseTab)"
                    class="bg-lime-700 hover:bg-lime-800 text-white px-3 py-1 w-fit rounded transition shadow text-sm">
                    Add Course
                </button>
                <button v-if="activeTab === bookTab" @click="selectedTab(addbookTab)"
                    class="bg-lime-700 hover:bg-lime-800 text-white px-3 py-1 w-fit rounded transition shadow text-sm">
                    Add Book
                </button>
            </div>
        </header>
        <div class="w-full mx-auto">
            <div>
                <div class="mb-6 border-b border-gray-200">
                    <nav class="flex" aria-label="Tabs">
                        <button @click="selectedTab(courseTab)" :class="{
                                'border-lime-700 text-lime-700': activeTab === courseTab,
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== courseTab,
                            }"
                            class="whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm focus:outline-none"
                        >
                            Courses
                        </button>
                        <button @click="selectedTab(bookTab)" :class="{
                                'border-lime-700 text-lime-700': activeTab === bookTab,
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== bookTab,
                            }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                            Books
                        </button>
                    </nav>
                </div>
                <div class="w-full mx-auto">
                    <!-- Main Content: Courses/Books/Forms Expand Fully -->
                    <div class="rounded-lg w-full">
                        <div class="w-full"> 
                            <div v-if="activeTab === courseTab" class="w-full"> 
                                <courses 
                                    v-if="activeTab === courseTab"
                                    :searchQuery="searchQuery"/>
                            </div>
                            <div v-else-if="activeTab === bookTab" class="w-full">
                                <bookmanagment 
                                    v-if="activeTab === bookTab"
                                    :searchQuery="searchQuery" />
                            </div>
                            <div v-else-if="activeTab === addcourseTab" class="w-full">
                                <AddCourse 
                                v-if="activeTab === addcourseTab" 
                                    :editCourse="false"/>
                            </div>
                            <div v-else-if="activeTab === addbookTab" class="w-full">
                                <AddBook v-if="activeTab === addbookTab" />
                            </div>
                            <div v-else-if="activeTab === courseModuleTab" class="bg-white">
                                <CourseModule />
                            </div>
                            <div v-else-if="activeTab === courseEditTab" class="bg-white">
                                <AddCourse 
                                    :editCourse="true"/>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</template>
