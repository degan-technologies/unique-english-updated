<script setup>
import { ref, onMounted, nextTick } from "vue";
import Axios from "axios";
import { Chart, ArcElement, Tooltip, Legend } from "chart.js";

Chart.register(ArcElement, Tooltip, Legend);

const animatedTotalUsers = ref(0);
const animatedActiveUsers = ref(0);
const animatedNewRegistrations = ref(0);

const stats = ref({
    totalUsers: 0,
    activeUsers: 0,
    newRegistrations: 0,
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

// Render the donut chart (active vs inactive users)
function renderDonutChart() {
    nextTick(() => {
        const ctx = donutChartCanvas.value.getContext("2d");
        if (!ctx) return;

        // Ensure at least 1 active user for visualization
        const activeUsers = Math.max(stats.value.activeUsers, 1);
        const inactiveUsers = Math.max(stats.value.totalUsers - activeUsers);

        const config = {
            type: "doughnut",
            data: {
                labels: ["Active", "Inactive"],
                datasets: [
                    {
                        data: [activeUsers, inactiveUsers],
                        backgroundColor: ["#f97316", "#e5e7eb"], // Orange for active, Gray for inactive
                        hoverBackgroundColor: ["#f97316", "#d1d5db"],
                        borderWidth: 1,
                        borderColor: ["#f97316", "#d1d5db"],
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: "bottom",
                        labels: { color: "#374151" },
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.label || "";
                                const value = context.parsed;
                                return `${label}: ${value}`;
                            },
                        },
                    },
                },
            },
        };

        if (donutChartInstance) {
            donutChartInstance.destroy();
        }
        donutChartInstance = new Chart(ctx, config);
    });
}

async function fetchUsers() {
    Axios.get("api/get-user/statistics").then((res) => {
        stats.value.totalUsers = res.data.totalUsers;
        stats.value.activeUsers = res.data.activeUsers;
        stats.value.newRegistrations = res.data.newRegistrations;

        animateValue(
            animatedTotalUsers,
            animatedTotalUsers.value,
            stats.value.totalUsers
        );
        animateValue(
            animatedActiveUsers,
            animatedActiveUsers.value,
            stats.value.activeUsers
        );
        animateValue(
            animatedNewRegistrations,
            animatedNewRegistrations.value,
            stats.value.newRegistrations
        );

        renderDonutChart();
    });
}

onMounted(() => {
    fetchUsers();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg w-full p-6 relative">
        <div class="flex items-center space-x-2 mb-4">
            <i class="fas fa-users text-lime-700"></i>
            <h3 class="text-lg font-bold text-gray-900">User Statistics</h3>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6 text-center mx-auto">
            <div class="py-2">
                <div class="text-3xl font-bold text-lime-700">
                    {{ animatedTotalUsers }}
                </div>
                <div class="text-sm text-gray-600">Total Users</div>
            </div>
            <div class="py-2">
                <div class="text-3xl font-bold text-lime-700">
                    {{ animatedActiveUsers == 0 ? 1 : animatedActiveUsers }}
                </div>
                <div class="text-sm text-gray-600">Active Users</div>
            </div>
            <div class="py-2">
                <div class="text-3xl font-bold text-lime-700">
                    {{ animatedNewRegistrations }}
                </div>
                <div class="text-sm text-gray-600">New User</div>
            </div>
        </div>
        <div class="relative h-64">
            <canvas ref="donutChartCanvas" class="w-full"></canvas>
        </div>
    </div>
</template>
