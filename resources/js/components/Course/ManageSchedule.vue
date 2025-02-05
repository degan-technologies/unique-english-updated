<template>
    <div class="p-6 max-w-6xl mx-auto bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            Manage Weekly Schedule
        </h2>

        <!-- Schedule Table -->
        <div class="overflow-x-auto">
            <table
                class="min-w-full table-auto text-center border-collapse border border-gray-300"
            >
                <thead>
                    <tr class="bg-lime-700 text-white">
                        <th class="px-4 py-2 border border-gray-300">Day</th>
                        <th class="px-4 py-2 border border-gray-300">
                            Time Slots
                        </th>
                        <th class="px-4 py-2 border border-gray-300">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(day, index) in schedule"
                        :key="index"
                        class="border border-gray-300"
                    >
                        <td
                            class="px-4 py-2 border border-gray-300 font-semibold"
                        >
                            {{ day.name }}
                        </td>
                        <td class="px-4 py-2 border border-gray-300">
                            <span v-if="day.slots.length">
                                {{
                                    day.slots
                                        .map((slot) => formatTime(slot.time))
                                        .join(", ")
                                }}
                            </span>
                            <span v-else class="text-gray-500"
                                >No Schedule</span
                            >
                        </td>
                        <td class="px-4 py-2 border border-gray-300">
                            <button
                                @click="editDay(index)"
                                class="text-lime-700 hover:text-lime-500 mr-3"
                            >
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button
                                @click="deleteDay(index)"
                                class="text-red-600 hover:text-red-400"
                            >
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="isEditing"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-10"
        >
            <div
                class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto border border-gray-300"
            >
                <h3 class="text-xl font-semibold text-lime-700 mb-4">
                    Edit Schedule
                </h3>
                <form @submit.prevent="updateSchedule">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700"
                            >Day</label
                        >
                        <input
                            v-model="editingDay.name"
                            disabled
                            class="w-full mt-2 p-3 border rounded-lg bg-gray-100"
                        />
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700"
                            >Time Slots</label
                        >
                        <div
                            class="border rounded-lg p-2 max-h-48 overflow-y-auto"
                        >
                            <div
                                v-for="(slot, slotIndex) in editingDay.slots"
                                :key="slotIndex"
                                class="flex gap-2 mt-2"
                            >
                                <input
                                    v-model="editingDay.slots[slotIndex].time"
                                    type="time"
                                    class="w-full p-3 border rounded-lg bg-gray-100"
                                />
                                <button
                                    type="button"
                                    @click="removeTimeSlot(slotIndex)"
                                    class="text-red-600 hover:text-red-400"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="addTimeSlot"
                            class="mt-2 px-4 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-600"
                        >
                            + Add Time Slot
                        </button>
                    </div>
                    <div class="flex justify-between items-center mt-6">
                        <button
                            @click="isEditing = false"
                            type="button"
                            class="px-6 py-3 bg-gray-300 text-gray-800 rounded-lg"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-600"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

// Schedule Data
const schedule = ref([
    { name: "Monday", slots: [{ time: "09:00" }, { time: "14:00" }] },
    { name: "Tuesday", slots: [{ time: "10:00" }] },
    { name: "Wednesday", slots: [{ time: "13:00" }, { time: "16:00" }] },
    { name: "Thursday", slots: [{ time: "08:00" }] },
    { name: "Friday", slots: [] },
    { name: "Saturday", slots: [{ time: "08:00" }] },
    { name: "Sunday", slots: [] },
]);

// Editing State
const isEditing = ref(false);
const editingDay = ref({ name: "", slots: [] });
const editingIndex = ref(null);

// Open Edit Modal
const editDay = (index) => {
    editingIndex.value = index;
    editingDay.value = JSON.parse(JSON.stringify(schedule.value[index])); // Deep copy
    isEditing.value = true;
};

// Add Time Slot
const addTimeSlot = () => {
    editingDay.value.slots.push({ time: "12:00" }); // Default to 12:00
};

// Remove Time Slot
const removeTimeSlot = (index) => {
    editingDay.value.slots.splice(index, 1);
};

// Update Schedule
const updateSchedule = () => {
    schedule.value[editingIndex.value] = JSON.parse(
        JSON.stringify(editingDay.value)
    ); // Save changes
    isEditing.value = false;
};

// Delete Day's Schedule (Clear time slots instead of removing the day)
const deleteDay = (index) => {
    schedule.value[index].slots = [];
};

// Format Time to AM/PM
const formatTime = (time) => {
    let [hours, minutes] = time.split(":").map(Number);
    let period = hours >= 12 ? "PM" : "AM";
    hours = hours % 12 || 12; // Convert 24-hour to 12-hour format
    return `${hours}:${minutes.toString().padStart(2, "0")} ${period}`;
};
</script>

<style scoped>
/* Custom styles */
</style>
