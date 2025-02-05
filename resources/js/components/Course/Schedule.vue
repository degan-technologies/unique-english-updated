<template>
    <div class="p-6 flex flex-col md:flex-row gap-6">
        <!-- Schedule Section (Fixed height) -->
        <div class="flex-[0.7]">
            <h1 class="text-2xl font-bold mb-4">
                Weekly Live Session Schedule
            </h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div
                    v-for="(day, index) in schedule"
                    :key="index"
                    class="p-4 rounded-lg shadow-md bg-white border"
                >
                    <h2 class="text-lg font-semibold text-gray-700 text-center">
                        {{ day.name }}
                    </h2>
                    <div class="mt-2 flex flex-col gap-2">
                        <template v-if="day.slots.length">
                            <div
                                v-for="(slot, slotIndex) in day.slots"
                                :key="slotIndex"
                                class="p-1 rounded-md text-center flex items-center justify-center gap-2 bg-gray-50 text-black"
                            >
                                <i class="fas fa-clock text-lime-700"></i>
                                <span>{{ slot.time }}</span>
                            </div>
                        </template>
                        <div v-else class="p-2 text-center text-gray-500">
                            No Schedule
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Section (Smaller height) -->
        <div
            class="flex-[0.3] bg-white p-3 rounded-lg shadow-md border flex flex-col justify-start py-2"
        >
            <h2 class="text-xl font-bold mb-3 text-center">Join a Plan</h2>
            <div class="space-y-6 flex flex-col">
                <button
                    class="w-full p-2 rounded-lg bg-lime-700 text-white hover:bg-lime-600"
                >
                    Join Monthly - 12,000 Birr
                </button>
                <button
                    class="w-full p-2 rounded-lg bg-lime-700 text-white hover:bg-lime-600"
                >
                    Join 3 Months - 30,000 Birr
                </button>
                <button
                    class="w-full p-2 rounded-lg bg-lime-700 text-white hover:bg-lime-600"
                >
                    Join 6 Months - 56,000 Birr
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const schedule = ref([
    {
        name: "Monday",
        slots: [{ time: "9:00 AM - 10:00 AM" }, { time: "2:00 PM - 3:00 PM" }],
    },
    {
        name: "Tuesday",
        slots: [{ time: "10:00 AM - 11:00 AM" }],
    },
    {
        name: "Wednesday",
        slots: [{ time: "1:00 PM - 2:00 PM" }, { time: "4:00 PM - 5:00 PM" }],
    },
    {
        name: "Thursday",
        slots: [{ time: "8:00 AM - 9:00 AM" }],
    },
    {
        name: "Friday",
        slots: [],
    },
    {
        name: "Saturday",
        slots: [{ time: "8:00 AM - 9:00 AM" }],
    },
    {
        name: "Sunday",
        slots: [],
    },
]);
</script>

<style>
/* Reset overflow on left card */
.scrollbar-hidden {
    overflow: unset;
}
</style>
