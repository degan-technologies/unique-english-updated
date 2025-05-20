<script setup>
import { ref, computed } from 'vue'
import ManagePlan from '@/components/Live/ManagePlan.vue'
import LiveRooms from '@/components/Layout/LiveRooms.vue';
import PrivateClass from '@/components/Layout/PrivateClass.vue';

const selectedTab = ref('room')
const toggleAddButton = ref(false)
const searchQuery = ref("");
const searchItem = ref(selectedTab.value);

const selectedTabComponent = computed(() => {
    if (selectedTab.value === 'room') {
        return LiveRooms;
    }else if (selectedTab.value === 'plan') {
        return ManagePlan;
    } else {
        return PrivateClass;
    }
})
</script>

<template>
    <div class="w-full mx-auto bg-gray-100">
        <header
            class="bg-white shadow px-6 py-4 flex-col flex lg:flex-row iteems-left lg:items-center lg:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-lime-700">
                    Schedule Management
                </h1>
                <p class="text-xs sm:text-sm text-gray-500">
                    Manage Schedule &amp; Plan
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center">
                <nav class="text-sm" aria-label="Breadcrumb">
                    <ol class="list-reset flex text-gray-600">
                        <li>
                            <a href="#" class="hover:underline">Home</a>
                        </li>
                        <li>
                            <span class="mx-2">/</span>
                        </li>
                        <li class="font-medium">{{ selectedTab }}</li>
                    </ol>
                </nav>

                <div class="ml-6 relative">
                    <input type="text" v-model="searchQuery" :placeholder="`Search  ${searchItem}...`"
                        class="border border-gray-300 rounded-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M12.9 14.32a8 8 0 111.414-1.414l4.243 4.243a1 1 0 01-1.414 1.414l-4.243-4.243zM8 14a6 6 0 100-12 6 6 0 000 12z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>
            </div>
            <!-- Add Buttons -->
            <div class="w-fit">
                <button v-if="selectedTab === 'room'" @click="toggleAddButton = !toggleAddButton"
                    class="bg-lime-700 hover:bg-lime-800 text-white px-3 py-1 w-fit rounded transition shadow text-sm">
                    Add Rooms
                </button>
                <button v-if="selectedTab === 'plan'" @click="toggleAddButton =  !toggleAddButton"
                    class="bg-lime-700 hover:bg-lime-800 text-white px-3 py-1 w-fit rounded transition shadow text-sm">
                    Add Plan
                </button>
            </div>
        </header>

        <div class="mb-6 flexborder-b w-full">
            <button @click="selectedTab = 'room'"
                :class="selectedTab === 'room' ? 'border-b-2 border-lime-700 text-lime-700 font-bold' : 'text-gray-600 font-medium'"
                class="px-4 py-2">
                Group Class  
            </button>
            <button @click="selectedTab = 'private'"
                :class="selectedTab === 'private' ? 'border-b-2 border-lime-700 text-lime-700 font-bold' : 'text-gray-600 font-medium'"
                class="px-4 py-2">
                Individual Class
            </button>
            <button @click="selectedTab = 'plan'"
                :class="selectedTab === 'plan' ? 'border-b-2 border-lime-700 text-lime-700 font-bold' : 'text-gray-600 font-medium'"
                class="px-4 py-2">
                Plans
            </button>
        </div>
        <div class="mx-auto">
            <component 
            :is="selectedTabComponent"
            :toggleAddButton="toggleAddButton"
             />
        </div>
    </div>
</template>
