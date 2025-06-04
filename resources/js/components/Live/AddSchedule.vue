<script setup>
import { computed, onMounted, ref, watch } from "vue";
import Axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();
const rooms = ref([]);
const isLoading = ref(false);
const isSubmitting = ref(false);

const days = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday"
];

const hours = Array.from({ length: 12 }, (_, i) =>
    String(i + 1).padStart(2, "0")
);

const minutes = Array.from({ length: 12 }, (_, i) =>
    String(i * 5).padStart(2, "0")
);

const periods = ["AM", "PM"];

const props = defineProps({
    selectedSession: Object
});

const emit = defineEmits(["close", "schedule-updated"]);

const errors = ref({
    day: "",
    time: "",
    room: ""
});

const form = ref({
    id: null,
    day: "",
    hour: "01",
    minute: "00",
    period: "AM",
    live_room_id: null
});

const isEditing = computed(() => !!props.selectedSession);

function parseSessionTime(timeString) {
    if (!timeString) return { hour: "01", minute: "00", period: "AM" };

    const [time, period] = timeString.split(" ");
    const [hour, minute] = (time || "").split(":");

    return {
        hour: hour || "01",
        minute: minute || "00",
        period: period || "AM"
    };
}

watch(() => props.selectedSession, (session) => {
    if (!session) {
        resetForm();
        return;
    }

    const { hour, minute, period } = parseSessionTime(session.time);

    form.value = {
        id: session.id,
        day: session.day,
        hour,
        minute,
        period,
        live_room_id: session.live_room_id
    };
}, { immediate: true });

function resetForm() {
    form.value = {
        id: null,
        day: "",
        hour: "01",
        minute: "00",
        period: "AM",
        live_room_id: null
    };
    errors.value = {
        day: "",
        time: "",
        room: ""
    };
}

function formatTime() {
    return `${form.value.hour}:${form.value.minute} ${form.value.period}`;
}

function validateForm() {
    let isValid = true;
    errors.value = { day: "", time: "", room: "" };

    if (!form.value.day) {
        errors.value.day = "Please select a day";
        isValid = false;
    }

    if (!form.value.hour || !form.value.minute || !form.value.period) {
        errors.value.time = "Please select a complete time";
        isValid = false;
    }

    if (!form.value.live_room_id) {
        errors.value.room = "Please select a room";
        isValid = false;
    }

    return isValid;
}

async function handleSubmit() {
    if (!validateForm()) return;

    isSubmitting.value = true;
    try {
        const payload = {
            day: form.value.day,
            time: formatTime(),
            live_room_id: form.value.live_room_id
        };

        const response = isEditing.value
            ? await Axios.put(`/api/schedules/${form.value.id}`, payload)
            : await Axios.post("/api/schedules", payload);

        toast.success(response.data.message || `Schedule ${isEditing.value ? "updated" : "created"} successfully`, {
            timeout: 3000
        });

        emit("schedule-updated");
        emit('close');
        if (!isEditing.value) resetForm();
    } catch (error) {
        toast.error(
            error.response?.data?.message ||
            `Failed to ${isEditing.value ? "update" : "create"} schedule`,
            { timeout: 3000 }
        );
    } finally {
        isSubmitting.value = false;
    }
}

async function fetchRooms() {
    isLoading.value = true;
    try {
        const response = await Axios.get("/api/get-my-rooms");
        rooms.value = response.data.data;
    } catch (error) {
        toast.error("Failed to fetch rooms", { timeout: 3000 });
        console.error("Error fetching rooms:", error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(fetchRooms);
</script>

<template>
    <div class="bg-white rounded-lg shadow-xl overflow-hidden w-full max-w-xl">
        <!-- Modal Header -->
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ isEditing ? "Edit Schedule" : "Add New Schedule" }}
                </h3>
                <button @click="emit('close')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Room Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Room <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="room in rooms" :key="room.id" type="button" @click="form.live_room_id = room.id"
                            :class="{
                                'bg-lime-600 text-white': form.live_room_id === room.id,
                                'bg-gray-100 text-gray-800 hover:bg-gray-200': form.live_room_id !== room.id
                            }" class="px-3 py-2 rounded-md text-sm font-medium transition-colors duration-150">
                            {{ room.class_name }}
                        </button>
                    </div>
                    <p v-if="errors.room" class="mt-1 text-sm text-red-600">
                        {{ errors.room }}
                    </p>
                </div>

                <!-- Day Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Day <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button v-for="day in days" :key="day" type="button" @click="form.day = day" :class="{
                            'bg-lime-600 text-white': form.day === day,
                            'bg-gray-100 text-gray-800 hover:bg-gray-200': form.day !== day
                        }" class="px-3 py-2 rounded-md text-sm font-medium transition-colors duration-150">
                            {{ day }}
                        </button>
                    </div>
                    <p v-if="errors.day" class="mt-1 text-sm text-red-600">
                        {{ errors.day }}
                    </p>
                </div>

                <!-- Time Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Time <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Hour -->
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Hour</label>
                            <select v-model="form.hour"
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500">
                                <option v-for="hour in hours" :key="hour" :value="hour">
                                    {{ hour }}
                                </option>
                            </select>
                        </div>

                        <!-- Minute -->
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Minute</label>
                            <select v-model="form.minute"
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500">
                                <option v-for="minute in minutes" :key="minute" :value="minute">
                                    {{ minute }}
                                </option>
                            </select>
                        </div>

                        <!-- Period -->
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Period</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="period in periods" :key="period" type="button"
                                    @click="form.period = period" :class="{
                                        'bg-lime-600 text-white': form.period === period,
                                        'bg-gray-100 text-gray-800 hover:bg-gray-200': form.period !== period
                                    }" class="px-3 py-2 rounded-md text-sm font-medium transition-colors duration-150">
                                    {{ period }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-if="errors.time" class="mt-1 text-sm text-red-600">
                        {{ errors.time }}
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="emit('close')"
                        class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
                        Cancel
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span v-if="!isSubmitting">
                            {{ isEditing ? "Update" : "Create" }} Schedule
                        </span>
                        <span v-else class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
/* Button transition effects */
button {
    transition: all 0.15s ease-in-out;
}

/* Select focus styles */
select:focus {
    box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.2);
}

/* Spinner animation */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>