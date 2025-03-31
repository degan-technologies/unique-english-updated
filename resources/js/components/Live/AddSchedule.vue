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
    const errors = ref({ day: "", time: "" });
    const addSchedule = async () => {
        errors.value = { day: "", time: "" };

        if (!selectedDay.value) {
            errors.value.day = "Please select a day.";
        }
        if (!selectedTime.value) {
            errors.value.time = "Please select a time.";
        }

        if (errors.value.day || errors.value.time) return;

        try {
            const response = await Axios.post(API_URL, {
                day: selectedDay.value,
                time: selectedTime.value,
            });

            toast.success(response.data.message || "Schedule added successfully!");
            selectedDay.value = "";
            selectedTime.value = "";
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
                <select v-model="selectedDay"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700">
                    <option value=""
                        disabled>Select a day</option>
                    <option v-for="day in days"
                        :key="day"
                        :value="day">
                        {{ day }}
                    </option>
                </select>
                <p v-if="errors.day"
                    class="text-red-600 text-sm mt-1">
                    {{ errors.day }}
                </p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">
                    Time Slot
                </label>
                <input v-model="selectedTime"
                    type="time"
                    class="w-full mt-2 p-3 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-700" />
                <p v-if="errors.time"
                    class="text-red-600 text-sm mt-1">
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
