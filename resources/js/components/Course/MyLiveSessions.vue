<script setup>
import Axios from 'axios';
import { ref, onMounted } from 'vue';

const sessions = ref([]);
const selectedRoom = ref(false);

import JetsiLive from '@/components/Live/JetsiLive.vue';

function getMySchedules() {
    Axios
        .get('/api/student-schedule')
        .then(res => {
            sessions.value = res.data.data
        })
}

function joinSession(room) { 
    selectedRoom.value = room;
}

onMounted(() => {
    getMySchedules();
});
</script>
<template>
    <div>
        <div v-if="selectedRoom"
            class="w-full h-full flex items-center justify-center bg-white animate-fadeIn z-50">
            <div class="relative w-full h-full"> 
                <JetsiLive
                    :selectedRoom="selectedRoom" 
                    @closeStream="selectedRoom = false"/>
            </div>
        </div>
        <div v-else >
            <div v-for="session in sessions" :key="session.id"
                class="mb-4 border p-3 max-w-[30%] rounded hover:shadow transition-shadow duration-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold">{{ session.day }}</h3>
                        <p class="text-sm my-2 text-gray-500">{{ session.schedule_time }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="editSession(session)" class="text-gray-500 hover:text-lime-600 transition-colors">
                            <i class="fas fa-edit"></i>
                        </button>
                        <span :class="session?.color" class="px-2 py-1 text-xs capitalize rounded">
                            {{ session.status }}
                        </span>
                    </div>
                </div>
                <div class="mt-2 flex justify-between items-center">
                    <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors"
                        @click="joinSession(session?.room_name)">
                        Start
                    </button> 
                </div>
            </div>
        </div>
    </div>
</template>
