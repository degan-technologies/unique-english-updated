<template>
    <div
      class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg transform transition-all duration-300 hover:scale-105 hover:shadow-xl"
    >
      <div class="flex items-center space-x-4">
        <div class="text-4xl">{{ icon }}</div>
        <div>
          <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">{{ title }}</h3>
          <p class="text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
        </div>
      </div>
      <p class="mt-4 text-3xl font-bold text-lime-500">{{ value }}</p>
    </div>
  </template>
  
  <script setup>
  defineProps({
    title: String,
    value: String,
    subtitle: String,
    icon: String,
  });
  </script>
  