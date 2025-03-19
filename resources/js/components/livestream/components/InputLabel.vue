<template>
    <label :class="computedClass" v-bind="props">
      {{ value || $slots.default() }}
    </label>
  </template>
  
  <script setup>
  import { defineProps, computed } from 'vue';
  
  // Define the props for the component
  const props = defineProps({
    value: {
      type: String,
      default: '',
    },
    className: {
      type: String,
      default: '',
    },
  });
  
  // Computed property for dynamic classes
  const computedClass = computed(() => {
    return `block font-medium text-sm text-gray-700 dark:text-gray-300 ${props.className}`;
  });
  </script>
  