<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import {
    Chart,
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Tooltip,
    Legend
} from 'chart.js';

// Register Chart.js components
Chart.register(
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Tooltip,
    Legend
);

// Define the prop for incoming data
const props = defineProps({
    data: {
        type: Object,
        required: true
    }
});

// Local reactive state for active chart type and chart instance reference
const activeChart = ref('enrollment'); // either 'enrollment' or 'dropout'
const chartInstance = ref(null);
const chartCanvas = ref(null);

// Computed property to get top 5 courses
const topFiveCourses = computed(() => {
    return props.data.topCourses.slice(0, 5);
});

// Utility function to return button classes based on active state
const buttonClass = (isActive) => {
    return isActive
        ? 'bg-lime-700 text-white px-4 py-2 rounded'
        : 'bg-gray-200 text-gray-900 px-4 py-2 rounded';
};

// Set active chart type when a toggle button is clicked
function setChart(type) {
    activeChart.value = type;
}

// Function to generate chart configuration based on active chart type
const getChartConfig = () => {
    // Dummy labels (replace with dynamic labels as needed)
    const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    if (activeChart.value === 'enrollment') {
        return {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Enrollments',
                        data: props.data.enrollmentTrend,
                        backgroundColor: 'rgba(101, 163, 13, 0.5)', // Brand color: lime-700 with opacity
                        borderColor: 'rgba(101, 163, 13, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => `Enrollments: ${context.parsed.y}`
                        }
                    },
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: {
                    duration: 500,
                    easing: 'easeInOutQuad'
                }
            }
        };
    } else if (activeChart.value === 'dropout') {
        return {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Dropout Rate (%)',
                        data: props.data.dropoutRate,
                        fill: false,
                        borderColor: 'rgba(239, 68, 68, 1)', // Red color (you may change this if needed)
                        backgroundColor: 'rgba(239, 68, 68, 0.5)',
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => `Dropout Rate: ${context.parsed.y}%`
                        }
                    },
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: {
                    duration: 500,
                    easing: 'easeInOutQuad'
                }
            }
        };
    }
};

// Initialize the chart when the component mounts
onMounted(() => {
    const config = getChartConfig();
    chartInstance.value = new Chart(chartCanvas.value.getContext('2d'), config);
});

// Watch for changes in the active chart type and update the chart
watch(activeChart, () => {
    if (chartInstance.value) {
        chartInstance.value.destroy();
    }
    const config = getChartConfig();
    chartInstance.value = new Chart(chartCanvas.value.getContext('2d'), config);
});

// Watch for changes in incoming data and update the chart accordingly
watch(
    () => props.data,
    () => {
        if (chartInstance.value) {
            chartInstance.value.destroy();
        }
        const config = getChartConfig();
        chartInstance.value = new Chart(chartCanvas.value.getContext('2d'), config);
    }
);
</script>

<template>
    <div class="bg-white rounded-lg shadow p-4 w-full">
        <!-- Header with Icon and Title -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 mb-4">
            <i class="fas fa-graduation-cap text-lime-700 text-2xl"></i>
            <h3 class="text-lg sm:text-xl font-bold">Course Performance</h3>
        </div>

        <!-- Toggle Buttons for Chart Type -->
        <div class="mb-4 flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
            <button @click="setChart('enrollment')" :class="buttonClass(activeChart === 'enrollment')">
                Enrollment
            </button>
            <button @click="setChart('dropout')" :class="buttonClass(activeChart === 'dropout')">
                Dropout Rate
            </button>
        </div>

        <!-- Chart Area -->
        <div class="mb-4">
            <canvas ref="chartCanvas" class="w-full h-auto"></canvas>
        </div>

        <!-- Top Courses List -->
        <div>
            <h4 class="text-md sm:text-lg font-semibold mb-2">Top 5 Courses</h4>
            <ul class="space-y-2">
                <li v-for="course in topFiveCourses" :key="course.id"
                    class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4 p-2 bg-gray-50 rounded cursor-pointer hover:bg-gray-100 transition"
                    @click="$emit('openCourse', course)">
                    <img :src="course.thumbnail" alt="Course Thumbnail" class="w-12 h-12 rounded object-cover" />
                    <div class="flex-1 text-center sm:text-left">
                        <div class="font-bold">{{ course.name }}</div>
                        <div class="text-sm text-gray-600">
                            Enrollments: {{ course.enrollments }} | Rating: {{ course.rating }}
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
/* Additional styling can be added here if needed */
</style>
