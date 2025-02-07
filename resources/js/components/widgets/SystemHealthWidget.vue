<template>
  <div class="bg-white rounded-lg shadow p-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-2">
        <i class="fas fa-shield-heart text-lime-700"></i>
        <h3 class="text-lg font-bold">System Health</h3>
      </div>
      <button @click="fetchSystemHealth" class="text-gray-500 hover:text-gray-800">
        <i class="fas fa-sync-alt"></i>
      </button>
    </div>

    <!-- System Health Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <div
        v-for="metric in systemMetrics"
        :key="metric.title"
        class="p-4 rounded-lg transition-all cursor-pointer shadow-sm border"
        :class="getStatusClass(metric.value, metric.thresholds)"
        @click="openDetails(metric)"
      >
        <h4 class="text-sm font-semibold flex items-center space-x-2">
          <i :class="metric.icon"></i>
          <span>{{ metric.title }}</span>
        </h4>
        <p class="text-xl font-bold">{{ metric.value }}{{ metric.unit }}</p>
        <small class="text-gray-500">{{ metric.description }}</small>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
      <!-- API Response Time Line Chart -->
      <div class="bg-gray-100 rounded-lg p-4">
        <h4 class="text-sm font-semibold mb-2">API Response Time</h4>
        <line-chart :data="responseTimeData"></line-chart>
      </div>

      <!-- Uptime Pie Chart -->
      <div class="bg-gray-100 rounded-lg p-4">
        <h4 class="text-sm font-semibold mb-2">Uptime vs. Downtime</h4>
        <pie-chart :data="uptimeData"></pie-chart>
      </div>
    </div>

    <!-- System Logs Modal -->
    <Teleport to="body">
      <div v-if="selectedMetric" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
          <div class="flex justify-between items-center mb-3">
            <h4 class="text-lg font-semibold">{{ selectedMetric.title }} Details</h4>
            <button @click="selectedMetric = null" class="text-gray-500 hover:text-gray-800">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <p class="text-gray-700">{{ selectedMetric.details }}</p>
          <div class="text-right mt-4">
            <button class="px-3 py-1 text-sm bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400" 
                    @click="selectedMetric = null">
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import LineChart from '@/components/LineChart.vue';
import PieChart from '@/components/PieChart.vue';

let intervalId;

onMounted(() => {
    intervalId = setInterval(fetchSystemHealth, 10000);
});

onUnmounted(() => {
    clearInterval(intervalId);
});

// Sample Data
const systemMetrics = ref([
  { title: "Uptime", value: 99.8, unit: "%", description: "System availability", icon: "fas fa-clock", thresholds: { warning: 95, critical: 90 }, details: "Server uptime report for the last 30 days." },
  { title: "API Response", value: 120, unit: "ms", description: "Average API response time", icon: "fas fa-server", thresholds: { warning: 200, critical: 400 }, details: "API response times measured over the last 24 hours." },
  { title: "Errors", value: 3, unit: "", description: "System errors detected", icon: "fas fa-exclamation-circle", thresholds: { warning: 10, critical: 20 }, details: "List of recent errors encountered by the system." }
]);

const selectedMetric = ref(null);

// Simulated API Response Time Data
const responseTimeData = ref([50, 100, 120, 140, 160, 110, 90]);

// Simulated Uptime Data
const uptimeData = ref([
  { label: "Uptime", value: 99.8, color: "lime-700" },
  { label: "Downtime", value: 0.2, color: "red" }
]);

// Fetch System Health Data (Simulated API Call)
const fetchSystemHealth = () => {
  systemMetrics.value = systemMetrics.value.map((metric, index) => {
      if (index === 1) {
          return { ...metric, value: Math.floor(Math.random() * 300) + 50 };
      }
      if (index === 2) {
          return { ...metric, value: Math.floor(Math.random() * 10) };
      }
      return metric;
  });

  responseTimeData.value = [...responseTimeData.value, Math.floor(Math.random() * 250) + 50].slice(-10);
};

// Get Status Class Based on Thresholds
const getStatusClass = (value, thresholds) => {
  if (value >= thresholds.critical) return "bg-red-100 border-red-400 text-red-700";
  if (value >= thresholds.warning) return "bg-yellow-100 border-yellow-400 text-yellow-700";
  return "bg-lime-100 border-lime-700 text-lime-700";
};

// Open Details Modal
const openDetails = (metric) => {
  selectedMetric.value = metric;
};
</script>

<style scoped>
/* Fade animation */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
