<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
import { useToast } from "vue-toastification";

import JetsiLive from "@/components/Live/JetsiLive.vue";
import AddSchedule from "@/components/Live/AddSchedule.vue";
import Spinner from "@/components/Layout/Spinner.vue";
import Privatechedule from '@/components/Layout/Privatechedule.vue';

const toast = useToast();
const sessions = ref([]);
const users = ref([]);
const showAddSchedule = ref(false);
const selectedRoom = ref(null);
const selectedSession = ref(null);
const isLoading = ref(true);
const isStartingSession = ref(false);
const activeTab = ref('group');

const currentPage = ref(1);
const rowsPerPage = ref(10);
const rowsPerPageOptions = [5, 10, 15, 20];
const pagination = ref("");
const totalPages = ref(0);

const fetchSessions = async () => {
    try {
        const response = await Axios.get("/api/my-schedule");
        sessions.value = response.data.data.map(session => ({
            ...session,
            statusColor: getStatusColor(session.status)
        }));
    } catch (error) {
        toast.error("Failed to load sessions", { timeout: 3000 });
        console.error("Error fetching sessions:", error);
    }
};

const fetchParticipants = async (page = 1) => {
    try {
        const res = await Axios.get(`/api/my-get-students?page=${page}`,  {
                params:{ 
                    rowsPerPageOptions: rowsPerPage.value,
                }
            });
        users.value = res.data.data;
        pagination.value = res.data.pagination;
        totalPages.value = res.data.pagination.last_page;
        currentPage.value = res.data.pagination.current_page;
    } catch (error) {
        toast.error("Failed to load participants", { timeout: 3000 });
        console.error("Error fetching participants:", error);
    }
};

function getStatusColor(status) {
    const statusMap = {
        active: "bg-green-100 text-green-800",
        pending: "bg-yellow-100 text-yellow-800",
        completed: "bg-blue-100 text-blue-800",
        cancelled: "bg-red-100 text-red-800"
    };
    return statusMap[status.toLowerCase()] || "bg-gray-100 text-gray-800";
}

function joinSession(room) {
    if (!room) {
        toast.warning("No room selected", { timeout: 3000 });
        return;
    }
    isStartingSession.value = true;
    selectedRoom.value = room;
    isStartingSession.value = false;
}

function editSession(session) {
    if (!session) {
        toast.warning("Please select a session to edit", { timeout: 3000 });
        return;
    }
    selectedSession.value = session;
    showAddSchedule.value = true;
}

function handleScheduleAdded() {
    showAddSchedule.value = false;
    fetchSessions();
}

function onNextPage() {
    if (currentPage.value == totalPages.value) return;

    fetchParticipants(currentPage.value + 1);
}

function onPreviousPage() {
    if (currentPage.value <= 1) return;

    fetchParticipants(currentPage.value - 1);
}

function coursePerPage(amount) {
    rowsPerPage.value = amount;
    fetchParticipants(currentPage.value);
} 

