<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
import { useToast } from "vue-toastification";

import Spinner from "@/components/Layout/Spinner.vue";
import Popper from "vue3-popper";

const toast = useToast();
const attendances = ref([]);
const users = ref([]);
const isLoading = ref(true);
const activeTab = ref('instractor');

const currentPage = ref(1);
const rowsPerPage = ref(10);
const rowsPerPageOptions = [5, 10, 15, 20];
const pagination = ref("");
const totalPages = ref(0);

const attendancePagination = ref("");
const attendanceTotalPages = ref(0);
const attendanceCurrentPage = ref(0);
 
const selectedInstructor = ref(null);

const getInstractorAttendance = async (page = 1) => {
    try {
        const res = await Axios.get(`/api/get-instractor-attendance?page=${page}`, {
            params: {
                rowsPerPageOptions: rowsPerPage.value,
            }
        });
        users.value = res.data.data;
        pagination.value = res.data.pagination;
        totalPages.value = res.data.pagination.last_page;
        currentPage.value = res.data.pagination.current_page;
    } catch (error) {
        toast.error("Failed to load participants", { timeout: 3000 }); 
    }
};

const detailInstractorAttendance = async (page = 1, id) => {
    try {
        const res = await Axios.get(`/api/detail-instractor-attendance/${id}?page=${page}`, {
            params: {
                rowsPerPageOptions: rowsPerPage.value,
            }
        });
        attendances.value = res.data.data;
        attendancePagination.value = res.data.pagination;
        attendanceTotalPages.value = res.data.pagination.last_page;
        attendanceCurrentPage.value = res.data.pagination.current_page;
    } catch (error) {
        selectedInstructor.value = null;
        attendances.value = [];
        attendancePagination.value = "";
        attendanceTotalPages.value = 0;
        attendanceCurrentPage.value = 0;
        toast.error("not found", { timeout: 3000 }); 
    }
}; 
 
function onNextPage() {
    if (currentPage.value == totalPages.value) return;

    getInstractorAttendance(currentPage.value + 1);
}

function onPreviousPage() {
    if (currentPage.value <= 1) return;

    getInstractorAttendance(currentPage.value - 1);
}

function studentPerPage(amount) {
    rowsPerPage.value = amount;
    getInstractorAttendance(currentPage.value);
}

function onModalNextPage() {
    if (attendanceCurrentPage.value == attendanceTotalPages.value) return;

    getInstractorAttendance(attendanceCurrentPage.value + 1);
}

function onModalPreviousPage() {
    if (attendanceCurrentPage.value <= 1) return;

    getInstractorAttendance(attendanceCurrentPage.value - 1);
}

function attendanceModalPerPage(amount) {
    rowsPerPage.value = amount;
    getInstractorAttendance(attendanceCurrentPage.value);
}

async function toggleAttendanceModal(user) {
    await detailInstractorAttendance(1, user.id);

    selectedInstructor.value = user;
    return;
}

