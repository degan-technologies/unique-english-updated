<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
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
    BarController, BarElement, LineController, LineElement,
    PointElement, LinearScale, CategoryScale, Tooltip, Legend
);

// Refs
const isLoading = ref(true);
const error = ref(null);
const chartInstance = ref(null);
const chartCanvas = ref(null);
const selectedYear = ref(new Date().getFullYear());
const activeChart = ref('sales');
const topCourses = ref([]);
const monthlySales = ref([]);

// Formatting functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'ETB',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
};

const formatMonth = (month) => {
    return new Date(2000, month - 1).toLocaleString('default', { month: 'short' });
};

// Fetch data
const fetchTopSoldCourses = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get('/api/top-sold-courses');
        topCourses.value = response.data.data.top_courses;
        monthlySales.value = response.data.data.monthly_sales;
    } catch (err) {
        console.error('Error fetching data:', err);
        error.value = 'Failed to load course analytics. Please try again later.';
    } finally {
        isLoading.value = false;
    }
};

// Chart data computation
const availableYears = computed(() => {
    const years = new Set();
    monthlySales.value.forEach(sale => years.add(sale.year));
    return Array.from(years).sort();
});

const filteredMonthlyData = computed(() => {
    return monthlySales.value
        .filter(sale => sale.year == selectedYear.value)
        .sort((a, b) => a.month - b.month);
});

const chartData = computed(() => {
    const labels = Array.from({ length: 12 }, (_, i) => formatMonth(i + 1));
    const salesData = Array(12).fill(0);
    const revenueData = Array(12).fill(0);

    filteredMonthlyData.value.forEach(sale => {
        salesData[sale.month - 1] = sale.count;
        revenueData[sale.month - 1] = sale.revenue;
    });

    return {
        labels,
        datasets: [
            {
                label: 'Sales Count',
                data: salesData,
                backgroundColor: 'rgba(101, 163, 13, 0.7)',
                borderColor: 'rgba(101, 163, 13, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            },
            {
                label: 'Revenue',
                data: revenueData,
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }
        ]
    };
});

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        tooltip: {
            callbacks: {
                label: (context) => {
                    let label = context.dataset.label || '';
                    if (label) label += ': ';
                    if (context.datasetIndex === 1) {
                        label += formatCurrency(context.parsed.y);
                    } else {
                        label += context.parsed.y;
                    }
                    return label;
                }
            }
        },
        legend: {
            position: 'top',
        }
    },
    scales: {
        y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: {
                display: true,
                text: 'Sales Count'
            },
            ticks: {
                precision: 0
            }
        },
        y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: {
                display: true,
                text: 'Revenue'
            },
            grid: {
                drawOnChartArea: false
            },
            ticks: {
                callback: (value) => formatCurrency(value)
            }
        }
    }
}));

// Chart management
const initializeChart = () => {
    if (!chartCanvas.value) return;

    if (chartInstance.value) {
        chartInstance.value.destroy();
    }

    chartInstance.value = new Chart(chartCanvas.value, {
        type: 'bar',
        data: chartData.value,
        options: chartOptions.value
    });
};

const setChartType = (type) => {
    activeChart.value = type;
    initializeChart();
};

// Lifecycle
onMounted(async () => {
    await fetchTopSoldCourses();
    initializeChart();
});
</script>

<template> 
    <div  class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-lime-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-lime-700" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Course Sales Analytics</h2>
            </div>

            <div class="flex gap-2 items-center">
                <select v-model="selectedYear" @change="initializeChart"
                    class="border rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-lime-500">
                    <option v-for="year in availableYears" :key="year" :value="year">
                        {{ year }}
                    </option>
                </select>

                <button @click="setChartType('sales')" :class="[
                    'px-3 py-1.5 rounded-md text-sm font-medium transition-colors',
                    activeChart === 'sales'
                        ? 'bg-lime-600 text-white'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]">
                    Sales
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-lime-500"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8">
            <div class="text-red-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="mt-2">{{ error }}</p>
            </div>
            <button @click="fetchTopSoldCourses"
                class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-colors">
                Retry
            </button>
        </div>

        <!-- Content -->
        <div v-else class="space-y-6">
            <!-- Chart -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="h-64">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </div>

            <!-- Top Courses -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-lime-600" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                            clip-rule="evenodd" />
                    </svg>
                    Top 5 Courses
                </h3>

                <div class="max-h-72 overflow-y-auto scrollable-container">
                    <ul class="space-y-2">
                        <li v-for="(course, index) in topCourses" :key="index"
                            class="flex items-center space-x-3 p-3 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-200 border border-gray-100"
                            @click="handleCourseClick(course.course_id)">

                            <div class="relative flex-shrink-0">
                                <img :src="course.thumbnail_url"
                                    alt="Course Thumbnail"
                                    class="w-12 h-12 rounded-lg object-cover border border-gray-200" loading="lazy" /> 
                            </div>

                            <div class="flex-grow min-w-0">
                                <div class="flex flex-row justify-between">
                                    <h4 class="font-medium text-gray-900 truncate">{{ course.course_name }}</h4>
                                    <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-400"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span class="ml-1">{{ course.average_rating.averageRating }}</span>
                                    </span> 
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <div class="text-sm text-gray-600 flex items-center space-x-2">
                                        <span>{{ course.transaction_count }} enrollments</span>
                                    </div>
                                    <span class="text-sm font-medium text-lime-600">
                                        {{ course.total_revenue }} ETB
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div v-if="topCourses.length === 0" class="text-center py-4 text-gray-500">
                    No courses available
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
.scrollable-container {
    scrollbar-width: thin;
    scrollbar-color: #E0E7FF #F8FAFC;
}

.scrollable-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.scrollable-container::-webkit-scrollbar-thumb {
    background-color: #E0E7FF;
    border-radius: 20px;
}

.scrollable-container::-webkit-scrollbar-track {
    background-color: #F8FAFC;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>