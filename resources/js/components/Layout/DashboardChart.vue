<template>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl">
      <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">{{ title }}</h3>
      <canvas :id="chartId"></canvas>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { Chart, registerables } from 'chart.js';
  
  // Register Chart.js components
  Chart.register(...registerables);
  
  // Define the props for the component
  const props = defineProps({
    title: String,
    chartId: String,
    type: String,
  });
  
  // The data for each chart type (Example data)
  const chartData = {
    line: [50, 75, 150, 200, 300],
    bar: [200, 450, 700, 1200, 1500],
    pie: [10, 20, 30],
  };
  
  // Function to create the chart
  const createChart = (type, data) => {
    new Chart(document.getElementById(props.chartId), {
      type: type,
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May"],
        datasets: [{
          label: 'Chart Data',
          backgroundColor: type === 'pie' ? ["#63b3ed", "#f56565", "#68d391"] : "#63b3ed", // Adjust for pie and other types
          borderColor: "#fff",
          data: data,
        }],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  };
  
  // Create chart when component is mounted
  onMounted(() => {
    createChart(props.type, chartData[props.type]);
  });
  </script>
  
  <style scoped>
  /* Custom styles for charts */
  canvas {
    width: 100% !important;
    height: 300px !important;
  }
  </style>
  