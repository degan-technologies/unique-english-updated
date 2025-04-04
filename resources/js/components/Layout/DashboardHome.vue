<script setup>
    import  Axios from 'axios';
    import { ref, onMounted } from 'vue';

    // Import widget and modal components
    import CoursePerformanceWidget from '@/components/widgets/CoursePerformanceWidget.vue';
    import UserStatisticsWidget from '@/components/widgets/UserStatisticsWidget.vue';
    import LiveSessionsWidget from '@/components/widgets/LiveSessionsWidget.vue';
    import ReviewsRatingsWidget from '@/components/widgets/ReviewsRatingsWidget.vue'; 
    import RevenueModal from '@/components/widgets/RevenueModal.vue';
     
    const activeFilter = ref('today');
 
    const revenueData = ref({
        today: 0,
        month: 0,
        total: 0,
        courseSell: 0,
        bookSell: 0,
        liveSell: 0,  
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
            .get('/api/system-transaction', {
                params: {
                    activeFilter:activeFilter.value,
                }
            })
            .then(res=>{
                revenueData.value.today = res.data.transactionToday;
                revenueData.value.month = res.data.transactionThisMonth;
                revenueData.value.total = res.data.totalSell; 
                revenueData.value.courseSell = res.data.courseSell;
                revenueData.value.bookSell = res.data.bookSell;
                revenueData.value.liveSell = res.data.liveSell;   
            })
    } 

    const filterCourse = (filterType) =>{
        activeFilter.value = filterType;
        fetchTransactions();
    }

    onMounted(()=>{
        fetchTransactions();
    })
</script>
<template>
    <!-- Main container -->
    <div class="min-h-screen bg-gray-100 text-gray-900 transition-colors duration-300">
        <!-- Top Navigation Bar -->

        <div class="">
            <div class="grid sm:grid-cols-2 gap-4 my-6">
                <RevenueModal 
                    :revenueData="revenueData"
                    @filterCourse="filterCourse"/>

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

 
