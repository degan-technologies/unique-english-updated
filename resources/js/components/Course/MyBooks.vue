
<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { fullUrl } from "../../utils/urlHelper.js";

import Book from "../Book/Book.vue"

const transactions = ref([]);

// Retrieve the token from localStorage
const storedToken = localStorage.getItem("token") || "";
console.log("Token from LS:", storedToken);

const cleanedToken = storedToken.replace(/^Bearer\s+/i, "");
console.log("Cleaned token:", cleanedToken);

if (cleanedToken) {
  axios.defaults.headers.common["Authorization"] = `Bearer ${cleanedToken}`;
} else {
  console.warn("No token found in localStorage");
}

const fetchTransactions = async () => {
  try {
    const response = await axios.get("/api/courses/transactions/book");
    transactions.value = response.data;
    console.log("Fetched transactions:", transactions.value);
  } catch (error) {
    console.error("Error fetching transactions:", error.response?.data || error);
  }
};

onMounted(fetchTransactions);
</script>

<template>
  <div>
    <div class="min-h-screen overflow-y-auto">
    <!-- Header
    <header class="fixed top-0 left-0 w-full bg-lime-700 text-white p-4 z-10">
      <h1 class="text-xl">My Courses</h1>
    </header> -->
   
    <!-- Main Content -->
    <main class="pt-10">
      <div class="container mx-auto p-8 max-w-6xl">
        <div v-if="transactions.length === 0" class="text-center text-gray-500">
          No transactions found.
        </div>
        <div v-else class="flex flex-col md:flex-row gap-4">
          <div class="md:w-2/3 space-y-6">
            <div
              v-for="transaction in transactions"
              :key="transaction.id"
              class="bg-white border border-gray-300 shadow-lg rounded-lg h-56 overflow-hidden p-4"
            >
              <div class="flex flex-col md:flex-row">
                <div class="md:w-1/2">
                  <video
                    class="w-full h-48 object-cover rounded-lg"
                    controls
                    :poster="fullUrl(transaction.book?.cover_page_url, '/images/course-thumbnail.jpg')"
                  >
                    <source
                      :src="fullUrl(transaction.book?.intro_vedio, 'https://www.w3schools.com/html/mov_bbb.mp4')"
                      type="video/mp4"
                    />
                    Your browser does not support the video tag.
                  </video>
                </div>
                <div class="md:w-1/2 p-4 flex flex-col justify-center">
                  <h2 class="text-xl font-semibold pb-2">
                    {{ transaction.book?.title || "Book Name" }}
                  </h2>
                  <div class="text-gray-600 text-sm">
                    <p class="line-clamp">
                      {{ transaction.book?.description || "Book overview goes here." }}
                    </p>
                  </div>

                  <!-- Progress Bar -->
                  <div class="flex items-center gap-2 mt-3">
  <div class="w-full bg-gray-200 rounded-full h-2.5">
    <div class="bg-green-500 h-2.5 rounded-full" :style="{ width: '30%' }"></div>
  </div>
  <span class="text-sm font-medium text-gray-700">30%</span>
</div>


                  <!-- Continue Button -->
                  <button class="mt-9 px-3 py-2 w-28 border border-lime-700 bg-white text-lime-600  font-semibold text-sm rounded-md hover:bg-lime-700">
                     Continue
                  </button>

                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Course Outline Card (Fixed Height) -->
          <div class="md:w-1/3 bg-gray-50 border border-gray-300 shadow-lg rounded-lg p-4 h-[400px] self-start overflow-y-auto">
            <h3 class="text-xl font-semibold mb-4">Course Outline</h3>
            <ul class="list-disc list-inside text-gray-700">
              <li class="mb-2">Introduction & Setup</li>
              <li class="mb-2">Understanding Vue 3 Fundamentals</li>
              <li class="mb-2">Building Components</li>
              <li class="mb-2">State Management with Vuex</li>
              <li class="mb-2">Routing and Navigation</li>
              <li class="mb-2">Advanced Patterns</li>
              <li class="mb-2">Deployment & Optimization</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="mt-8">
         <Book/>
      </div>
    </main>
    </div>
  </div>
</template>

<style>
html,
body {
  overflow-y: auto;
  height: auto;
}
.line-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 2; /* Restrict to 2 lines */
  line-clamp: 2; /* Restrict to 2 lines */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
