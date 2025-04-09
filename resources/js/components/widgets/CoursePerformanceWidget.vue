<script setup>
    import Axios from 'axios';
    import { ref, onMounted, watch, computed, watchEffect } from 'vue';
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

    const props = defineProps({
        data: {
            type: Object,
            required: true
        }
    });


    const activeChart = ref('enrollment');
    const chartInstance = ref(null);
    const chartCanvas = ref(null);
    const courses = ref([]);
    const books = ref([]);
    const transactions = ref([]);
    const selectedYear = ref(new Date().getFullYear());

    const fetchTransactions = async () => {
        try {
    
            const response = await Axios.get('/api/transaction');
        
            transactions.value = response.data.data || [];
        } catch (error) {
            console.error('Error fetching transactions:', error);
        }
    };



    const fetchBooks = async () => {
        try {
            const response = await Axios.get(`/api/all-books`); 
            books.value = response.data.data || [];

        } catch (error) {
            console.error("Error fetching books:", error);
        }
    };

    const topFiveBooks = computed(() => {

        const bookEnrollments = {};

        transactions.value.forEach(transaction => {
            if (transaction.type === 'book' && transaction.book_id) {
                if (bookEnrollments[transaction.book_id]) {
                    bookEnrollments[transaction.book_id] += 1;
                } else {
                    bookEnrollments[transaction.book_id] = 1;
                }
            }
        });

        const booksWithEnrollments = books.value.map(book => {
            return {
                ...book,
                totalEnrollments: bookEnrollments[book.id] || 0, 
            };
        });

        return [...booksWithEnrollments]
            .sort((a, b) => {
                if (b.totalEnrollments === a.totalEnrollments) {
                    return (b.averageRating || 0) - (a.averageRating || 0); 
                }
                return b.totalEnrollments - a.totalEnrollments; 
            })
            .slice(0, 5);
    });

    const fetchTopCourses = async () => {
        try {
            const response = await Axios.get(`/api/all-couses`); 
            courses.value = response.data.data || [];

        } catch (error) {
            console.error("Error fetching courses:", error);
        }
    };

    const topFiveCourses = computed(() => {

        const courseEnrollments = {};

        transactions.value.forEach(transaction => {
            if (transaction.type === 'course' && transaction.course_id) {
                if (courseEnrollments[transaction.course_id]) {
                    courseEnrollments[transaction.course_id] += 1;
                } else {
                    courseEnrollments[transaction.course_id] = 1;
                }
            }
        });

        const coursesWithEnrollments = courses.value.map(course => {
            return {
                ...course,
                totalEnrollments: courseEnrollments[course.id] || 0 
            };
        });
        return coursesWithEnrollments
            .sort((a, b) => b.totalEnrollments - a.totalEnrollments) 
            .slice(0, 5);
    });

    const buttonClass = (isActive) => {
        return isActive
            ? 'bg-lime-700 text-white px-4 py-2 rounded'
            : 'bg-gray-200 text-gray-900 px-4 py-2 rounded';
    };

    function setChart(type) {
        activeChart.value = type;
    }

    const allMonths = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const availableYears = computed(() => {
        const years = new Set();
        transactions.value.forEach((transaction) => {
            if (transaction.date) {
                const year = new Date(transaction.date).getFullYear();
                years.add(year);
            }
        });
        return Array.from(years).sort(); 
    });

    // Compute summary only for the selected year
    const transactionSummary = computed(() => {
        const summary = {};

        transactions.value.forEach((transaction) => {
            if (!transaction.date || !transaction.type) return;

            const dateObj = new Date(transaction.date);
            const year = dateObj.getFullYear();
            const monthKey = dateObj.toLocaleString('default', { month: 'long' });

            if (year === selectedYear.value) {
                if (!summary[monthKey]) {
                    summary[monthKey] = { enrollments: 0, books: 0 };
                }

                if (transaction.type === 'course') {
                    summary[monthKey].enrollments += 1;
                } else if (transaction.type === 'book') {
                    summary[monthKey].books += 1;
                }
            }
        });

        allMonths.forEach((month) => {
            if (!summary[month]) {
                summary[month] = { enrollments: 0, books: 0 };
            }
        });

        return summary;
    });

    const chartLabels = computed(() => allMonths);

    const enrollmentCounts = computed(() =>
        chartLabels.value.map((month) => transactionSummary.value[month]?.enrollments || 0)
    );

    const bookCounts = computed(() =>
        chartLabels.value.map((month) => transactionSummary.value[month]?.books || 0)
    );

    const getChartConfig = () => {
        const labels = chartLabels.value;

        if (activeChart.value === 'enrollment') {
            return {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Enrollments',
                            data: enrollmentCounts.value, 
                            backgroundColor: 'rgba(101, 163, 13, 0.5)', 
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
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 10,
                                callback: (value) => value.toLocaleString()
                            }
                        }
                    },
                    animation: {
                        duration: 500,
                        easing: 'easeInOutQuad'
                    }
                }
            };
        } else if (activeChart.value === 'book') {
            return {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Enrollments',
                            data: bookCounts.value,  
                            fill: false,
                            borderColor: 'rgba(59, 130, 246, 1)', 
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
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
                                label: (context) => `Enrollments: ${context.parsed.y}`
                            }
                        },
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 10,
                                precision: 0,
                                callback: (value) => {
                                    if (value >= 1000000) return `${value / 1000000}M`; 
                                    if (value >= 1000) return `${value / 1000}K`; 
                                    if (value >= 100) return `${value / 100}H`;
                                    return value; 
                                }
                            }
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

    onMounted(async () => {
        await fetchTopCourses();
        await fetchBooks();
        await fetchTransactions(); 

        if (Object.keys(transactionSummary.value).length) {
            initializeChart(); 
        }
    });

    const initializeChart = () => {
        if (!chartCanvas.value) return;

        const config = getChartConfig();
        chartInstance.value = new Chart(chartCanvas.value.getContext('2d'), config);
    };

    watchEffect(() => {
        if (Object.keys(transactionSummary.value).length) {
            if (chartInstance.value) {
                chartInstance.value.destroy();
            }
            initializeChart();
        }
    });

