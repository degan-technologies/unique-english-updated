<script setup>
import { ref, onMounted, watch } from "vue";
import Popper from "vue3-popper";
import Axios from "axios";
import { useToast } from "vue-toastification";

import AddPrivateSchedule from "@/components/Live/AddPrivateSchedule.vue";
import JetsiLive from "@/components/Live/JetsiLive.vue";
import Spinner from "@/components/Layout/Spinner.vue";

const toast = useToast();
const showAddModal = ref(false);
const users = ref([]);
const instructors = ref([]);

const showAddSchedule = ref(false);
const selectedSession = ref(null);
const studentId = ref(null);
const expandedUser = ref(null);

const currentPage = ref(1);
const rowsPerPage = ref(10);
const rowsPerPageOptions = [5, 10, 15, 20];
const pagination = ref("");
const totalPages = ref(0);

const isStartingSession = ref(false);
const selectedRoom = ref(null);

const props = defineProps({
    toggleAddButton: Boolean
});

const fetchUsers = async (page = 1) => {
    try {
        const res = await Axios.get(`/api/my-private-students-schedule?page=${page}`, {
            params: {
                rowsPerPageOptions: rowsPerPage.value,
            }
        });
        users.value = res.data.data;
        pagination.value = res.data.pagination;
        totalPages.value = res.data.pagination.last_page;
        currentPage.value = res.data.pagination.current_page;
    } catch (error) {
        console.error("Error fetching users:", error);
        toast.error("Failed to fetch users");
    }
};

const fetchInstructors = async () => {
    try {
        const response = await Axios.get(`/api/my-instructors`);
        instructors.value = response.data.data;
    } catch (error) {
        console.error("Error fetching instructors:", error);
        toast.error("Failed to fetch instructors");
    }
};

watch(() => props.toggleAddButton, (newValue) => {
    if (newValue) {
        showAddModal.value = true;
    }
});

function onNextPage() {
    if (currentPage.value == totalPages.value) return;

    fetchUsers(currentPage.value + 1);
}

function onPreviousPage() {
    if (currentPage.value <= 1) return;

    fetchUsers(currentPage.value - 1);
}

function coursePerPage(amount) {
    rowsPerPage.value = amount;
    fetchUsers(currentPage.value);
}

function onAddSchedule(id) {
    studentId.value = id;
    showAddSchedule.value = true;
}

function toggleSchedule(userId) {
    expandedUser.value = expandedUser.value === userId ? null : userId;
}

function editSession(session) {
    selectedSession.value = session;
    showAddSchedule.value = true;
}

