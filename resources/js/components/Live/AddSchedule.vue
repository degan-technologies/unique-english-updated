<script setup>
import { ref } from "vue";
import Axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();
const API_URL = "/api/schedules";

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
const selectedHour = ref("");
const selectedMinute = ref("");
const selectedPeriod = ref("AM");
const errors = ref({ day: "", time: "" });

// Generate hours (1-12)
const hours = Array.from({ length: 12 }, (_, i) => String(i + 1).padStart(2, '0'));
// Generate minutes (00-55, increment by 5)
const minutes = Array.from({ length: 12 }, (_, i) => String(i * 5).padStart(2, '0'));
const periods = ["AM", "PM"];

const formatTime = () => {
    if (selectedHour.value && selectedMinute.value && selectedPeriod.value) {
        return `${selectedHour.value}:${selectedMinute.value} ${selectedPeriod.value}`;
    }
    return "";
};

const addSchedule = async () => {
    errors.value = { day: "", time: "" };
    const formattedTime = formatTime();

    if (!selectedDay.value) {
        errors.value.day = "Please select a day.";
    }
    if (!formattedTime) {
        errors.value.time = "Please select a complete time.";
    }

    if (errors.value.day || errors.value.time) return;

    try {
        const response = await Axios.post(API_URL, {
            day: selectedDay.value,
            time: formattedTime,
        });

        toast.success(response.data.message || "Schedule added successfully!");
        selectedDay.value = "";
        selectedHour.value = "";
        selectedMinute.value = "";
        selectedPeriod.value = "AM";
    } catch (error) {
        toast.error(error.response?.data?.message || "Error adding schedule.");
    }
};
</script>

<template>
    <div class="bg-white p-4 rounded-lg w-full max-w-md sm:max-w-lg md:max-w-xl">
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            Add Schedule
        </h2>
        <form @submit.prevent="addSchedule">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">
                    Day
                </label>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2">
                    <button
                        v-for="day in days"
                        :key="day"
                        type="button"
                        @click="selectedDay = day"
                        :class="[
                            'p-2 rounded-lg text-center transition-colors',
                            selectedDay === day
                                ? 'bg-lime-700 text-white'
                                : 'bg-gray-100 hover:bg-lime-100'
                        ]"
                    >
                        {{ day }}
                    </button>
                </div>
                <p v-if="errors.day" class="text-red-600 text-sm mt-1">
                    {{ errors.day }}
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">
                    Time
                </label>
                <div class="grid grid-cols-3 gap-4 mt-2">
                    <!-- Hours -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Hour</label>
                        <div class="grid grid-cols-3 gap-1">
                            <button
                                v-for="hour in hours"
                                :key="hour"
                                type="button"
                                @click="selectedHour = hour"
                                :class="[
                                    'p-2 rounded-lg text-center text-sm transition-colors',
                                    selectedHour === hour
                                        ? 'bg-lime-700 text-white'
                                        : 'bg-gray-100 hover:bg-lime-100'
                                ]"
                            >
                                {{ hour }}
                            </button>
                        </div>
                    </div>

                    <!-- Minutes -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Minute</label>
                        <div class="grid grid-cols-3 gap-1">
                            <button
                                v-for="minute in minutes"
                                :key="minute"
                                type="button"
                                @click="selectedMinute = minute"
                                :class="[
                                    'p-2 rounded-lg text-center text-sm transition-colors',
                                    selectedMinute === minute
                                        ? 'bg-lime-700 text-white'
                                        : 'bg-gray-100 hover:bg-lime-100'
                                ]"
                            >
                                {{ minute }}
                            </button>
                        </div>
                    </div>

                    <!-- AM/PM -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Period</label>
                        <div class="grid grid-cols-2 gap-1">
                            <button
                                v-for="period in periods"
                                :key="period"
                                type="button"
                                @click="selectedPeriod = period"
                                :class="[
                                    'p-2 rounded-lg text-center text-sm transition-colors',
                                    selectedPeriod === period
                                        ? 'bg-lime-700 text-white'
                                        : 'bg-gray-100 hover:bg-lime-100'
                                ]"
                            >
                                {{ period }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-center text-sm font-medium text-gray-700">
                    Selected Time: {{ formatTime() || 'Not set' }}
                </div>
                <p v-if="errors.time" class="text-red-600 text-sm mt-1">
                    {{ errors.time }}
                </p>
            </div>

            <div class="flex justify-center">
                <button type="submit"
                    class="px-6 py-3 bg-lime-700 text-white rounded-lg hover:bg-lime-800 transition focus:outline-none focus:ring-2 focus:ring-lime-700">
                    Add Schedule
                </button>
            </div>
        </form>
    </div>
</template>
