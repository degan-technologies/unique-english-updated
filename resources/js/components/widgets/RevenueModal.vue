<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import LineChart from '@/components/LineChart.vue';
import PieChart from '@/components/PieChart.vue';

// Define the emit for the 'close' event
const emit = defineEmits(['close']);

const props = defineProps({
    revenueData: Object
})

// Revenue Metrics
const revenueMetrics = ref([
    {
        title: "Today's",
        value: props.revenueData.today,
        description: "Revenue generated today",
        icon: "fas fa-wallet",
        details: "Today's revenue details and transactions."
    },
    {
        title: "This Month",
        value: props.revenueData.month,
        description: "Total revenue this month",
        icon: "fas fa-calendar-alt",
        details: "This month's revenue breakdown by day."
    },
    {
        title: "Total",
        value: props.revenueData.total,
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
 
const revenueBreakdownCopy = computed(() => [...revenueBreakdown.value]);


// Open Details Function
const openDetails = (metric) => {
    alert(`Details for ${metric.title}: ${metric.details}`);
};

let intervalId = null;

// Auto-refresh revenue data every 10 seconds
onMounted(() => {
     
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

<template>
   <div
        class="bg-white rounded-lg shadow-lg w-full p-6 relative ">
        <!-- Modal Header -->
        <div class="flex items-center space-x-2 mb-4">
            <i class="fas fa-dollar-sign text-lime-700 text-lg"></i>
            <h3 class="text-xl font-bold text-lime-700">Revenue Summary</h3>
        </div>

        <!-- Revenue Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="metric in revenueMetrics" :key="metric.title"
                class="p-4 rounded-lg transition-all cursor-pointer shadow-sm border border-lime-700 bg-lime-100 text-lime-900"
                @click="openDetails(metric)">
                <h4 class="text-sm font-semibold flex items-center space-x-2">
                    <i :class="metric.icon"></i>
                    <span>{{ metric.title }}</span>
                </h4>
                <p class="text-xl font-bold">
                    <span class="animate-number">{{ metric.value }}</span>
                </p> 
            </div>
        </div>

        <!-- Charts Section -->
        <div class="gap-4 mt-6"> 
            <!-- Revenue Breakdown Pie Chart -->
            <div class="bg-gray-100 rounded-lg p-4">
                <h4 class="text-sm font-semibold mb-2 text-lime-700">Revenue Breakdown</h4>
                <pie-chart :data="revenueBreakdownCopy"></pie-chart>
            </div>
        </div>

    </div>
</template>

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
