<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
import JetsiLive from "@/components/Live/JetsiLive.vue";
import Spinner from "@/components/Layout/Spinner.vue";

const sessions = ref([]);
const selectedRoom = ref(false);
const isLoading = ref(true);

function getMySchedules() {
    isLoading.value = true;
    Axios.get("/api/student-schedule")
        .then((res) => {
            sessions.value = res.data.data;
        })
        .finally(() => {
            isLoading.value = false;
        });
}

function joinSession(room) {
    selectedRoom.value = room;
}

onMounted(() => {
    getMySchedules();
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
        <!-- Live Stream View -->
        <div
            v-if="selectedRoom"
            class="fixed inset-0 z-50 flex items-center justify-center bg-white animate-fadeIn"
        >
            <div class="relative w-full h-full">
                <JetsiLive
                    :selectedRoom="selectedRoom"
                    @closeStream="selectedRoom = false"
                />
            </div>
        </div>

        <!-- Spinner -->
        <div v-if="isLoading" class="flex justify-center items-center h-64">
            <Spinner />
        </div>

        <!-- No Sessions -->
        <div
            v-else-if="sessions.length === 0"
            class="text-center text-gray-600 py-12 text-lg"
        >
            No sessions found.
        </div>

        <!-- Session Cards -->
        <div
            v-else
            class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 animate-fadeIn"
        >
            <div
                v-for="session in sessions"
                :key="session.id"
                class="bg-white border border-gray-200 rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow duration-300"
            >
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">
                            {{ session.day }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ session.schedule_time }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="editSession(session)"
                            class="text-gray-500 hover:text-lime-600 text-lg"
                        >
                            <i class="fas fa-edit"></i>
                        </button>
                        <span
                            :class="session?.color"
                            class="text-xs font-medium px-2 py-1 rounded capitalize bg-gray-100"
                        >
                            {{ session.status }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        class="bg-lime-600 hover:bg-lime-700 text-white px-4 py-2 rounded-md font-semibold text-sm transition duration-200"
                        @click="joinSession(session?.room_name)"
                    >
                        Start
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
