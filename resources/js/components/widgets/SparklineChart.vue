<template>
    <svg :width="width" :height="height" class="w-full h-12">
      <!-- Create a polyline based on computed points -->
      <polyline :points="points" fill="none" stroke="#4CAF50" stroke-width="2" />
    </svg>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  
  const props = defineProps({
    data: {
      type: Array,
      default: () => [],
    },
    width: {
      type: Number,
      default: 200,
    },
    height: {
      type: Number,
      default: 40,
    },
  });
  
  // Compute maximum and minimum values from the data for scaling.
  const maxValue = computed(() => Math.max(...props.data));
  const minValue = computed(() => Math.min(...props.data));
  const range = computed(() => maxValue.value - minValue.value || 1);
  
  // Generate a string of points for the polyline based on the data.
  const points = computed(() =>
    props.data
      .map((val, index) => {
        const x = (index / (props.data.length - 1)) * props.width;
        const y = props.height - ((val - minValue.value) / range.value) * props.height;
        return `${x},${y}`;
      })
      .join(' ')
  );
  </script>
  