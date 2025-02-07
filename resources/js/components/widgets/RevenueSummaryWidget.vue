<template>
  <div
    class="bg-white rounded-lg shadow hover:shadow-lg transition p-4 sm:p-6 cursor-pointer w-full"
    @click="$emit('openDetail')"
  >
    <!-- Header with icon and title -->
    <div class="flex items-center space-x-2 mb-2">
      <i class="fas fa-dollar-sign text-lime-700 text-2xl"></i>
      <h3 class="text-lg font-bold">Revenue Summary</h3>
    </div>

    <!-- Revenue Metrics -->
    <div class="space-y-2">
      <div>
        <span class="text-sm">Today's Revenue</span>
        <div class="text-2xl font-bold">{{ formatCurrency(data.today) }}</div>
      </div>
      <div>
        <span class="text-sm">This Month's Revenue</span>
        <div class="text-2xl font-bold">{{ formatCurrency(data.month) }}</div>
      </div>
      <div>
        <span class="text-sm">Total Earnings</span>
        <div class="text-2xl font-bold">{{ formatCurrency(data.total) }}</div>
      </div>
    </div>

    <!-- Mini Sparkline Chart -->
    <div class="mt-4">
      <SparklineChart :data="data.trend" />
    </div>
  </div>
</template>

<script setup>
import SparklineChart from '@/components/widgets/SparklineChart.vue';

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
});

// Format numeric values as currency (extend as needed)
function formatCurrency(value) {
  return '$' + value.toLocaleString();
}
</script>

<style scoped>
/* Additional widget-specific styles can be added here */
</style>
