<template>
    <div>
      <canvas ref="pieChartCanvas"></canvas>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, watch, nextTick } from 'vue';
  import { Chart, registerables } from 'chart.js';
  
  Chart.register(...registerables);
  
  const props = defineProps({
    data: Array // Expects [{ label: "Uptime", value: 99, color: "green" }, { label: "Downtime", value: 1, color: "red" }]
  });
  
  const pieChartCanvas = ref(null);
  let pieChartInstance = null;
  
  const renderChart = async () => {
    if (!pieChartCanvas.value) return; // 🛑 Prevent rendering if canvas is not yet available
  
    await nextTick(); // 🛠 Ensure DOM updates before accessing canvas
  
    if (pieChartInstance) {
      pieChartInstance.destroy();
    }
  
    pieChartInstance = new Chart(pieChartCanvas.value.getContext('2d'), {
      type: 'pie',
      data: {
        labels: props.data.map(item => item.label),
        datasets: [
          {
            data: props.data.map(item => item.value),
            backgroundColor: props.data.map(item => item.color),
            hoverOffset: 10
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true, position: 'bottom' },
          tooltip: { enabled: true }
        }
      }
    });
  };
  
  watch(() => props.data, async () => {
    await nextTick(); // Ensure DOM is updated before accessing canvas
    renderChart();
  }, { deep: true });
  
  onMounted(async () => {
    await nextTick(); // Ensure the canvas is ready before rendering
    renderChart();
  });
  </script>
  
  <style scoped>
  canvas {
    width: 100%;
    height: 200px;
  }
  </style>
  