</script>

<template>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 mb-4">
            <i class="fas fa-graduation-cap text-lime-700 text-2xl"></i>
            <h3 class="text-lg sm:text-xl font-bold">Performance Indicator</h3>
        </div>
        <div class="mb-4 flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
            <button @click="setChart('enrollment')"
                :class="buttonClass(activeChart === 'enrollment')">
                Course Enrollments
            </button>
            <button @click="setChart('book')"
                :class="buttonClass(activeChart === 'book')">
                Book Enrollments
            </button>
            <div class="flex justify-end mb-4 items-center">
            <select id="yearFilter"
                v-model="selectedYear"
                class="border rounded px-2 py-1">
                <option v-for="year in availableYears"
                    :key="year"
                    :value="year">
                    {{ year }}
                </option>
            </select>
        </div>
        </div>
        <!-- Chart Area -->
        <div class="mb-3">
            <div class="relative w-full max-w-3xl mx-auto">
                <canvas ref="chartCanvas"
                    class="block w-full h-full"></canvas>
            </div>
        </div>
        <!-- Top Courses/Books List -->
        <div v-if="activeChart === 'enrollment'">
            <h4 class="text-md sm:text-lg font-semibold mb-2">Top 5 Courses</h4>
            <div class="max-h-40 overflow-y-auto scrollable-container">
                <ul class="space-y-2">
                    <li
                        v-for="course in topFiveCourses"
                        :key="course.id"
                        class="flex items-center space-x-4 p-2 rounded cursor-pointer hover:bg-gray-100 transition border border-gray-100"
                        >
                        <img
                            :src="course.thumbnail_url"
                            alt="Course Thumbnail"
                            class="w-10 h-10 rounded object-cover"
                        />
                        <div class="flex flex-col justify-center">
                            <div class="font-normal">{{ course.course_name }}</div>
                            <div class="text-sm text-gray-600">
                            Enrollments: {{ course.totalEnrollments }} | Rating:
                            {{ (course.averageRating || 0).toFixed(1) }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div v-else-if="activeChart === 'book'">
            <h4 class="text-md sm:text-lg font-semibold mb-2">Top 5 Books</h4>
            <div class="max-h-40 overflow-y-auto scrollable-container">
                <ul class="space-y-2">
                    <li v-for="book in topFiveBooks"
                        :key="book.id"
                        class="flex items-center space-x-4 p-2 rounded cursor-pointer hover:bg-gray-100 transition border border-gray-100"
                        >
                        <img :src="book.cover_page_url"
                            alt="Book Thumbnail"
                            class="w-10 h-10 rounded object-cover" />
                        <div class="flex flex-col justify-center">
                            <div class="font-normal">{{ book.title || 'Unknown Book' }}</div>
                            <div class="text-sm text-gray-600">
                                Enrollments: {{ book.totalEnrollments }} | Rating: {{ (book.averageRating ||
                                0).toFixed(1) }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
    canvas {
        max-width: 100% !important;
    }

    .scrollable-container {
        max-height: 130px;
        overflow-y: auto;
    }

    .scrollable-container {
        scrollbar-width: thin;
        scrollbar-color: #A0AEC0 #F7FAFC;

    }

    .scrollable-container::-webkit-scrollbar {
        width: 4px;
    }

    .scrollable-container::-webkit-scrollbar-thumb {
        background-color: #A0AEC0;
        border-radius: 5px;
    }

    .scrollable-container::-webkit-scrollbar-track {
        background-color: #F7FAFC;
    }
</style>