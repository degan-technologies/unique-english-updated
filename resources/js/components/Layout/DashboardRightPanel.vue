<template>
    <div class="bg-white shadow-lg rounded-lg p-4 space-y-6 w-80">
      <!-- Quick Stats Section -->
      <div class="quick-stats">
        <h3 class="text-xl font-bold mb-3">Quick Stats</h3>
        <ul class="space-y-2">
          <li class="flex justify-between">
            <span>Active Users:</span>
            <span class="font-semibold text-blue-600">1,245</span>
          </li>
          <li class="flex justify-between">
            <span>New Orders:</span>
            <span class="font-semibold text-green-600">320</span>
          </li>
          <li class="flex justify-between">
            <span>Total Revenue:</span>
            <span class="font-semibold text-purple-600">$12,540</span>
          </li>
        </ul>
      </div>
  
      <!-- Messages Section -->
      <div class="messages">
        <h3 class="text-xl font-bold mb-3">Messages</h3>
        <div class="space-y-3">
          <div class="message p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
            <p class="font-semibold">John Doe</p>
            <p class="text-sm text-gray-600 truncate">Hey, can we schedule a call?</p>
          </div>
          <div class="message p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
            <p class="font-semibold">Jane Smith</p>
            <p class="text-sm text-gray-600 truncate">The report is ready for review.</p>
          </div>
        </div>
      </div>
  
      <!-- Live Sessions Section -->
      <div class="live-sessions">
        <h3 class="text-xl font-bold mb-3">Live Sessions</h3>
        <ul class="space-y-3">
          <li class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
            <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
            <span class="truncate">Session 1: Product Demo</span>
          </li>
          <li class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
            <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
            <span class="truncate">Session 2: Q&A with CEO</span>
          </li>
        </ul>
      </div>
  
      <!-- Alerts Section -->
      <div class="alerts">
        <h3 class="text-xl font-bold mb-3">Alerts</h3>
        <ul class="space-y-3">
          <li class="p-3 bg-red-50 rounded-lg hover:bg-red-100 transition duration-200">
            <p class="font-semibold text-red-600">Server Down!</p>
            <p class="text-sm text-gray-600">Our main server is down. We're working on it.</p>
          </li>
          <li class="p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition duration-200">
            <p class="font-semibold text-yellow-600">Update Available</p>
            <p class="text-sm text-gray-600">New update available. Please refresh the page.</p>
          </li>
        </ul>
      </div>
    </div>
  </template>
  
  <script setup>
  // No additional script is needed for static content.
  // You can import reactive data or methods if needed.
  </script>
  
  <style scoped>
  /* Additional styling can be added here if necessary */
  </style>
  