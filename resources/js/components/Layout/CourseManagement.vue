<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import Axios from 'axios'; // <-- Added Axios import
import AddBook from './AddBook.vue';

import bookmanagment from './bookmanagment.vue';
import courses from '../../components/Course/courses.vue';
import AddCourse from '../../components/Course/AddCourse.vue';

import { storeToRefs } from 'pinia';
import { useAppStore } from '@/store/useAppStore';
const appStore = useAppStore();
const { authUser, frontLang } = storeToRefs(appStore);

const activeTab = ref('Courses');
const searchQuery = ref('');
const currentPage = ref('list'); 

const goToAddCourse = () => {
  currentPage.value = 'addcourse';
};
const goToAddBook = () => {
  currentPage.value = 'addbook';
};

const tabClass = (tabName) => {
  return [
    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none',
    activeTab.value === tabName
      ? 'border-lime-700 text-lime-700'
      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
  ];
};

// Dummy analytics data
const analytics = ref({
  total: 0,
  newToday: 0,
  pending: 0,
});

// Dummy book list (replace with your API call if needed)
const books = ref([
  {
    id: 1,
    type: 'Courses',
    title: 'Vue.js Mastery',
    description: 'Learn Vue.js from scratch with practical projects.',
    status: 'Pending',
    thumbnail: '/images/course-1.jpg',
  },
  {
    id: 2,
    type: 'Courses',
    title: 'Advanced JavaScript',
    description: 'Deep dive into advanced JavaScript concepts.',
    status: 'Approved',
    thumbnail: '/images/course-2.jpg',
  },
  {
    id: 3,
    type: 'Books',
    title: 'Tailwind CSS Guide',
    description: 'A complete guide to mastering Tailwind CSS.',
    status: 'Approved',
    thumbnail: '/images/book-4.jpg',
  },
  {
    id: 4,
    type: 'Books',
    title: 'Learning D3.js',
    description: 'Visualize data with interactive charts using D3.js.',
    status: 'Pending',
    thumbnail: '/images/book-2.jpeg',
  },
]);

// Helper function to check if a date string represents today's date
const isToday = (dateStr) => {
  const date = new Date(dateStr);
  const today = new Date();
  return (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  );
};

const updateAnalytics = async () => {
  if (activeTab.value === 'Books') {
    try {
      const response = await Axios.get("/api/books/books", {
        headers: { "Content-Type": "application/json" },
      });
      const booksData = response.data.data;
      analytics.value.total = booksData.length;
      // For Books, using pending status as "new today"
      analytics.value.newToday = booksData.filter(
        (book) => book.status === 'Pending'
      ).length;
      analytics.value.pending = booksData.filter(
        (book) => book.status === 'Pending'
      ).length;
    } catch (error) {
      console.error("Error fetching books analytics:", error);
    }
  } else if (activeTab.value === 'Courses') {
    try {
      const response = await Axios.get("/api/courses/course", {
        headers: { "Content-Type": "application/json" },
      });
      const coursesData = response.data.data;
      analytics.value.total = coursesData.length;
      // Count courses created today by checking the created_at date
      analytics.value.newToday = coursesData.filter(
        (course) => isToday(course.created_at)
      ).length;
      analytics.value.pending = coursesData.filter(
        (course) => course.status === 'Pending'
      ).length;
    } catch (error) {
      console.error("Error fetching courses analytics:", error);
    }
  } else {
    const currentItems = books.value.filter(
      (book) => book.type === activeTab.value
    );
    analytics.value.total = currentItems.length;
    analytics.value.newToday = currentItems.filter(
      (book) => book.status === 'Pending'
    ).length;
    analytics.value.pending = currentItems.filter(
      (book) => book.status === 'Pending'
    ).length;
  }
};


onMounted(() => {
  updateAnalytics();
});

// Update analytics when activeTab or books changes.
watch([activeTab, books], () => {
  updateAnalytics();
});
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
          <input
            type="text"
            :placeholder="`Search ${activeTab}...`"
            class="border border-gray-300 rounded-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-lime-700"
            v-model="searchQuery"
          />
          <span class="absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M12.9 14.32a8 8 0 111.414-1.414l4.243 4.243a1 1 0 01-1.414 1.414l-4.243-4.243zM8 14a6 6 0 100-12 6 6 0 000 12z"
                clip-rule="evenodd"
              />
            </svg>
          </span>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <div class="container mx-auto px-6 py-6">
      <div v-if="currentPage === 'list'">
        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-gray-200">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="activeTab = 'Courses'" :class="tabClass('Courses')">
              Courses
            </button>
            <button @click="activeTab = 'Books'" :class="tabClass('Books')">
              Books
            </button>
          </nav>
        </div>

        <!-- Add Buttons -->
        <div class="mb-6 flex justify-end space-x-4">
          <button @click="goToAddCourse" class="bg-lime-700 hover:bg-lime-800 text-white px-4 py-2 rounded transition">
            Add Course
          </button>
          <button @click="goToAddBook" class="bg-lime-700 hover:bg-lime-800 text-white px-4 py-2 rounded transition">
            Add Book
          </button>
        </div>

        <!-- List / Grid View -->
        <div class="grid mt-4 grid-cols-1 lg:grid-cols-4 gap-6">
          <!-- Main Content Column -->
          <div class="lg:col-span-3">
            <!-- Courses Tab: Display filtered courses based on search -->
            <div v-if="activeTab === 'Courses'">
              <courses :searchQuery="searchQuery"/>
             

            </div>
            <!-- Books Tab: Render the bookmanagment component and pass the search query -->
            <div v-else-if="activeTab === 'Books'">
              <h1 class="text-xl font-bold text-center mb-4">Books Content</h1>
              <bookmanagment :searchQuery="searchQuery" />
            </div>
          </div>

          <!-- Right: Analytics Dashboard -->
          <aside class="bg-white shadow rounded-lg p-4 lg:col-span-1">
            <h3 class="text-xl font-semibold mb-4">Analytics Dashboard</h3>
            <div class="space-y-4">
              <div class="grid grid-cols-3 gap-4">
                <div class="p-4 bg-lime-50 rounded text-center">
                  <p class="text-sm">Total {{ activeTab }}</p>
                  <p class="text-2xl font-bold text-lime-700">{{ analytics.total }}</p>
                </div>
                <div class="p-4 bg-green-50 rounded text-center">
                  <p class="text-sm">New Today</p>
                  <p class="text-2xl font-bold">{{ analytics.newToday }}</p>
                </div>
              </div>
              <div class="bg-gray-100 p-4 rounded">
                <canvas id="analyticsChart" class="w-full h-48"></canvas>
              </div>
            </div>
          </aside>
        </div>
      </div>

      <!-- Add Course Page -->
      <div v-else-if="currentPage === 'addcourse'" class="space-y-4">
     
        
        <button @click="currentPage = 'list'" class="bg-lime-100 hover:bg-lime-400 text-gray-700 px-4 py-2 rounded transition">
          Back to List
        </button>
        <AddCourse />
      </div>

      <!-- Add Book Page -->
      <div v-else-if="currentPage === 'addbook'" class="space-y-4">
       
        <button @click="currentPage = 'list'" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded transition">
          Back to List
        </button>
        <AddBook />
      </div>
    </div>
  </div>
</template>
