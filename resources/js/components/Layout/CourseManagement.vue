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
                    class="grid mt-4 grid-cols-1 lg:grid-cols-4 gap-6">
                    <div class="lg:col-span-3">
                        <div v-if="activeTab === courseTab">
                            <courses :searchQuery="searchQuery" />
                        </div>
                        <div v-else-if="activeTab === bookTab">
                            <bookmanagment :searchQuery="searchQuery" />
                        </div>
                        <!-- Add Course Page -->
                        <div v-if="activeTab === addcourseTab"
                            class="space-y-4">
                            <AddCourse />
                        </div>

                        <!-- Add Book Page -->
                        <div v-else-if="activeTab === addbookTab"
                            class="space-y-4">
                            <AddBook />
                        </div>
                    </div>
                    <div>
                        <div class="mb-6 flex justify-center space-x-4">
                            <button @click="selectedTab(addcourseTab)"
                                class="bg-lime-700 hover:bg-lime-800 text-white px-4 py-2 rounded transition">
                                Add Course
                            </button>
                            <button @click="selectedTab(addbookTab)"
                                class="bg-lime-700 hover:bg-lime-800 text-white px-4 py-2 rounded transition">
                                Add Book
                            </button>
                        </div>
                        <aside v-if="activeTab !== addcourseTab"
                            class="bg-white shadow rounded-lg p-4 lg:col-span-1 h-fit">
                            <h3 class="text-xl font-semibold text-center mb-4">Analytics Dashboard</h3>
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-lime-50 rounded text-center">
                                        <p class="text-sm">Total {{ searchItem }}</p>
                                        <p class="text-2xl font-bold text-lime-700">{{ analytics.total }}</p>
                                    </div>
                                    <div class="p-4 bg-green-50 rounded text-center">
                                        <p class="text-sm">New Today</p>
                                        <p class="text-2xl font-bold">{{ analytics.newToday }}</p>
                                    </div>
                                </div>
                                <div class="bg-gray-100 p-4 rounded">
                                    <canvas id="analyticsChart"
                                        class="w-full h-48"></canvas>
                                </div>
                            </div>
                        </aside>
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
