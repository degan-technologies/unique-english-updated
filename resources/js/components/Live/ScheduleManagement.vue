<script setup>
import { ref, computed } from 'vue'
import ManagePlan from '@/components/Live/ManagePlan.vue' 
import LiveRooms from '@/components/Layout/LiveRooms.vue';

const selectedTab = ref('room')

const selectedTabComponent = computed(() => { 
  return  selectedTab.value ==='plan' ? ManagePlan : LiveRooms;
})
</script>

<template>
    <div class="w-full mx-auto bg-gray-100">
        <div class="w-full bg-white py-4 shadow mb-6">
            <h1 class="text-xl font-bold text-lime-700 pl-4">
                Schedule Management
            </h1>
            <p class="text-gray-600 pl-4">
                Manage Schedule & Plan
            </p>
        </div>
        <div class="mb-6 flex space-x-2 border-b mx-6">
            <button @click="selectedTab = 'room'"
                :class="selectedTab === 'room' ? 'border-b-2 border-lime-700 text-lime-700 font-bold' : 'text-gray-600 font-medium'"
                class="px-4 py-2">
                Rooms
            </button> 
            <button @click="selectedTab = 'plan'"
                :class="selectedTab === 'plan' ? 'border-b-2 border-lime-700 text-lime-700 font-bold' : 'text-gray-600 font-medium'"
                class="px-4 py-2">
                Plans
            </button>
        </div>
        <div class="mx-6">
            <component :is="selectedTabComponent" />
        </div>
    </div>
</template>
