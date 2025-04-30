<script setup>
import Axios from 'axios';
import { ref, nextTick, onMounted } from 'vue';

import JetsiLive from '@/components/Live/JetsiLive.vue';

const activeTab = ref("live");
const sessions = ref([]);
const users = ref([]);

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
        .then(res=>{
            sessions.value = res.data.data
        })
} 

function getMyParticipants() {
    Axios
        .get('/api/my-participants')
        .then(res=>{
            users.value = res.data.data
        })
}

function joinSession(session) {
    alert(`Joining session: ${session.title}`);
}
 
function scheduleSession() {
    sessions.value.push({
        id: Date.now(),
        title: newSession.value.title,
        instructor: "You",
        startTime: new Date(newSession.value.dateTime).toLocaleTimeString(),
        status: "scheduled",
        participants: 0,
    });
    newSession.value = { title: "", dateTime: "", duration: 60 };
    showScheduleModal.value = false;
}

onMounted(() => {
    getMySchedules();
    getMyParticipants();
})
</script>


<template>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-white shadow p-4 flex flex-col md:flex-row justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Live Sessions & Student Engagement</h1>
                <p class="text-sm text-gray-600">Monitor, host, and engage in live learning sessions</p>
            </div>
            <nav class="mt-2 md:mt-0">
                <ul class="flex space-x-4">
                    <li>
                        <button class="px-4 py-2 rounded hover:bg-blue-600 transition-colors"
                            :class="activeTab === 'live' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'"
                            @click="activeTab = 'live'">
                            Live Now
                        </button>
                    </li>
                    <li>
                        <button class="px-4 py-2 rounded hover:bg-green-600 transition-colors"
                            :class="activeTab === 'upcoming' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800'"
                            @click="activeTab = 'upcoming'">
                            Upcoming Sessions
                        </button>
                    </li>
                </ul>
            </nav>
        </header>

        <!-- Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
            <!-- Left Panel: Session List -->
            <aside class="md:col-span-1 bg-white rounded shadow p-4">
                <h2 class="text-xl font-semibold mb-4">Sessions</h2>
                <ul class="h-72 overflow-hidden overflow-y-auto scrollbar">
                    <li v-for="session in sessions" :key="session.id"
                        class="mb-4 border p-3 rounded hover:shadow transition-shadow duration-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold">{{ session.day }}</h3>
                                <p class="text-sm text-gray-500">{{ session.schedule_time }}</p> 
                            </div>
                            <div>
                                <span :class="session?.color" class="px-2 py-1 text-xs capitalize rounded">
                                    {{ session.status }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-between items-center">
                            <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors"
                                @click="joinSession(session)">
                                Start
                            </button>
                            <span class="text-sm text-gray-600">{{ 4 }} participants</span>
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
                            <JetsiLive />
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
                        <table class="w-full" v-if="users.length" >
                            <thead>
                                <tr class="bg-gray-100 py-2 border-b border-gray-400"> 
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th> 
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users" 
                                    :key="user.id" 
                                    class="cursor-pointer" >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center gap-2">
                                        <img :src="user.profile" alt="avatar"
                                            class="w-8 h-8 rounded-full mr-3" />
                                        <div class="flex flex-col">
                                            <h1 class="font-medium capitalize">{{ user.first_name }} {{ user.middle_name }}</h1>
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

        <!-- Modal: Schedule New Session -->
        <transition name="fade">
            <div v-if="showScheduleModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded p-6 w-11/12 md:w-1/2">
                    <h2 class="text-xl font-semibold mb-4">Schedule New Session</h2>
                    <form @submit.prevent="scheduleSession()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Session Title</label>
                            <input v-model="newSession.title" type="text"
                                class="w-full border rounded px-3 py-2 focus:outline-none" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Date & Time</label>
                            <input v-model="newSession.dateTime" type="datetime-local"
                                class="w-full border rounded px-3 py-2 focus:outline-none" required />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Duration (minutes)</label>
                            <input v-model="newSession.duration" type="number"
                                class="w-full border rounded px-3 py-2 focus:outline-none" required />
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" class="px-4 py-2 border rounded hover:bg-gray-100 transition-colors"
                                @click="showScheduleModal = false">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                                Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
        <!-- Footer with Quick Access -->
        <footer class="p-4">
            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors"
                @click="showScheduleModal = true">
                Schedule New Session
            </button>
        </footer>
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
</style>