<template>
    <input
      type="checkbox"
      v-bind="props"
      :class="computedClass"
      v-model="checked"
    />
  </template>
  
  <script setup>
  import { defineProps, ref, computed } from 'vue';
  
  // Define the incoming props
  const props = defineProps({
    className: {
      type: String,
      default: ''
    },
    // You can add other standard attributes here if needed (e.g., `disabled`, `checked`, etc.)
  });
  
  // Define reactive state for checked value
  const checked = ref(false);
  
  // Computed property for dynamic classes
  const computedClass = computed(() => {
    return `rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800 ${props.className}`;
  });
  </script>
  