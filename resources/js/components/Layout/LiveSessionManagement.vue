<script setup>
import Axios from 'axios';
import { ref, nextTick, onMounted } from 'vue';

import JetsiLive from '@/components/Live/JetsiLive.vue';
import AddSchedule from "@/components/Live/AddSchedule.vue";

const sessions = ref([]);
const users = ref([]);
const showAddSchedule = ref(false); 
const selectedRoom = ref(null);

const currentAttendance = ref(25);
const maxAttendance = ref(50);

const showScheduleModal = ref(false);

const newSession = ref({
    title: "",
    dateTime: "",
    duration: 60,
});

function getMySchedules() {
    Axios
        .get('/api/my-schedule')
        .then(res => {
            sessions.value = res.data.data
        })
}

function getMyParticipants() {
    Axios
        .get('/api/my-participants')
        .then(res => {
            users.value = res.data.data
        })
}

function joinSession(room) { 
    selectedRoom.value = room;
}

onMounted(() => {
    getMySchedules();
    getMyParticipants();
})

const selectedSession = ref(null); 

function editSession(session) {
    if (!session) {
        alert("Please select a session to edit.");
        return;
    }
    selectedSession.value = { ...session };
    showAddSchedule.value = true;
}
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-white shadow p-4 flex flex-col md:flex-row justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Live Sessions & Student Engagement</h1>
                <p class="text-sm text-gray-600">Monitor, host, and engage in live learning sessions</p>
            </div>
        </header>

        <div class="my-12">

            <div v-if="selectedRoom"
                class="w-full h-full flex items-center justify-center bg-white animate-fadeIn z-50">
                <div class="relative w-full h-full"> 
                     <JetsiLive
                        :selectedRoom="selectedRoom" 
                        @closeStream="selectedRoom = false"/>
                </div>
            </div>
            <!-- Main Content -->
            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                <!-- Left Panel: Session List -->
                <aside class="md:col-span-1 bg-white rounded shadow p-4">
                    <div class="flex flex-1 justify-between">
                        <h2 class="text-xl font-semibold mb-4">Sessions</h2>
                        <button 
                            @click="showAddSchedule = true"
                            class="bg-lime-700 self-center text-white px-4 py-1 rounded hover:bg-lime-800 transition">
                            Add Schedule
                        </button>
                    </div>
                    <ul class="h-72 overflow-hidden overflow-y-auto scrollbar">
                        <li v-for="session in sessions" :key="session.id"
                            class="mb-4 border p-3 rounded hover:shadow transition-shadow duration-200">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold">{{ session.day }}</h3>
                                    <p class="text-sm my-2 text-gray-500">{{ session.schedule_time }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="editSession(session)"
                                        class="text-gray-500 hover:text-lime-600 transition-colors">
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
                                <span class="text-sm text-gray-600">{{ session.class_name }}</span>
                            </div>
                        </li>
                    </ul>
                </aside>

                <!-- Central Panel: Live Video & Chat -->
                <main class="md:col-span-2 bg-white rounded shadow p-4 flex flex-col">
                    <!-- Live Video Preview -->
                    <div class="relative">
                        <div class="bg-black rounded overflow-hidden">
                            <!-- Embedded Video (demo link) -->
                            <div class="aspect-w-16 aspect-h-9">
                                <JetsiLive v-if="false" />
                            </div>
                        </div>
                        <!-- Live Indicator -->
                        <div
                            class="absolute top-2 left-2 bg-red-600 text-white px-2 py-1 rounded text-xs flex items-center space-x-1">
                            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-white opacity-75"></span>
                            <span>Live</span>
                        </div>
                        <!-- Attendance Tracker -->
                        <div class="absolute bottom-2 right-2 bg-gray-800 text-white px-2 py-1 rounded text-xs">
                            Attendance: {{ currentAttendance }} / {{ maxAttendance }}
                        </div>
                    </div>

                    <!-- participants -->
                    <div>
                        <div class="w-full h-72 overflow-auto mt-12 scrollbar">
                            <table class="w-full" v-if="users.length">
                                <thead>
                                    <tr class="bg-gray-100 py-2 border-b border-gray-400">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users" :key="user.id" class="cursor-pointer">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center gap-2">
                                            <img :src="user.profile" alt="avatar" class="w-8 h-8 rounded-full mr-3" />
                                            <div class="flex flex-col">
                                                <h1 class="font-medium capitalize">{{ user.first_name }} {{ user.middle_name
                                                    }}</h1>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap  text-sm text-gray-500">
                                            <div class="text-sm text-gray-700">{{ user.email }}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </main>
            </div>
        </div>

        <!-- Overlay: Add Schedule Form -->
        <div v-if="showAddSchedule"
            class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 animate-fadeIn z-50">
            <div class="bg-white p-4 rounded-lg w-full mx-4 max-w-md sm:max-w-lg md:max-w-xl relative">
                <!-- Close Button -->
                <button @click="showAddSchedule = false"
                    class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 focus:outline-none" title="Close">
                    <i class="fas fa-times text-2xl"></i>
                </button>
                <AddSchedule 
                    :selectedSession="selectedSession"/>
            </div>
        </div>
 
    </div>
 
</template>

<style scoped>
/* Fade transition for modal */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.jitsi-container {
    @apply max-w-4xl mx-auto  bg-white rounded-lg shadow-lg;
    min-height: 600px;
}
</style>