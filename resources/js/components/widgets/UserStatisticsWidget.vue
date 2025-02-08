<template>
  <div class="bg-white rounded-lg shadow p-4">
    <!-- Header with Icon and Title -->
    <div class="flex items-center space-x-2 mb-4">
      <i class="fas fa-users text-lime-700"></i>
      <h3 class="text-lg font-bold text-gray-900">User Statistics</h3>
    </div>

    <!-- User Metrics Counters -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 text-center">
      <div class="p-2">
        <div class="text-3xl font-bold text-lime-700">{{ animatedTotalStudents }}</div>
        <div class="text-sm text-gray-600">Total Students</div>
      </div>
      <div class="p-2">
        <div class="text-3xl font-bold text-lime-700">{{ animatedActiveUsers }}</div>
        <div class="text-sm text-gray-600">Active Users</div>
      </div>
      <div class="p-2">
        <div class="text-3xl font-bold text-lime-700">{{ animatedNewRegistrations }}</div>
        <div class="text-sm text-gray-600">New Registrations</div>
      </div>
    </div>

    <!-- Donut Chart for Active vs Inactive Users -->
    <div class="relative w-full max-w-md mx-auto">
      <canvas ref="donutChartCanvas" class="w-full h-auto"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import {
  Chart,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js';

Chart.register(ArcElement, Tooltip, Legend);

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});

// Animated counter reactive variables
const animatedTotalStudents = ref(0);
const animatedActiveUsers = ref(0);
const animatedNewRegistrations = ref(0);

const donutChartCanvas = ref(null);
let donutChartInstance = null;

// Helper function to animate counters
function animateValue(refValue, start, end, duration = 800) {
  const startTime = performance.now();

  const animate = (currentTime) => {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    refValue.value = Math.floor(start + (end - start) * progress);
    if (progress < 1) {
      requestAnimationFrame(animate);
    }
  };
  requestAnimationFrame(animate);
}

// Function to render donut chart
function renderDonutChart() {
  nextTick(() => {
    const ctx = donutChartCanvas.value.getContext('2d');
    if (!ctx) return;

    const config = {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Inactive'],
        datasets: [{
          data: [props.data.activeInactive.active, props.data.activeInactive.inactive],
          backgroundColor: ['#84cc16', '#e5e7eb'], // Lime-700 and Gray-200
          hoverBackgroundColor: ['#65a30d', '#d1d5db'],
          borderWidth: 1,
          borderColor: ['#65a30d', '#d1d5db']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: { color: '#374151' }
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                const label = context.label || '';
                const value = context.parsed;
                return `${label}: ${value}`;
              }
            }
          }
        }
      }
    };

    if (donutChartInstance) {
      donutChartInstance.destroy();
    }
    donutChartInstance = new Chart(ctx, config);
  });
}

// Watch for data changes to update counters and chart
watch(() => props.data, (newData) => {
  animateValue(animatedTotalStudents, animatedTotalStudents.value, newData.totalStudents);
  animateValue(animatedActiveUsers, animatedActiveUsers.value, newData.activeUsers);
  animateValue(animatedNewRegistrations, animatedNewRegistrations.value, newData.newRegistrations);
  renderDonutChart();
}, { immediate: true, deep: true });

onMounted(() => {
  renderDonutChart();
});
</script>

<style scoped>
/* Ensuring responsiveness */
@media (max-width: 640px) {
  .grid-cols-3 {
    grid-template-columns: 1fr;
  }
}
</style>
