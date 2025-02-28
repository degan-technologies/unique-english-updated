<template>
    <div class="p-6 flex flex-col md:flex-row mt-24 gap-6">
        <!-- Schedule Section -->
        <div class="flex-[0.7]">
            <h1 class="text-2xl font-bold mb-4 text-center text-lime-700">
                Weekly Live Session Schedule
            </h1>

            <!-- Loading Spinner -->
            <Spinner v-if="loadingSchedule" />

            <div
                v-else
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6"
            >
                <div
                    v-for="(day, index) in schedule"
                    :key="index"
                    class="p-4 rounded-lg shadow-lg bg-white border border-gray-200"
                >
                    <h2
                        class="text-lg font-bold text-gray-700 text-center mb-3"
                    >
                        {{ day.name }}
                    </h2>

                    <!-- Display Times as Cards -->
                    <div v-if="day.slots.length" class="space-y-3">
                        <div
                            v-for="(slot, slotIndex) in day.slots"
                            :key="slotIndex"
                            class="flex items-center justify-center gap-3 bg-gray-100 p-2 rounded-md shadow-sm border border-gray-300"
                        >
                            <i class="fas fa-clock text-lime-700 text-lg"></i>
                            <span class="text-base font-semibold text-gray-800">
                                {{ formatTime(slot.time) }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-else
                        class="p-2 text-center text-gray-500 bg-gray-100 rounded-md"
                    >
                        No Schedule
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Section with Dynamic Plans -->
        <div
            class="flex-[0.3] bg-white p-4 rounded-lg shadow-lg border border-gray-200 flex flex-col justify-start"
        >
            <h2 class="text-xl font-bold mb-3 text-center text-lime-700">
                Join a Plan
            </h2>

            <!-- Loading Spinner -->
            <Spinner v-if="loadingPlans" />

            <!-- Error Message -->
            <div v-if="error" class="text-red-500 text-center">{{ error }}</div>

            <!-- Dynamic Plan Buttons -->
            <div v-else-if="plans.length" class="space-y-6 flex flex-col">
                <button
                    v-for="plan in plans"
                    :key="plan.id"
                    @click="joinPlan(plan)"
                    class="w-full py-2 px-5 bg-lime-700 text-white font-semibold text-lg rounded-lg hover:bg-lime-800 transition duration-200"
                >
                    {{ plan.name }} - {{ plan.price }} Birr
                </button>
            </div>

            <!-- No Plans Found -->
            <div v-else class="text-gray-500 text-center">
                No plans available.
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Spinner from "../Layout/Spinner.vue"; // Import Spinner

const schedule = ref([]);
const plans = ref([]); // Store fetched plans
const loadingSchedule = ref(true);
const loadingPlans = ref(true);
const error = ref(null);

// Ordered days from Monday to Sunday
const orderedDays = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
];

// Function to format 24-hour time to 12-hour format with AM/PM
const formatTime = (time) => {
    if (!time) return "";
    let [hour, minute] = time.split(":").map(Number);
    let period = hour >= 12 ? "PM" : "AM";
    hour = hour % 12 || 12; // Convert 0-23 hours to 12-hour format
    return `${hour}:${minute.toString().padStart(2, "0")} ${period}`;
};

// Fetch schedule from API
const fetchSchedule = async () => {
    try {
        const response = await axios.get("/api/schedules");
        const rawData = response.data.data;

        console.log("Raw API Data:", rawData); // Debugging: Check what the API returns

        // Grouping schedules by day
        const groupedSchedule = orderedDays.reduce((acc, day) => {
            acc[day] = { name: day, slots: [] };
            return acc;
        }, {});

        rawData.forEach((schedule) => {
            if (groupedSchedule[schedule.day]) {
                groupedSchedule[schedule.day].slots.push({
                    time: schedule.time,
                });
            }
        });

        schedule.value = Object.values(groupedSchedule);
        console.log("Formatted Schedule:", schedule.value); // Debugging: Check the final schedule array
    } catch (error) {
        console.error("Error fetching schedule:", error);
    } finally {
        loadingSchedule.value = false;
    }
};

// Fetch plans from API
const fetchPlans = async () => {
    try {
        const response = await axios.get("/api/plans");
        console.log("API Response:", response);
        plans.value = response.data.data;
    } catch (err) {
        console.error("Error fetching plans:", err);
        error.value = "Failed to fetch plans.";
    } finally {
        loadingPlans.value = false;
    }
};

// Join plan function (dummy for now)
const joinPlan = (plan) => {
    alert(`You have joined the ${plan.name} plan for ${plan.price} Birr.`);
};

// Fetch data on component mount
onMounted(() => {
    fetchSchedule();
    fetchPlans();
});
</script>

<style scoped>
/* Additional styles if needed */
</style>
