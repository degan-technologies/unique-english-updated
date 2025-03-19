<template>
    <button
      :class="computedClass"
      v-bind="props"
      :disabled="disabled"
    >
      <slot></slot> <!-- Use slot for button content -->
    </button>
  </template>
  
  <script setup>
  import { defineProps, computed } from 'vue';
  
  // Define the props for the component
  const props = defineProps({
    className: {
      type: String,
      default: '',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    // Additional button attributes can be passed here
  });
  
  // Computed property for dynamic classes
  const computedClass = computed(() => {
    return `inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ${
      props.disabled ? 'opacity-25' : ''
    } ${props.className}`;
  });
  </script>
  