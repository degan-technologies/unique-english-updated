<script setup>
    import { ref, computed } from 'vue';
    import { storeToRefs } from 'pinia';
    import PieChart from '@/components/PieChart.vue';
    
    import { useSidebarStore } from "@/store/useSidebarStore";

    const sidebarStore = useSidebarStore();
    const { sidebarCollapsed, } = storeToRefs(sidebarStore);

    const emit = defineEmits(['filterCourse'])
    const props = defineProps({
        revenueData: Object
    })

    const breakdownType = ref('month');

    const revenueMetrics = computed(() => {
        return [
            {
                title: "Today's",
                value: props.revenueData.today, 
                icon: "fas fa-wallet", 
                Type: 'today'
            },
            {
                title: "This Month",
                value: props.revenueData.month, 
                icon: "fas fa-calendar-alt", 
                Type: 'month'
            },
            {
                title: "Total",
                value: props.revenueData.total, 
                icon: "fas fa-chart-line", 
                Type: 'allTime'
            }
        ];
    });

    const revenueBreakdown = computed(() => {
        const breakdown = [
            { label: "Courses", value: props.revenueData.courseSell, color: "blue" },
            { label: "Books", value: props.revenueData.bookSell, color: "green" },
            { label: "Live Class", value: props.revenueData.liveSell, color: "orange" }
        ];
    
        const isEmpty = breakdown.every(item => item.value === 0); 
        if (isEmpty) {
            return breakdown.map(item => ({ ...item, color: "red" }));
        }

        return breakdown;
    });

    const revenueBreakdownCopy = computed(() => [...revenueBreakdown.value]);

    function selectFilterType(filterType) {
        breakdownType.value = filterType;
        emit('filterCourse', filterType);
    } 
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg w-full p-6 relative">
        <div class="flex items-center space-x-2 mb-4">
            <i class="fas fa-dollar-sign text-lime-700 text-lg"></i>
            <h3 class="text-xl font-bold text-lime-700">Revenue Summary</h3>
        </div>
 
        <div 
            :class="{
                'sm:grid-cols-2':sidebarCollapsed
            }"
            class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div v-for="metric in revenueMetrics" :key="metric.title" 
                @click="selectFilterType(metric.Type)" 
                :class="{
                    'bg-lime-500': breakdownType === metric.Type,
                    'bg-lime-100': breakdownType !== metric.Type
                }" 
                class="p-4 rounded-lg transition-all cursor-pointer shadow-sm border border-lime-700 text-lime-900">
                <h4 class="text-sm font-semibold flex items-center space-x-2">
                    <i :class="metric.icon"></i>
                    <span>{{ metric.title }}</span>
                </h4>
                <p class="text-xl font-bold">
                    <span class="animate-number">{{ metric.value }}</span>
                </p>
            </div>
        </div>
 
        <div class="gap-4 mt-8"> 
            <div class="bg-gray-100 rounded-lg p-4">
                <h4 class="text-sm font-semibold mb-4 text-lime-700">
                    Revenue Breakdown {{ breakdownType }}
                </h4>
                <PieChart :data="revenueBreakdownCopy"/>
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
