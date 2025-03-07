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
        <div class="text-3xl font-bold text-lime-700">{{ animatedTotalUsers }}</div>
        <div class="text-sm text-gray-600">Total Users</div>
      </div>
      <div class="p-2">
        <div class="text-3xl font-bold text-lime-700">{{ animatedTotalUsers }}</div>
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
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';
import { Chart, ArcElement, Tooltip, Legend } from 'chart.js';

Chart.register(ArcElement, Tooltip, Legend);

// Animated counter reactive variables
const animatedTotalUsers = ref(0);
const animatedActiveUsers = ref(0);
const animatedNewRegistrations = ref(0);

// Reactive variable to store fetched users
const users = ref([]);

// Reactive object for computed stats (used for the donut chart)
const stats = ref({
  totalUsers: 0,
  activeUsers: 0,
  newRegistrations: 0
});

// Reference for the Chart.js canvas element and instance
const donutChartCanvas = ref(null);
let donutChartInstance = null;

// Helper function to animate counter values
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

// Compute stats from the fetched users data
function computeStats() {
  const now = new Date();
  const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

  // Total users count
  const totalUsers = users.value.length;

  // New registrations: users created in the last 7 days
  const newRegistrations = users.value.filter(user => {
    return new Date(user.created_at) >= weekAgo;
  }).length;

  // Active users: users with a last_login_at within the last 7 days
  const activeUsers = users.value.filter(user => {
    return user.last_login_at && new Date(user.last_login_at) >= weekAgo;
  }).length;

  // Animate the counters
  animateValue(animatedTotalUsers, animatedTotalUsers.value, totalUsers);
  animateValue(animatedNewRegistrations, animatedNewRegistrations.value, newRegistrations);
  animateValue(animatedActiveUsers, animatedActiveUsers.value, activeUsers);

  // Update stats used for the chart
  stats.value = {
    totalUsers,
    activeUsers,
    newRegistrations
  };

  renderDonutChart();
}

// Render the donut chart (active vs inactive users)
function renderDonutChart() {
  nextTick(() => {
    const ctx = donutChartCanvas.value.getContext('2d');
    if (!ctx) return;

    // Ensure at least 1 active user for visualization
    const activeUsers = Math.max(stats.value.activeUsers, 1);
    const inactiveUsers = Math.max(stats.value.totalUsers - stats.value.activeUsers, 0);

    const config = {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Inactive'],
        datasets: [{
          data: [activeUsers, inactiveUsers],
          backgroundColor: ['#84cc16', '#e5e7eb'], // Green for active, Gray for inactive
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

// Fetch users from the API endpoint and compute stats on the front-end
async function fetchUsers() {
  try {
    const response = await axios.get('api/users');
    // Assuming your controller returns data as { data: [...] }
    // Adjust if necessary (e.g. response.data.data)
    users.value = response.data.data || response.data;
    computeStats();
  } catch (error) {
    console.error('Error fetching users:', error);
  }
}

onMounted(() => {
  fetchUsers();
});
</script>

<style scoped>
/* Ensure responsiveness on small screens */
@media (max-width: 640px) {
  .grid-cols-3 {
    grid-template-columns: 1fr;
  }
}
</style>
