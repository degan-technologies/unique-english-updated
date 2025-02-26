<template>
    <div class="p-6 max-w-lg mx-auto bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            Add Schedule
        </h2>
        <form @submit.prevent="addSchedule">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700"
                    >Day</label
                >
                <select
                    v-model="selectedDay"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                >
                    <option value="" disabled>Select a day</option>
                    <option v-for="day in days" :key="day" :value="day">
                        {{ day }}
                    </option>
                </select>
                <p v-if="errors.day" class="text-red-600 text-sm mt-1">
                    {{ errors.day }}
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700"
                    >Time Slot</label
                >
                <input
                    v-model="selectedTime"
                    type="time"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700"
                />
                <p v-if="errors.time" class="text-red-600 text-sm mt-1">
                    {{ errors.time }}
                </p>
            </div>

            <div class="flex justify-between items-center">
                <button
                    type="submit"
                    class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-800 focus:outline-none focus:ring-2 focus:ring-lime-700"
                >
                    Add Schedule
                </button>
            </div>
        </form>

        <!-- Success Message -->
        <p v-if="successMessage" class="text-green-600 text-center mt-4">
            {{ successMessage }}
        </p>
    </div>
</template>

<script setup>
import { ref } from "vue";

const days = ref([
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
]);
const selectedDay = ref("");
const selectedTime = ref("");
const errors = ref({ day: "", time: "" });
const successMessage = ref("");
const scheduleList = ref([]); // Store added schedules to prevent duplicates

const addSchedule = () => {
    errors.value = { day: "", time: "" }; // Reset errors

    if (!selectedDay.value) {
        errors.value.day = "Please select a day.";
    }
    if (!selectedTime.value) {
        errors.value.time = "Please select a time.";
    }

    if (errors.value.day || errors.value.time) return; // Stop if validation fails

    // Prevent duplicate schedules
    const isDuplicate = scheduleList.value.some(
        (entry) =>
            entry.day === selectedDay.value && entry.time === selectedTime.value
    );

    if (isDuplicate) {
        errors.value.time =
            "This time slot already exists for the selected day.";
        return;
    }

    // Add schedule
    scheduleList.value.push({
        day: selectedDay.value,
        time: selectedTime.value,
    });
    successMessage.value = `Schedule added: ${
        selectedDay.value
    } at ${formatTime(selectedTime.value)}`;

    // Reset fields
    selectedDay.value = "";
    selectedTime.value = "";
    setTimeout(() => (successMessage.value = ""), 3000); // Clear success message after 3 seconds
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
/* Custom styles if needed */
</style>
