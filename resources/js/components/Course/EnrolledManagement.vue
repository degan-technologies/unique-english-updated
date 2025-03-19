<script setup>
import { ref } from "vue";
import MyCourses from "./MyCourse.vue";
import MyBooks from "./MyBooks.vue";
import LiveSessions from "./MyLiveSessions.vue";
import Header from "../Layout/Header.vue"
const activeTab = ref("courses");

const setActiveTab = (tab) => {
  activeTab.value = tab;
};
</script>

<template>
<Header />
  <div class="min-h-screen ml-10 mt-24 p-6">
 
    <div class="flex space-x-4 border-b border-gray-300">
      <button
        @click="setActiveTab('courses')"
        :class="['px-4 py-2', activeTab === 'courses' ? 'border-b-2 border-lime-600 font-semibold' : 'text-gray-500']"
      >
        My Courses
      </button>
      <button
        @click="setActiveTab('books')"
        :class="['px-4 py-2', activeTab === 'books' ? 'border-b-2 border-lime-600 font-semibold' : 'text-gray-500']"
      >
        My Books
      </button>
      <button
        @click="setActiveTab('live')"
        :class="['px-4 py-2', activeTab === 'live' ? 'border-b-2 border-lime-600 font-semibold' : 'text-gray-500']"
      >
        Live Sessions
      </button>
    </div>

    <!-- Content Sections -->
    <div class="mt-6">
      <MyCourses v-if="activeTab === 'courses'" />
      <MyBooks v-if="activeTab === 'books'" />
      <LiveSessions v-if="activeTab === 'live'" />
    </div>
  </div>
</template>
