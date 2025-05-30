<script setup>
    import  Axios from 'axios';
    import { storeToRefs } from "pinia";
    import { ref, onMounted } from 'vue';

    import { useSidebarStore } from "@/store/useSidebarStore";

    // Import widget and modal components
    import JetsiLive from "@/components/Live/JetsiLive.vue";
    import RevenueModal from '@/components/widgets/RevenueModal.vue';
    import LiveSessionsWidget from '@/components/widgets/LiveSessionsWidget.vue';
    import ReviewsRatingsWidget from '@/components/widgets/ReviewsRatingsWidget.vue'; 
    import UserStatisticsWidget from '@/components/widgets/UserStatisticsWidget.vue';
    import BookPerformanceWidget from '@/components/widgets/BookPerformanceWidget.vue';
    import CoursePerformanceWidget from '@/components/widgets/CoursePerformanceWidget.vue';

    const sidebarStore = useSidebarStore();
    const { sidebarCollapsed, } = storeToRefs(sidebarStore);
     
    const activeFilter = ref('allTime');
    const startSelectedSchedule = ref(null);
 
    const revenueData = ref({
        today: 0,
        month: 0,
        total: 0,
        courseSell: 0,
        bookSell: 0,
        liveSell: 0,  
    });  
    
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

    const changeFilterType = (filterType) =>{
        activeFilter.value = filterType;
        fetchTransactions();
    }

    function joinSession(newSession) {
        startSelectedSchedule.value = newSession;
    }

    onMounted(()=>{
        fetchTransactions();
    })
</script>
<template>
     <div v-if="startSelectedSchedule" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="relative w-full h-full min-h-[600px]">
            <JetsiLive :startSelectedSchedule="startSelectedSchedule" @closeStream="startSelectedSchedule = null" />
        </div>
    </div>
    <!-- Main container -->
    <div v-else class="min-h-screen overflow-hidden w-full bg-gray-100 text-gray-900 transition-colors duration-300">
        <!-- Top Navigation Bar -->

        <div class="">
            <div 
                :class="{
                        'sm:grid-cols-2' : sidebarCollapsed,
                    }"
                class="grid lg:grid-cols-2 gap-4 my-6">
                <RevenueModal 
                    :revenueData="revenueData" 
                    @filterCourse="changeFilterType"/>
                <UserStatisticsWidget />
            </div>

            <div 
                :class="{
                        'sm:grid-cols-2' : sidebarCollapsed,
                    }"
                class="grid lg:grid-cols-2 gap-4 my-6">
                <CoursePerformanceWidget/>
                <BookPerformanceWidget />
            </div>

            <div 
                :class="{
                        'sm:grid-cols-2' : sidebarCollapsed,
                    }"
                class="grid lg:grid-cols-2 gap-4 my-6">
                <LiveSessionsWidget
                    @joinSession="joinSession"/>
                <ReviewsRatingsWidget />
            </div> 
        </div>

        <!-- Revenue Modal -->
        
    </div>
</template>

 
