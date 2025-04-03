<script setup>
    import  Axios from 'axios';
    import { ref, onMounted } from 'vue';

    // Import widget and modal components
    import CoursePerformanceWidget from '@/components/widgets/CoursePerformanceWidget.vue';
    import UserStatisticsWidget from '@/components/widgets/UserStatisticsWidget.vue';
    import LiveSessionsWidget from '@/components/widgets/LiveSessionsWidget.vue';
    import ReviewsRatingsWidget from '@/components/widgets/ReviewsRatingsWidget.vue'; 
    import RevenueModal from '@/components/widgets/RevenueModal.vue';
    
    // Reactive state for the dashboard
    const activeFilter = ref('today');

    // Dummy data for demonstration purposes
    const revenueData = ref({
        today: 0,
        month: 0,
        total: 0, 
        fetched: false
    });

    const courseData = ref({
        enrollmentTrend: [30, 45, 60, 80, 70, 90, 100],
        dropoutRate: [5, 4, 6, 3, 5, 2, 4],
        topCourses: [
            { id: 1, name: 'Vue.js Basics', enrollments: 1200, rating: 4.5, thumbnail: 'images/course-1.jpg' },
            { id: 2, name: 'Advanced Tailwind CSS', enrollments: 800, rating: 4.7, thumbnail: 'images/course-2.jpg' },
            // …more courses
        ],
    });

    const userStatsData = ref({
        totalStudents: 5000,
        activeUsers: 3200,
        newRegistrations: 150,
        activeInactive: { active: 3200, inactive: 1800 },
    });

    const liveSessions = ref([
        {
            id: 1,
            title: 'Live Coding Session',
            instructor: 'John Doe',
            startTime: '2025-02-01T14:00:00',
            participants: 75,
            status: 'ongoing',
        },
        {
            id: 2,
            title: 'Design Q&A',
            instructor: 'Jane Smith',
            startTime: '2025-02-01T15:00:00',
            participants: 50,
            status: 'upcoming',
        },
    ]);

    const reviewsData = ref([
        { id: 1, reviewer: 'Alice', rating: 5, comment: 'Great course!', timestamp: '2025-02-01T10:00:00' },
        { id: 2, reviewer: 'Bob', rating: 4, comment: 'Very informative', timestamp: '2025-02-01T11:30:00' },
    ]);

    const systemHealthData = ref({
        uptime: 99.9,
        avgApiResponse: 200,
        errorCount: 2,
        responseTrend: [180, 190, 210, 200, 205],
    });

    // Methods to handle filtering, refreshing, and opening details
    function setFilter(filter) {
        activeFilter.value = filter;
        refreshData();
    }

    function refreshData() {
        // Simulate data refresh; replace with API calls as needed.
        console.log('Refreshing data...');
    }
 

    function openCourseDetail(course) {
        console.log('Open course detail:', course);
    }

    function filterUsers(segment) {
        console.log('Filtering users:', segment);
    }

    function openSessionModal(session) {
        console.log('Open session modal:', session);
    }

    function openReviewDetail(review) {
        console.log('Open review detail:', review);
    }
 
    function fetchTransactions() {
        Axios
            .get('/api/system-transaction')
            .then(res=>{
                revenueData.value.today = res.data.transactionToday;
                revenueData.value.month = res.data.transactionThisMonth;
                revenueData.value.total = res.data.totalSell; 
                revenueData.value.fetched = true;
            })
    }

    function buttonClass(isActive) {
        return isActive
            ? 'bg-lime-700 text-white'
            : 'bg-gray-200 text-gray-900 hover:bg-lime-600 hover:text-white transition';
    }

    onMounted(()=>{
        fetchTransactions();
    })
</script>
<template>
    <!-- Main container -->
    <div class="min-h-screen bg-gray-100 text-gray-900 transition-colors duration-300">
        <!-- Top Navigation Bar -->
        <nav class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 bg-white shadow-md">
            <!-- Date/Time Filters -->
            <div class="flex flex-wrap items-center justify-center space-x-0 sm:space-x-4 mb-2 sm:mb-0">
                <button @click="setFilter('today')"
                    :class="[buttonClass(activeFilter === 'today'), 'px-3 py-1 m-1 sm:m-0 rounded']">
                    Today
                </button>
                <button @click="setFilter('month')"
                    :class="[buttonClass(activeFilter === 'month'), 'px-3 py-1 m-1 sm:m-0 rounded']">
                    This Month
                </button>
                <button @click="setFilter('all')"
                    :class="[buttonClass(activeFilter === 'all'), 'px-3 py-1 m-1 sm:m-0 rounded']">
                    All Time
                </button>
            </div>
            <!-- Refresh Button -->
            <div class="flex items-center justify-center">
                <button @click="refreshData" class="p-2 bg-gray-200 rounded hover:bg-gray-300 transition"
                    aria-label="Refresh Data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </nav>

        <div class="">
            <div class="grid sm:grid-cols-2 gap-4 my-6">
                <RevenueModal 
                    v-if="revenueData?.fetched"
                    :revenueData="revenueData"/>

                <CoursePerformanceWidget 
                    :data="courseData" 
                    @openCourse="openCourseDetail" />
                
                </div>
                <div class="grid  sm:grid-cols-2 md:grid-cols-3 gap-4 my-6">
                    <UserStatisticsWidget 
                        :data="userStatsData" 
                        @filterUsers="filterUsers" />

                    <LiveSessionsWidget 
                        :data="liveSessions" 
                        @openSession="openSessionModal" />

                    <ReviewsRatingsWidget 
                        :data="reviewsData" 
                        @openReview="openReviewDetail" /> 
                </div>
        </div>

        <!-- Revenue Modal -->
        
    </div>
</template>

 
