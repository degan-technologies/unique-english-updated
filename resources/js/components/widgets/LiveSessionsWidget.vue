<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
 
const schedules = ref([]);  
const isLoading = ref(false);

const emit = defineEmits(["joinSession"]);
  
function getTodaySchedules() {
    isLoading.value = true;
    Axios.get("/api/today-schedules")
        .then((res) => {
            schedules.value = res.data.data;
        })
        .finally(() => {
            isLoading.value = false;
        });
}

function joinNewSession(roomName) {
    emit("joinSession", roomName);
}

onMounted(() => {
     getTodaySchedules()
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg w-full p-6 relative">
        <!-- Header with Icon -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
                <i class="fas fa-video text-lime-700 text-xl"></i>
                <h3 class="text-lg font-bold">Live Sessions Overview</h3>
            </div>
            <button  class="text-gray-500 hover:text-gray-800">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>

        <!-- Live Sessions List -->
        <div v-if="schedules.length" class="max-h-[400px] overflow-y-auto scrollable-container space-y-2 pr-1">
            <div v-for="schedule in schedules" :key="schedule.id"
                class="p-4 rounded-lg border border-gray-200 mb-3 cursor-pointer transition hover:bg-gray-100">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <!-- Live Indicator -->
                        <span  class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                        <h4 class="text-md font-semibold">{{ schedule.day }}</h4>
                    </div>
                    <span class="text-sm text-gray-500">{{ schedule.schedule_time }}</span>
                </div>

                <!-- Instructor & Participants -->
                <div class="flex items-center justify-between">
                    <div v-if="schedule?.class_name == null" class="text-sm text-gray-600 mt-1">
                         <i class="fas fa-user"></i>  <span class="font-medium">{{ schedule.private_instructor_name }}</span>
                        <h1 class="font-bold text-lime-700 py-2">Private Class</h1>
                    </div>
                    <div v-else class="text-sm text-gray-600 mt-1">
                        <div class="flex-col">
                             <i class="fas fa-user"></i>  <span class="font-medium">{{ schedule.group_instructor_name }}</span>
                            <h1 class="font-bold text-lime-700 py-2">Group Class</h1>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-sm py-2 capitalize text-blue-700"> {{ schedule.status }}</h1>
                        <button @click="joinNewSession(schedule.room_name)"
                            :disabled="isStartingSession"
                            class="inline-flex self-center items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 disabled:opacity-75 disabled:cursor-not-allowed">
                            <span v-if="!isStartingSession">Join</span> 
                        </button>
                    </div>
                </div>
                <div class="text-xs capitalize text-gray-500">
                      <i class="fas fa-users"></i>  {{ schedule.student_name }} {{ schedule.class_name }} 
                </div>
            </div>
        </div>

        <!-- No Sessions Placeholder -->
        <div v-else class="text-gray-500 text-center py-6">No live sessions available.</div>
 
    </div>
</template>
