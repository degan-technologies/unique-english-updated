<template>
    <div class="max-w-lg mx-auto p-6 mt-24">
        <!-- Title -->
        <h2
            class="text-3xl font-bold mb-6 text-center text-lime-700 flex items-center justify-center gap-2"
        >
            Manage Schedules
        </h2>

        <!-- Loading Spinner -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <Spinner />
        </div>

        <!-- Table Wrapper -->
        <div v-else class="overflow-x-auto bg-white shadow-md rounded-lg mb-6">
            <table class="w-full table-auto border-collapse">
                <!-- Table Header -->
                <thead class="bg-gray-200 uppercase text-sm">
                    <tr>
                        <th class="p-4 text-left border">Day</th>
                        <th class="p-4 text-left border">Time</th>
                        <th class="p-4 text-center border">Actions</th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody>
                    <tr
                        v-for="schedule in schedules.data"
                        :key="schedule.id"
                        class="border-b"
                    >
                        <td class="p-4 border">{{ schedule.day }}</td>
                        <td class="p-4 border">
                            {{ formatTime(schedule.time) }}
                        </td>
                        <td class="p-4 flex justify-center gap-4">
                            <!-- Edit Button -->
                            <button
                                @click="editSchedule(schedule)"
                                class="text-lime-600 hover:text-lime-500 p-2 transition-all"
                                title="Edit"
                            >
                                <i class="fas fa-edit text-lg"></i>
                            </button>
                            <!-- Delete Button -->
                            <button
                                @click="confirmDelete(schedule.id)"
                                class="text-red-600 hover:text-red-500 p-2 transition-all"
                                title="Delete"
                            >
                                <i class="fas fa-trash-alt text-lg"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center space-x-3 mt-4">
            <button
                v-if="schedules.prev_page_url"
                @click="fetchSchedules(schedules.prev_page_url)"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-all"
            >
                Previous
            </button>
            <button
                v-if="schedules.next_page_url"
                @click="fetchSchedules(schedules.next_page_url)"
                class="bg-lime-700 text-white px-4 py-2 rounded-md hover:bg-lime-600 transition-all"
            >
                Next
            </button>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm animate-fadeIn"
        >
            <div class="bg-white p-6 rounded-lg shadow-xl w-80">
                <h2
                    class="text-xl font-bold mb-4 text-lime-700 flex items-center gap-2"
                >
                    <i class="fas fa-edit"></i> Edit Schedule
                </h2>
                <label class="block mb-2 text-gray-700">Day:</label>
                <!-- Dropdown for day selection -->
                <select
                    v-model="form.day"
                    class="w-full border p-2 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-500"
                >
                    <option value="" disabled>Select a day</option>
                    <option v-for="day in days" :key="day" :value="day">
                        {{ day }}
                    </option>
                </select>

                <label class="block mt-3 mb-2 text-gray-700">Time:</label>
                <input
                    v-model="form.time"
                    type="time"
                    class="w-full border p-2 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-500"
                    step="3600"
                />
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
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-80 text-center">
                <h2
                    class="text-xl font-semibold text-red-600 mb-4 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h2>
                <p class="text-gray-700 mb-6">
                    Are you sure you want to delete this schedule?
                </p>

                <div class="flex justify-between">
                    <button
                        @click="deleteSchedule"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-500 flex items-center gap-2"
                    >
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button
                        @click="showDeleteModal = false"
                        class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Spinner from "../Layout/Spinner.vue";
import { useToast } from "vue-toastification"; // Import toastification

// Initialize toast
const toast = useToast();

// Define days for dropdown selection
const days = ref([
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
]);

// State for schedules and modal
const schedules = ref({ data: [], prev_page_url: null, next_page_url: null });
const showModal = ref(false);
const showDeleteModal = ref(false);
const selectedScheduleId = ref(null);
const form = ref({ id: null, day: "", time: "" });
const loading = ref(false);

// Fetch schedules with pagination
const fetchSchedules = async (url = "/api/schedules") => {
    loading.value = true;
    try {
        const response = await axios.get(url);
        schedules.value = response.data;
    } catch (error) {
        console.error("Error fetching schedules:", error);
        toast.error("Error fetching schedules!");
    } finally {
        loading.value = false;
    }
};

// Update schedule
const updateSchedule = async () => {
    // Ensure the time value is in "H:i" format (e.g., "14:30").
    // If form.value.time is "14:30:00", slicing will return "14:30".
    const formattedTime = form.value.time.slice(0, 5);
    try {
        await axios.put(`/api/schedules/${form.value.id}`, {
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

// When editing a schedule, reformat the time if necessary:
const editSchedule = (schedule) => {
    // If the schedule time might have seconds, slice to get "H:i".
    const timeValue = schedule.time.slice(0, 5);
    form.value = { ...schedule, time: timeValue };
    showModal.value = true;
};

// Open delete modal
const confirmDelete = (id) => {
    selectedScheduleId.value = id;
    showDeleteModal.value = true;
};

// Delete schedule
const deleteSchedule = async () => {
    try {
        await axios.delete(`/api/schedules/${selectedScheduleId.value}`);
        showDeleteModal.value = false;
        toast.success("Schedule deleted successfully!");
        fetchSchedules();
    } catch (error) {
        console.error("Error deleting schedule:", error);
        toast.error("Error deleting schedule!");
    }
};

// Format time to 12-hour format
const formatTime = (time) => {
    const [hour, minute] = time.split(":");
    const ampm = hour >= 12 ? "PM" : "AM";
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minute} ${ampm}`;
};

// Fetch schedules on component mount
onMounted(() => fetchSchedules());
</script>

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