onMounted(async () => {
    try {
        await Promise.all([fetchSessions(), fetchParticipants()]);
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Live Sessions
                        </h1> 
                        <nav class="flex mt-3" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2">
                                <li>
                                    <div class="flex items-center">
                                        <a href="#"
                                            class="text-sm font-medium text-gray-500 hover:text-gray-700">Dashboard</a>
                                    </div>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        /
                                        <span class="ml-2 text-sm font-medium text-gray-700">Live Sessions</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                    </div>
                    <div v-if="activeTab === 'group'" class="mt-4 flex md:mt-0 md:ml-4">
                        <button @click="showAddSchedule = true" type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Add Schedule
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="my-4">
            <nav class="flex border-b">
                <button
                    :class="{
                            'border-lime-700 text-lime-700 border-b-2': activeTab === 'group',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'group',
                        }"
                    @click="activeTab = 'group'"
                    class="px-4 py-2 font-medium focus:outline-none" >
                    Group Live
                </button>
                <button
                    :class="{
                            'border-lime-700 text-lime-700  border-b-2': activeTab === 'private',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'private',
                        }" 
                    @click="activeTab = 'private'"
                    class="px-4 py-2 font-medium focus:outline-none" >
                    Private Live
                </button> 
            </nav>
        </div>

       <div v-if="activeTab === 'group'">
             <!-- Main Content -->
            <main class="max-w-7xl mx-auto py-6">
                <div v-if="isLoading" class="flex justify-center items-center h-64">
                    <Spinner />
                </div>

                <template v-else>
                    <!-- When a room is selected -->
                    <div v-if="selectedRoom" class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="relative w-full h-full min-h-[600px]">
                            <JetsiLive :selectedRoom="selectedRoom" @closeStream="selectedRoom = null" />
                        </div>
                    </div>

                    <!-- Default view -->
                    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Sessions List -->
                        <div class="lg:col-span-1">
                            <div class="bg-white shadow rounded-lg overflow-hidden">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900">Upcoming Sessions</h3>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ sessions.length }} total
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-white overflow-y-auto" style="max-height: 600px;">
                                    <ul class="divide-y divide-gray-200">
                                        <li v-for="session in sessions" :key="session.id"
                                            class="px-4 py-4 hover:bg-gray-50 transition-colors duration-150">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-shrink-0">
                                                        <div
                                                            class="h-10 w-10 rounded-full bg-lime-100 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-lime-600"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="1.5"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-medium text-gray-900 truncate">{{ session.day
                                                        }}</p>
                                                        <p class="text-sm text-gray-500 truncate">{{ session.schedule_time
                                                        }}</p>
                                                        <p class="text-xs text-gray-500 mt-1">{{ session.class_name }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col items-end space-y-2">
                                                    <span  
                                                        class="px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                        {{ session.status }}
                                                    </span>
                                                    <div class="flex space-x-2">
                                                        <button @click="editSession(session)"
                                                            class="text-gray-400 hover:text-lime-600 focus:outline-none"
                                                            title="Edit session">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="1.5"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <button @click="joinSession(session.room_name)"
                                                            :disabled="isStartingSession"
                                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 disabled:opacity-75 disabled:cursor-not-allowed">
                                                            <span v-if="!isStartingSession">Start</span>
                                                            <svg v-else class="animate-spin -ml-1 mr-2 h-3 w-3 text-white"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                    stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor"
                                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div v-if="sessions.length === 0" class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No sessions</h3>
                                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new session.</p>
                                        <div class="mt-6">
                                            <button @click="showAddSchedule = true" type="button"
                                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
                                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                New Session
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Area -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Video Preview -->
                            <div class="bg-white shadow rounded-lg overflow-hidden">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Live Preview</h3>
                                </div>
                                <div class="p-4">
                                    <div
                                        class="aspect-w-16 aspect-h-9 bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
                                        <div class="text-center p-6">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No active session</h3>
                                            <p class="mt-1 text-sm text-gray-500">Select a session from the list to start
                                                streaming.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Participants List -->
                            <div class="bg-white shadow rounded-lg overflow-hidden">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900">Participants</h3>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ users.length }} registered
                                        </span>
                                    </div>
                                </div>
                                <div class="overflow-x-auto min-h-48">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    User</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Email</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Type</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="user in users" :key="user.id"
                                                class="hover:bg-gray-50 cursor-pointer transition-colors duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <img class="h-10 w-10 rounded-full" :src="user.profile"
                                                                alt="User avatar">
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900 capitalize">
                                                                {{ user.first_name }} {{ user.middle_name }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">{{ user.class_name ? 'Group' : 'Individual' }}</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div v-if="users.length === 0" class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No participants</h3>
                                        <p class="mt-1 text-sm text-gray-500">Participants will appear here once registered.
                                        </p>
                                    </div>
                                </div>

                                <!-- Pagination Footer -->
                                <div class="p-4 bg-white flex flex-row items-center justify-between">
                                    <!-- Rows Per Page Selector -->
                                    <div class="flex flex-wrap space-x-2 items-center">
                                        <span class="text-sm text-gray-600">Courses per page:</span>
                                        <div v-for="option in rowsPerPageOptions" :key="option" @click="coursePerPage(option)"
                                            class="border border-gray-300 rounded-md px-2 py-2 text-sm cursor-pointer transition-all duration-200"
                                            :class="{
                                                'bg-blue-500 text-white font-bold':
                                                    rowsPerPage === option,
                                                'bg-white text-gray-700 hover:bg-gray-200':
                                                    rowsPerPage !== option,
                                            }">
                                            {{ option }}
                                        </div>
                                    </div>

                                    <!-- Pagination Controls -->
                                    <div class="flex items-center space-x-3">
                                        <button @click="onPreviousPage()" :disabled="currentPage === 1"
                                            class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                            Prev
                                        </button>

                                        <span class="text-sm text-gray-600">
                                            Page {{ currentPage }} of {{ totalPages }}
                                        </span>

                                        <button @click="onNextPage()" :disabled="currentPage === totalPages"
                                            class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                            Next
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </main>

            <!-- Overlay: Add Schedule Form -->
            <div v-if="showAddSchedule"
                class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 animate-fadeIn z-50">
                <AddSchedule v-if="showAddSchedule" :selectedSession="selectedSession" @close="showAddSchedule = false"
                        @schedule-updated="handleScheduleAdded" />
            </div>
       </div>

       <div v-else>
            <Privatechedule/>
       </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Animation for status indicator */
@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Spinner animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Transition effects */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>