const handleScheduleAdded = () => {
    fetchUsers();
    showAddSchedule.value = false;
    selectedSession.value = null;
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

onMounted(() => {
    fetchUsers();
    fetchInstructors();
});
</script>

<template>

    <div v-if="selectedRoom" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="relative w-full h-full min-h-[600px]">
            <JetsiLive :selectedRoom="selectedRoom" @closeStream="selectedRoom = null" />
        </div>
    </div>

    <div v-else>
        <div v-if="isLoading" class="flex justify-center items-center h-64">
            <Spinner />
        </div>
        <div v-else>
            <div class="w-full min-h-72 overflow-auto mt-12 bg-white scrollbar">
                <div class="w-full" v-if="users.length">
                    <!-- Table Header -->
                    <div class="grid grid-cols-12 border-b border-gray-200 bg-gray-50">
                        <div class="col-span-4 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            User
                        </div>
                        <div class="col-span-5 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Email
                        </div>
                        <div class="col-span-3 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Action
                        </div>
                    </div>

                    <!-- Table Rows -->
                    <div class="divide-y divide-gray-200">
                        <div v-for="user in users" :key="user.id">
                            <!-- Main User Row -->
                            <div class="grid grid-cols-12 hover:bg-gray-50 cursor-pointer">
                                <!-- User Column -->
                                <div @click="toggleSchedule(user.id)"
                                    class="col-span-4 px-6 py-4 whitespace-nowrap flex items-center">
                                    <div
                                        class="text-sm text-gray-700 flex items-center cursor-pointer hover:text-lime-700 mr-4">
                                        <i :class="{
                                            'fa-angle-right': expandedUser !== user.id,
                                            'fa-chevron-down': expandedUser === user.id
                                        }" class="fa-solid  text-lime-700 text-lg"></i>
                                    </div>
                                    <img :src="user.profile || 'images/no-profile.png'" alt="avatar"
                                        class="w-8 h-8 rounded-full mr-3" />
                                    <div class="flex-col">
                                        <h1 class="text-sm font-medium capitalize text-gray-900">
                                            {{ user.first_name }} {{ user.middle_name }}
                                        </h1>
                                        <span class="text-lime-700 text-sm">Schedules ({{ user.mySchedules ? user.mySchedules.length : 0 }})</span>
                                    </div>
                                </div>

                                <!-- Email Column -->
                                <div class="col-span-5 px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">
                                        {{ user.email }}
                                    </div>
                                </div>

                                <!-- Action Column -->
                                <div class="col-span-3 px-6 py-4 whitespace-nowrap flex items-center"> 
                                   <button @click="onAddSchedule(user.id)"
                                        class="text-sm px-3 py-1.5 rounded-md bg-lime-600 text-white hover:bg-lime-700 focus:outline-none transition-colors flex items-center">
                                        <i class="fas fa-plus mr-1 text-xs"></i>
                                        Add Schedule
                                    </button>
                                </div>
                            </div>

                            <!-- Schedule Details (Collapsible) -->
                            <div v-if="expandedUser === user.id && user.mySchedules?.length"
                                class="col-span-12 bg-gray-50 px-6 py-3 border-t">
                                <div class="overflow-hidden rounded-lg shadow-xs">
                                    <div class="w-full overflow-x-auto">
                                        <table class="w-full whitespace-nowrap">
                                            <thead class="text-xs font-semibold text-gray-500 bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 text-left">Day</th>
                                                    <th class="px-4 py-2 text-left">Time</th>
                                                    <th class="px-4 py-2 text-left">Status</th>
                                                    <th class="px-4 py-2 text-left">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-sm divide-y divide-gray-200 bg-white">
                                                <tr v-for="session in user.mySchedules" :key="session.id"
                                                    class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-medium text-gray-900">
                                                        {{ session.day }}
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-600">
                                                        {{ session.schedule_time || 'Not set' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span :class="{
                                                            'bg-green-100 text-green-800': session.status === 'scheduled',
                                                            'bg-yellow-100 text-yellow-800': session.status === 'pending',
                                                            'bg-red-100 text-red-800': session.status === 'cancelled'
                                                        }"
                                                            class="px-3 py-1 rounded-full text-xs font-medium inline-flex items-center">
                                                            <span class="w-2 h-2 rounded-full mr-2" :class="{
                                                                'bg-green-500': session.status === 'scheduled',
                                                                'bg-yellow-500': session.status === 'pending',
                                                                'bg-red-500': session.status === 'cancelled'
                                                            }"></span>
                                                            {{ session.status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 space-x-2">
                                                        <button @click="editSession(session)"
                                                            class="px-3 py-1 text-xs border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none transition-colors">
                                                            <i class="fas fa-edit mr-1"></i> Edit
                                                        </button>
                                                        <button @click="joinSession(session.room_name)"
                                                            :disabled="isStartingSession"
                                                            class="px-3 py-1 text-xs border border-lime-600 rounded-md text-white bg-lime-600 hover:bg-lime-700 focus:outline-none transition-colors disabled:opacity-70">
                                                            <i class="fas fa-play mr-1"></i>
                                                            <span v-if="!isStartingSession">Start</span>
                                                            <span v-else>Starting...</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
        <!-- Overlay: Add private Schedule Form -->
        <div v-if="showAddSchedule"
            class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 animate-fadeIn z-50">
            <AddPrivateSchedule v-if="showAddSchedule" :selectedSession="selectedSession"
                @close="showAddSchedule = false" :studentId="studentId" @schedule-updated="handleScheduleAdded" />
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
