<script setup>
import { onMounted, computed, ref, watch } from "vue";
import Axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();
const rooms = ref([]);
const days = ref([
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
]);
const hours = Array.from({ length: 12 }, (_, i) =>
    String(i + 1).padStart(2, "0")
);
const minutes = Array.from({ length: 12 }, (_, i) =>
    String(i * 5).padStart(2, "0")
);
const periods = ["AM", "PM"];

const props = defineProps({
    selectedSession: Object,
});
const emit = defineEmits(["scheduleUpdated"]);

const errors = ref({ day: "", time: "", room: "" });
const form = ref({
    id: null,
    day: "",
    hour: "01",
    minute: "00",
    period: "AM",
    live_room_id: null,
});

const isEditing = computed(() => !!props.selectedSession);

// Watch for changes in selectedSession and update form accordingly
watch(
    () => props.selectedSession,
    (session) => {
        if (session) {
            form.value = {
                id: session.id,
                day: session.day,
                hour: session.time.split(":")[0],
                minute: session.time.split(":")[1].split(" ")[0],
                period: session.time.split(" ")[1],
                live_room_id: session.live_room_id,
            };
        } else {
            form.value = {
                id: null,
                day: "",
                hour: "01",
                minute: "00",
                period: "AM",
                live_room_id: null,
            };
        }
    },
    { immediate: true }
);

const formatTime = () => {
    if (form.value.hour && form.value.minute && form.value.period) {
        return `${form.value.hour}:${form.value.minute} ${form.value.period}`;
    }
    return "";
};

const handleSubmit = async () => {
    errors.value = { day: "", time: "", room: "" };

    if (!form.value.day) {
        errors.value.day = "Please select a day.";
    }
    if (!form.value.hour || !form.value.minute) {
        errors.value.time = "Please select a complete time.";
    }
    if (!form.value.live_room_id) {
        errors.value.room = "Please select a room.";
    }

    if (errors.value.day || errors.value.time || errors.value.room) return;

    try {
        const formattedTime = formatTime();
        const payload = {
            day: form.value.day,
            time: formattedTime,
            live_room_id: form.value.live_room_id,
        };

        let response;
        if (isEditing.value) {
            response = await Axios.put(
                `/api/schedules/${form.value.id}`,
                payload
            );
            emit("scheduleUpdated");
        } else {
            response = await Axios.post("/api/schedules", payload);
        }

        toast.success(
            response.data.message ||
                `Schedule ${
                    isEditing.value ? "updated" : "added"
                } successfully!`
        );

        // Reset form if not editing
        if (!isEditing.value) {
            form.value = {
                id: null,
                day: "",
                hour: "01",
                minute: "00",
                period: "AM",
                live_room_id: null,
            };
        }
    } catch (error) {
        toast.error(
            error.response?.data?.message ||
                `Failed to ${isEditing.value ? "update" : "add"} schedule.`
        );
    }
};

const fetchRooms = async () => {
    try {
        const response = await Axios.get("/api/get-rooms");
        rooms.value = response.data.data;
    } catch (error) {
        console.error("Error fetching rooms:", error);
        toast.error("Failed to fetch rooms");
    }
};

onMounted(() => {
    fetchRooms();
});
</script>

<template>
    <div
        class="bg-white p-4 max-h-full overflow-auto scrollbar rounded-lg w-full max-w-md sm:max-w-lg md:max-w-xl"
    >
        <h2 class="text-2xl font-bold text-center text-lime-700 mb-6">
            {{ isEditing ? "Edit" : "Add" }} Schedule
        </h2>
        <form @submit.prevent="handleSubmit">
            <!-- Room Selection -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">
                    Select Room
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-2">
                    <button
                        v-for="room in rooms"
                        :key="room.id"
                        type="button"
                        @click="form.live_room_id = room.id"
                        :class="[
                            'p-2 rounded-lg text-center transition-colors',
                            form.live_room_id === room.id
                                ? 'bg-lime-700 text-white'
                                : 'bg-gray-100 hover:bg-lime-100',
                        ]"
                    >
                        {{ room.class_name }}
                    </button>
                </div>
                <p v-if="errors.room" class="text-red-600 text-sm mt-1">
                    {{ errors.room }}
                </p>
            </div>

            <!-- Day Selection -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">
                    Day
                </label>
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
                                : 'bg-gray-100 hover:bg-lime-100',
                        ]"
                    >
                        {{ day }}
                    </button>
                </div>
                <p v-if="errors.day" class="text-red-600 text-sm mt-1">
                    {{ errors.day }}
                </p>
            </div>

            <!-- Time Selection -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700"
                    >Time</label
                >
                <div class="grid grid-cols-3 gap-4">
                    <!-- Hours -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-1"
                            >Hour</label
                        >
                        <select
                            v-model="form.hour"
                            class="w-full border p-2 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-500"
                        >
                            <option
                                v-for="hour in hours"
                                :key="hour"
                                :value="hour"
                            >
                                {{ hour }}
                            </option>
                        </select>
                    </div>
                    <!-- Minutes -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-1"
                            >Minute</label
                        >
                        <select
                            v-model="form.minute"
                            class="w-full border p-2 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-lime-500"
                        >
                            <option
                                v-for="minute in minutes"
                                :key="minute"
                                :value="minute"
                            >
                                {{ minute }}
                            </option>
                        </select>
                    </div>
                    <!-- AM/PM -->
                    <div>
                        <label class="block text-xs text-gray-600 mb-1"
                            >Period</label
                        >
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="period in periods"
                                :key="period"
                                type="button"
                                @click="form.period = period"
                                :class="[
                                    'p-2 rounded-lg text-center transition-colors',
                                    form.period === period
                                        ? 'bg-lime-700 text-white'
                                        : 'bg-gray-100 hover:bg-lime-100',
                                ]"
                            >
                                {{ period }}
                            </button>
                        </div>
                    </div>
                </div>
                <p v-if="errors.time" class="text-red-600 text-sm mt-1">
                    {{ errors.time }}
                </p>
            </div>

            <div class="flex justify-center">
                <button
                    @click="handleSubmit"
                    type="button"
                    class="px-6 py-2 bg-lime-700 text-white rounded-lg hover:bg-lime-800 transition focus:outline-none focus:ring-2 focus:ring-lime-700"
                >
                    {{ isEditing ? "Update" : "Add" }} Schedule
                </button>
            </div>
        </form>
    </div>
</template>
