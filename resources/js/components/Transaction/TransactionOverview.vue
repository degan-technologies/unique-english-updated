<script setup>
    import Chart from 'chart.js/auto'
    import { onMounted, onUnmounted, ref, computed, watch } from 'vue';

    const props = defineProps({
        revenue: Object,
    })

    const emit = defineEmits(['changeEarningType']);

    const chartInstance = ref(null)
    
    const courseSalesDisplay = computed(() =>
        props.revenue && props.revenue.courseSales !== undefined
            ? props.revenue.courseSales
            : 0
    )
    const bookSalesDisplay = computed(() =>
        props.revenue && props.revenue.bookSales !== undefined
            ? props.revenue.bookSales
            : 0
    )
    const virtualClassesDisplay = computed(() =>
        props.revenue && props.revenue.virtualClasses !== undefined
            ? props.revenue.virtualClasses
            : 0
    )
    const totalSellDisplay = computed(() =>
        props.revenue && props.revenue.totalSell !== undefined
            ? props.revenue.totalSell
            : 0
    )
    
    const computeLabels = () => {
        if (!props.revenue || !props.revenue.transactionSummary) {
            return [];
        }
        const summary = props.revenue.transactionSummary;
        return Object.keys(summary).sort();
    }

    const computeDatasetData = () => {
        if (!props.revenue || !props.revenue.transactionSummary) {
            return [];
        }
        const summary = props.revenue.transactionSummary;
        return computeLabels().map(key => summary[key]);
    } 

    const initChart = () => {
        const ctx = document.getElementById('earningsChart');
        if (!ctx) return;
        chartInstance.value = new Chart(ctx, {
            type: 'line',
            data: {
                labels: computeLabels(),
                datasets: [
                    {
                        label: 'Revenue',
                        data: computeDatasetData(),
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
        });
    }

    function changeEarning() {
        emit('changeEarningType');
    }

    onMounted(() => {
        initChart();
    });

    onUnmounted(() => {
        if (chartInstance.value) {
            chartInstance.value.destroy();
        }
    });
</script>

<template>
    <div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-book-reader text-2xl mx-3 font-bold text-blue-500"></i>
                    <div>
                        <h3 class="text-gray-700 text-sm">Course Sales</h3>
                        <p class="text-gray-500 text-md font-bold">${{ courseSalesDisplay }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-book text-2xl mx-3 font-bold text-green-500"></i>
                    <div>
                        <h3 class="text-gray-700 text-sm">Book Sales</h3>
                        <p class="text-gray-500 text-md font-bold">${{ bookSalesDisplay }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-video text-2xl mx-3 font-bold text-purple-500"></i>
                    <div>
                        <h3 class="text-gray-700 text-sm">Live Classes</h3>
                        <p class="text-gray-500 text-md font-bold">${{ virtualClassesDisplay }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center">
                    <i class="fas fa-dollar-sign text-2xl mx-3 font-bold text-indigo-500"></i>
                    <div>
                        <h3 class="text-gray-700 text-sm">Overall Revenue</h3>
                        <p class="text-gray-500 text-md font-bold">${{ totalSellDisplay }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Earnings</h2>
            <p @click="changeEarning()">monthly</p>
            <canvas id="earningsChart"></canvas>
        </div>
    </div>
</template>