<template>
    <div>
      <canvas ref="lineChartCanvas"></canvas>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, watch } from 'vue';
  import { Chart, registerables } from 'chart.js';
  
  Chart.register(...registerables);
  
  const props = defineProps({
    data: Array // Accepts an array of numbers for plotting
  });
  
  const lineChartCanvas = ref(null);
  let lineChartInstance = null;
  
  const renderChart = () => {
    if (lineChartInstance) {
      lineChartInstance.destroy();
    }
  
    lineChartInstance = new Chart(lineChartCanvas.value, {
      type: 'line',
      data: {
        labels: props.data.map((_, i) => `T-${props.data.length - i}`),
        datasets: [
          {
            label: 'Response Time (ms)',
            data: props.data,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true }
        },
        scales: {
          x: { display: true, grid: { display: false } },
          y: { display: true, grid: { color: 'rgba(200, 200, 200, 0.2)' } }
        }
      }
    });
  };
  
  watch(props.data, renderChart, { deep: true });
  
  onMounted(() => {
    renderChart();
  });
  </script>
  
  <style scoped>
  canvas {
    width: 100%;
    height: 200px;
  }
  </style>
  