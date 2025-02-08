<template>
  <Teleport to="body">
    <!-- Wrapper that allows vertical scrolling on mobile devices -->
    <div class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center overflow-y-auto p-4">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-lg sm:max-w-xl md:max-w-2xl p-6 relative max-h-screen overflow-y-auto">
        <!-- Close Button -->
        <button
          @click="closeModal"
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-800"
        >
          <i class="fas fa-times"></i>
        </button>

        <!-- Modal Header -->
        <div class="flex items-center space-x-2 mb-4">
          <i class="fas fa-dollar-sign text-lime-700 text-lg"></i>
          <h3 class="text-xl font-bold text-lime-700">Revenue Summary</h3>
        </div>

        <!-- Revenue Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <div
            v-for="metric in revenueMetrics"
            :key="metric.title"
            class="p-4 rounded-lg transition-all cursor-pointer shadow-sm border border-lime-700 bg-lime-100 text-lime-900"
            @click="openDetails(metric)"
          >
            <h4 class="text-sm font-semibold flex items-center space-x-2">
              <i :class="metric.icon"></i>
              <span>{{ metric.title }}</span>
            </h4>
            <p class="text-xl font-bold">
              <span class="animate-number">{{ metric.value }}</span>
            </p>
            <small class="text-gray-700">{{ metric.description }}</small>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
          <!-- Revenue Trend Line Chart -->
          <div class="bg-gray-100 rounded-lg p-4">
            <h4 class="text-sm font-semibold mb-2 text-lime-700">Revenue Trend (Last 7 Days)</h4>
            <line-chart :data="revenueTrendCopy"></line-chart>
          </div>

          <!-- Revenue Breakdown Pie Chart -->
          <div class="bg-gray-100 rounded-lg p-4">
            <h4 class="text-sm font-semibold mb-2 text-lime-700">Revenue Breakdown</h4>
            <pie-chart :data="revenueBreakdownCopy"></pie-chart>
          </div>
        </div>

        <!-- Close Button at the bottom -->
        <div class="text-right mt-4">
          <button
            class="px-4 py-2 bg-lime-700 text-white rounded-md hover:bg-lime-800"
            @click="closeModal"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import LineChart from '@/components/LineChart.vue';
import PieChart from '@/components/PieChart.vue';

// Define the emit for the 'close' event
const emit = defineEmits(['close']);

// Revenue Metrics
const revenueMetrics = ref([
  {
    title: "Today's Revenue",
    value: 2500,
    description: "Revenue generated today",
    icon: "fas fa-wallet",
    details: "Today's revenue details and transactions."
  },
  {
    title: "This Month",
    value: 53000,
    description: "Total revenue this month",
    icon: "fas fa-calendar-alt",
    details: "This month's revenue breakdown by day."
  },
  {
    title: "Total Earnings",
    value: 1200000,
    description: "Lifetime revenue",
    icon: "fas fa-chart-line",
    details: "Lifetime earnings, including historical data."
  }
]);

// Revenue Trend Data (Last 7 Days)
const revenueTrend = ref([2000, 2300, 2500, 2600, 2800, 3000, 3200]);

// Revenue Breakdown Data
const revenueBreakdown = ref([
  { label: "Courses", value: 60, color: "blue" },
  { label: "Subscriptions", value: 30, color: "green" },
  { label: "Other Services", value: 10, color: "orange" }
]);

// Computed shallow copies for passing as props to child components
const revenueTrendCopy = computed(() => [...revenueTrend.value]);
const revenueBreakdownCopy = computed(() => [...revenueBreakdown.value]);

// Close Modal Function
const closeModal = () => {
  emit('close');
};

// Open Details Function
const openDetails = (metric) => {
  alert(`Details for ${metric.title}: ${metric.details}`);
};

let intervalId = null;

// Auto-refresh revenue data every 10 seconds
onMounted(() => {
  intervalId = setInterval(() => {
    console.log("Refreshing revenue data...");
    revenueMetrics.value[0].value = Math.floor(Math.random() * 3000) + 1500;
    revenueMetrics.value[1].value = Math.floor(Math.random() * 60000) + 50000;

    // Update Revenue Trend
    revenueTrend.value.push(Math.floor(Math.random() * 3500) + 2000);
    if (revenueTrend.value.length > 7) {
      revenueTrend.value.shift();
    }
  }, 10000);
});

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId);
  }
});
</script>

<style scoped>
/* Fade animation */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
