<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
import Spinner from "../Layout/Spinner.vue";
import AddSchedule from "./AddSchedule.vue";  // Import your AddSchedule component
import { useToast } from "vue-toastification";

const toast = useToast();

const days = ref([
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
]);

const schedules = ref({ data: [], prev_page_url: null, next_page_url: null });
const showModal = ref(false);
const showDeleteModal = ref(false);
const showAddSchedule = ref(false); 
const selectedScheduleId = ref(null);
const form = ref({ id: null, day: "", time: "", period: "AM" });
const loading = ref(false);

const currentPage = ref(1);
const totalPages = ref(1);
const searchPage = ref('');

const fetchSchedules = async (url = "/api/schedules") => {
    loading.value = true;
    try {
        const response = await Axios.get(url);
        schedules.value = response.data;
    } catch (error) {
        console.error("Error fetching schedules:", error);
        toast.error("Error fetching schedules!");
    } finally {
        loading.value = false;
    }
};

const editSchedule = (schedule) => {
    // Parse the time into hours, minutes, and period
    const timeStr = schedule.schedule_time;
    const [time, period] = timeStr.split(' ');
    const [hours, minutes] = time.split(':');
    
    form.value = {
        id: schedule.id,
        day: schedule.day,
        time: `${hours.padStart(2, '0')}:${minutes?.padStart(2, '0')}`,
        period: period || 'AM'
    };
    showModal.value = true;
};

const updateSchedule = async () => {
    try {
        const formattedTime = `${form.value.time} ${form.value.period}`;
        await Axios.put(`/api/schedules/${form.value.id}`, {
            day: form.value.day,
            time: formattedTime,
        });
        showModal.value = false;
        toast.success("Schedule updated successfully!");
        fetchSchedules();
    } catch (error) {
        console.error("Error updating schedule:", error);
        toast.error("Error updating schedule!");
    }
};

const confirmDelete = (id) => {
    selectedScheduleId.value = id;
    showDeleteModal.value = true;
};

const deleteSchedule = async () => {
    try {
        await Axios.delete(`/api/schedules/${selectedScheduleId.value}`);
        showDeleteModal.value = false;
        toast.success("Schedule deleted successfully!");
        fetchSchedules();
    } catch (error) {
        console.error("Error deleting schedule:", error);
        toast.error("Error deleting schedule!");
    }
};
 
onMounted(() => {
    fetchSchedules();
});
</script>

<template>
    <div class="max-w-6xl mx-auto mt-7">
        <div class="flex justify-end items-center mb-2 mr-2">
            <button @click="showAddSchedule = true"
                class="bg-lime-700 text-white px-4 py-2 rounded hover:bg-lime-800 transition">
                Add Schedule
            </button>
        </div>

        <!-- Table Wrapper -->
        <div class="overflow-x-auto bg-white shadow-md rounded-md mb-6">
            <div v-if="loading"
                class="flex justify-center items-center h-64">
                <Spinner />
            </div>
            <div v-else class="w-full  overflow-x-auto scrollbar">
                <table class="min-w-full divide-y divide-gray-200">
                    <!-- Table Header -->
                    <thead class="bg-white rounded-t-lg">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <!-- Table Body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="schedule in schedules.data"
                            :key="schedule.id"
                            class="border-b">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ schedule.day }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{schedule.schedule_time }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex justify-center gap-4">
                                <!-- Edit Button -->
                                <button @click="editSchedule(schedule)"
                                    class="text-lime-600 hover:text-lime-500 p-2 transition-all"
                                    title="Edit">
                                    <i class="fas fa-edit text-lg"></i>
                                </button>
                                <!-- Delete Button -->
                                <button @click="confirmDelete(schedule.id)"
                                    class="text-red-600 hover:text-red-500 p-2 transition-all"
                                    title="Delete">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center space-x-3 mt-4">
            <button v-if="schedules.prev_page_url"
                @click="fetchSchedules(schedules.prev_page_url)"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-all">
                Previous
            </button>
            <button v-if="schedules.next_page_url"
                @click="fetchSchedules(schedules.next_page_url)"
                class="bg-lime-700 text-white px-4 py-2 rounded-md hover:bg-lime-600 transition-all">
                Next
            </button>
        </div>

        <!-- Overlay: Add Schedule Form -->
        <div v-if="showAddSchedule"
            class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 animate-fadeIn z-50">
            <div class="bg-white p-4 rounded-lg w-full mx-4 max-w-md sm:max-w-lg md:max-w-xl relative">
                <!-- Close Button -->
                <button @click="showAddSchedule = false"
                    class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 focus:outline-none"
                    title="Close">
                    <i class="fas fa-times text-2xl"></i>
                </button>
                <AddSchedule />
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 animate-fadeIn z-50">
            <div class="bg-white p-6 rounded-lg w-full mx-4 max-w-md sm:max-w-lg md:max-w-xl">
                <h2 class="text-xl font-bold mb-4 text-lime-700 flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit Schedule
                </h2>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700">Day</label>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2">
                        <button
                            v-for="day in days"
                            :key="day"
                            type="button"
                            @click="form.day = day"
                            :class="[
                                'p-2 rounded-lg text-center transition-colors',
                                form.day === day
                                    ? 'bg-lime-700 text-white'
                                    : 'bg-gray-100 hover:bg-lime-100'
                            ]"
                        >
                            {{ day }}
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700">Time</label>
                    <div class="grid grid-cols-2 gap-4">
                        <input
                            v-model="form.time"
                            type="text" 
                            pattern="(0[0-9]|1[0-2]):[0-5][0-9]"
                            placeholder="HH:MM"
                            class="w-full border p-2 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-500"
                        />
                        <div class="flex gap-2">
                            <button
                                v-for="p in ['AM', 'PM']"
                                :key="p"
                                type="button"
                                @click="form.period = p"
                                :class="[
                                    'flex-1 p-2 rounded-lg text-center transition-colors',
                                    form.period === p
                                        ? 'bg-lime-700 text-white'
                                        : 'bg-gray-100 hover:bg-lime-100'
                                ]"
                            >
                                {{ p }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-3">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateSchedule"
                        class="px-4 py-2 bg-lime-600 text-white rounded-md hover:bg-lime-700 transition-all"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>


        <!-- Delete Confirmation Overlay -->
        <div v-if="showDeleteModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center z-50  animate-fadeIn">
            <div class="bg-white p-6 rounded-lg w-80 text-center">
                <h2 class="text-xl font-semibold text-red-600 mb-4 flex items-center justify-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h2>
                <p class="text-gray-700 mb-6">Are you sure you want to delete this schedule?</p>
                <div class="flex items-center gap-4 justify-center">
                    <button @click="showDeleteModal = false"
                        class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500">
                        Cancel
                    </button>
                    <button @click="deleteSchedule"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-500 flex items-center gap-2">
                        <i class="fas fa-trash"></i> Delete
                    </button>

                </div>
            </div>
        </div>
    </div>
</template>



<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}
</style>