onMounted(async () => {
    try {
        await Promise.all([getInstractorAttendance()]);
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
                            Attendance Management
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
                                        <span class="ml-2 text-sm font-medium text-gray-700">Attendances</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <div class="my-4">
            <nav class="flex border-b">
                <button :class="{
                    'border-lime-700 text-lime-700 border-b-2': activeTab === 'instractor',
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'instractor',
                }" @click="activeTab = 'instractor'" class="px-4 py-2 font-medium focus:outline-none">
                    Instractor 
                </button> 
            </nav>
        </div>

        <div v-if="activeTab === 'instractor'">
            <!-- Main Content -->
            <main class="max-w-7xl mx-auto py-6">
                <div v-if="isLoading" class="flex justify-center items-center h-64">
                    <Spinner />
                </div>

                <template v-else>
                    <div class="bg-white shadow rounded-lg overflow-hidden"> 
                            <div class="overflow-x-auto min-h-48">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Email</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total Classes
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total Houre
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="user in users" :key="user.id"
                                            class="hover:bg-gray-50 cursor-pointer transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center"> 
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
                                                <div class="text-sm text-gray-500">{{ user.total_classes }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ user.total_hours }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button 
                                                    @click="toggleAttendanceModal(user)"
                                                    class="inline-flex items-center px-3 py-1 text-xs font-medium rounded border border-gray-50 text-gray-500 hover:bg-lime-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 disabled:opacity-75 disabled:cursor-not-allowed">
                                                    <span> Details </span>
                                                </button>
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
                                <Popper>
                                    <div class="flex flex-row md:gap-2">
                                        <span class="hidden md:flex text-sm text-gray-600">rows per page:</span>
                                        <span class="text-sm font-medium">{{ rowsPerPage }}</span>
                                        <i class="fa-solid fa-chevron-down text-lg"></i>
                                    </div>
                                    <template #content>
                                        <div v-for="option in rowsPerPageOptions" :key="option"
                                            @click="studentPerPage(option)"
                                            class="border w-32 block border-gray-200 rounded-md px-2 py-2 text-sm cursor-pointer transition-all duration-200"
                                            :class="{
                                                'bg-gray-300 text-white font-bold':
                                                    rowsPerPage === option,
                                                'bg-white text-gray-700 hover:bg-gray-200':
                                                    rowsPerPage !== option,
                                            }">
                                            {{ option }}
                                        </div>
                                    </template>
                                </Popper>

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
                </template>
            </main>
        </div>

         <!-- Activity Log Modal -->
        <transition name="fade">
            <div v-if="selectedInstructor && attendances.length > 0"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div class="bg-white rounded shadow-lg w-full md:max-w-4xl p-4 mx-4">
                    <h3 class="text-xl font-bold mb-4">
                       Detail attendance for {{ selectedInstructor.first_name }}
                        {{ selectedInstructor.middle_name }}
                    </h3>
                    <div v-if="attendances.length"
                        class="max-h-[60vh] overflow-auto scrollbar">
                        <table class="min-w-[700px] w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2 text-left">Date</th>
                                    <th class="py-2 text-left">Time</th>
                                    <th class="py-2 text-left">Total Hours</th>
                                    <th class="py-2 text-left">Total Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(attendance, index) in attendances" :key="index"
                                    class="border-b hover:bg-gray-50">
                                    <td class="py-2 pr-6 whitespace-nowrap">
                                        {{ attendance.created_at }}
                                    </td>
                                    <td class="py-2">{{ attendance.start_time }} - {{ attendance.end_time }}</td>
                                    <td class="py-2">{{ attendance.total_hours }}</td>
                                    <td class="py-2">{{ attendance.total_students }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-sm text-gray-600 py-4 text-center">
                        No activity found for this user.
                    </div>
                    <div class="p-4 bg-white flex flex-row items-center justify-between">
                                <Popper>
                                    <div class="flex flex-row md:gap-2">
                                        <span class="hidden md:flex text-sm text-gray-600">rows per page:</span>
                                        <span class="text-sm font-medium">{{ rowsPerPage }}</span>
                                        <i class="fa-solid fa-chevron-down text-lg"></i>
                                    </div>
                                    <template #content>
                                        <div v-for="option in rowsPerPageOptions" :key="option"
                                            @click="attendanceModalPerPage(option)"
                                            class="border w-32 block border-gray-200 rounded-md px-2 py-2 text-sm cursor-pointer transition-all duration-200"
                                            :class="{
                                                'bg-gray-300 text-white font-bold':
                                                    rowsPerPage === option,
                                                'bg-white text-gray-700 hover:bg-gray-200':
                                                    rowsPerPage !== option,
                                            }">
                                            {{ option }}
                                        </div>
                                    </template>
                                </Popper>

                                <!-- Pagination Controls -->
                                <div class="flex items-center space-x-3">
                                    <button @click="onModalPreviousPage()" :disabled="attendanceCurrentPage === 1"
                                        class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                        Prev
                                    </button>

                                    <span class="text-sm text-gray-600">
                                        Page {{ attendanceCurrentPage }} of {{ attendanceTotalPages }}
                                    </span>

                                    <button @click="onModalNextPage()" :disabled="attendanceCurrentPage === attendanceTotalPages"
                                        class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                        Next
                                    </button>
                                </div>
                            </div>
                    <div class="mt-4 flex justify-end">
                        <button @click="selectedInstructor = null"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition-colors duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </transition>
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