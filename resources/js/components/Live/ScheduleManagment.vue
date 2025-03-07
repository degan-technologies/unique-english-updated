<template>
  <div class="max-w-7xl mx-auto p-6 bg-gray-100">
    <!-- Top Navigation Bar -->
    <div class="mb-6 flex space-x-4 border-b pb-2">
      <button
        @click="selectedTab = 'schedule'"
        :class="selectedTab === 'schedule' ? 'border-b-2 border-lime-700 text-lime-700 font-bold' : 'text-gray-600 font-medium'"
        class="px-4 py-2"
      >
         Schedules
      </button>
      <button
        @click="selectedTab = 'plan'"
        :class="selectedTab === 'plan' ? 'border-b-2 border-lime-700 text-lime-700 font-bold' : 'text-gray-600 font-medium'"
        class="px-4 py-2"
      >
         Plans
      </button>
    </div>

    <!-- Conditionally Render the Selected Component -->
    <div>
      <component :is="selectedTabComponent" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ManageSchedule from './ManageSchedule.vue'
import ManagePlan from './ManagePlan.vue'

const selectedTab = ref('schedule')

const selectedTabComponent = computed(() => {
  return selectedTab.value === 'schedule' ? ManageSchedule : ManagePlan
})
</script>
