<template>
  <div class="bg-white rounded-lg shadow p-4 w-full mx-auto">
    <!-- Header with Icon -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-2">
        <i class="fas fa-video text-lime-700 text-xl"></i>
        <h3 class="text-lg font-bold">Live Sessions Overview</h3>
      </div>
      <button @click="fetchSessions" class="text-gray-500 hover:text-gray-800">
        <i class="fas fa-sync-alt"></i>
      </button>
    </div>

    <!-- Live Sessions List -->
    <div v-if="sessions.length">
      <div
        v-for="session in sessions"
        :key="session.id"
        @click="openSessionDetails(session)"
        class="p-4 rounded-lg border border-gray-200 mb-3 cursor-pointer transition hover:bg-gray-100"
      >
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-3">
            <!-- Live Indicator -->
            <span v-if="session.isLive" class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
            <h4 class="text-md font-semibold">{{ session.title }}</h4>
          </div>
          <span class="text-sm text-gray-500">{{ formatTime(session.startTime) }}</span>
        </div>

        <!-- Instructor & Participants -->
        <div class="text-sm text-gray-600 mt-1">
          Instructor: <span class="font-medium">{{ session.instructor }}</span>
        </div>
        <div class="text-xs text-gray-500">
          <i class="fas fa-users"></i> {{ session.participants }} Participants
        </div>
      </div>
    </div>

    <!-- No Sessions Placeholder -->
    <div v-else class="text-gray-500 text-center py-6">No live sessions available.</div>

    <!-- Modal for Session Details -->
    <Teleport to="body">
      <div
        v-if="selectedSession"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center px-4"
      >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
          <div class="flex justify-between items-center mb-3">
            <h4 class="text-lg font-semibold">{{ selectedSession.title }}</h4>
            <button @click="selectedSession = null" class="text-gray-500 hover:text-gray-800">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <p><strong>Instructor:</strong> {{ selectedSession.instructor }}</p>
          <p><strong>Start Time:</strong> {{ formatTime(selectedSession.startTime) }}</p>
          <p><strong>Participants:</strong> {{ selectedSession.participants }}</p>
          <div class="flex justify-end mt-4 space-x-2">
            <button class="px-3 py-1 text-sm bg-lime-700 text-white rounded-md hover:bg-lime-800">
              Join Session
            </button>
            <button
              class="px-3 py-1 text-sm bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400"
              @click="selectedSession = null"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Sample Data (Replace with API Call)
const sessions = ref([
  { id: 1, title: "Advanced Vue.js", instructor: "John Doe", startTime: "2024-02-01T14:30:00Z", participants: 42, isLive: true },
  { id: 2, title: "Data Science with Python", instructor: "Jane Smith", startTime: "2024-02-01T16:00:00Z", participants: 35, isLive: false },
]);

const selectedSession = ref(null);

// Fetch Sessions (Simulated API Call)
const fetchSessions = () => {
  // Simulate a live session update (Replace with API call)
  sessions.value = [
    { id: 1, title: "Advanced Vue.js", instructor: "John Doe", startTime: "2024-02-01T14:30:00Z", participants: 42, isLive: true },
    { id: 2, title: "Data Science with Python", instructor: "Jane Smith", startTime: "2024-02-01T16:00:00Z", participants: 38, isLive: false },
    { id: 3, title: "Web3 & Blockchain", instructor: "Michael Lee", startTime: "2024-02-01T18:00:00Z", participants: 21, isLive: false },
  ];
};

// Open Session Details Modal
const openSessionDetails = (session) => {
  selectedSession.value = session;
};

// Format Time
const formatTime = (isoString) => {
  const date = new Date(isoString);
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit", hour12: true });
};

// Auto-refresh every 10 seconds
onMounted(() => {
  setInterval(fetchSessions, 10000);
});
</script>

<style scoped>
/* Live indicator animation */
.animate-pulse {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.4;
  }
  100% {
    opacity: 1;
  }
}
</style>
