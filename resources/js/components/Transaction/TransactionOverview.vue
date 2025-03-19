<script setup>
 import Chart from 'chart.js/auto'
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    revenue: Object,
})

const getMonths = Array.from({ length: 6 }, (_, i) => {
  const date = new Date()
  date.setMonth(date.getMonth() - (5 - i))
  return date.toLocaleString('default', { month: 'short' })
})

// Helper function: Aggregate transactions for each month based on keys in "YYYY-MM" format.
const getDatasetData = () => {
  // Get transactionSummary object or default to an empty object.
  const summary = props.revenue?.transactionSummary || {}
  return getMonths.map((_, index) => {
    const date = new Date()
    date.setMonth(date.getMonth() - (5 - index))
    // Use the prefix in "YYYY-MM" format.
    const monthPrefix = date.toISOString().slice(0, 7)
    let sum = 0
    // Loop through all keys in summary; add value if key starts with the month prefix.
    Object.keys(summary).forEach((key) => {
      if (key.startsWith(monthPrefix)) {
        sum += summary[key]
      }
    })
    return sum
  })
}

onMounted(() => {     
    const ctx = document.getElementById('earningsChart')
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: getMonths,
            datasets: [
                {
                label: 'Revenue',
                data: getDatasetData(),
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                mode: 'index',
                intersect: false,
                },
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false,
            },
            scales: {
                y: {
                beginAtZero: true,
                },
            },
        },
    })
})

onUnmounted(()=>{
    const chart = Chart.getChart('earningsChart');
    if (chart) {
        chart.destroy();
    }
})

</script>
<template>
    <div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-book-reader text-2xl mx-3 font-bold text-blue-500"></i>
                    <div>
                        <h3 class="text-gray-700 text-sm">Course Sales</h3>
                        <p class="text-gray-500 text-md font-bold">${{ revenue.courseSales }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-book text-2xl mx-3 font-bold text-green-500"></i>
                    <div>
                        <h3 class="text-gray-700 text-sm">Book Sales</h3>
                        <p class="text-gray-500 text-md font-bold">${{ revenue.bookSales }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-video text-2xl mx-3 font-bold text-purple-500"></i>
                    <div>
                        <h3 class="text-gray-700 text-sm">Live Classes</h3>
                        <p class="text-gray-500 text-md font-bold">${{ revenue.virtualClasses }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-dollar-sign text-2xl mx-3 font-bold text-indigo-500"></i>
                    <div>
                        <h3 class="text-gray-700 text-sm">Overall Revenue</h3>
                        <p class="text-gray-500 text-md font-bold">${{  revenue.totalSell }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Earnings Forecast</h2>
            <canvas id="earningsChart"></canvas>
        </div>
    </div>
</template>