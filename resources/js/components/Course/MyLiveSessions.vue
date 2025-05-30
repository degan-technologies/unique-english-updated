<script setup>
import Axios from "axios";
import { ref, onMounted } from "vue";
import JetsiLive from "@/components/Live/JetsiLive.vue";
import Spinner from "@/components/Layout/Spinner.vue";

// Reactive state
const sessions = ref([]);
const privateSchedule = ref([]);
const startSelectedSchedule = ref(false);
const isLoading = ref(false);

// Format end time by adding 1 hour
const formatEndTime = (time) => {
    return time.replace(/(\d+):/, (match, hour) =>
        `${(parseInt(hour) + 1).toString().padStart(2, '0')}:`);
};

// Fetch all schedule data
const fetchSchedules = async () => {
    try {
        isLoading.value = true;
        const [groupRes, privateRes] = await Promise.all([
            Axios.get("/api/student-schedule"),
            Axios.get("/api/private-student-schedule")
        ]);
        sessions.value = groupRes.data.data;
        privateSchedule.value = privateRes.data.data;
    } catch (error) {
        console.error("Error fetching schedules:", error);
    } finally {
        isLoading.value = false;
    }
};

function joinNewSession(newSession) {
    if(newSession.status !== 'live'){
        toast.warning("This room is not live yet", { timeout: 3000 });
        return;
    }

    if (!newSession) {
        toast.warning("No room selected", { timeout: 3000 });
        return;
    }

    Axios.post(`/api/store-student-attendance/${newSession.id}`)
        .then(res => {  
            startSelectedSchedule.value = newSession; 
            toast.success(res.data.data, { timeout: 3000 });
        })
        .catch(error => {
            toast.warning("No room selected", { timeout: 3000 });
            return;
        })  
}

onMounted(fetchSchedules);
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
        <!-- Live Stream Modal Overlay -->
        <div v-if="startSelectedSchedule" class="fixed inset-0 z-50 bg-white">
            <JetsiLive :startSelectedSchedule="startSelectedSchedule" @closeStream="startSelectedSchedule = null"
                class="w-full h-full animate-fadeIn" />
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto">
            <!-- Loading State -->
            <div v-if="isLoading" class="flex justify-center items-center h-64">
                <Spinner />
            </div>

            <!-- Empty State -->
            <div v-else-if="!sessions.length && !privateSchedule.length" class="text-center py-20">
                <i class="fa-regular fa-calendar-xmark text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-500">No sessions scheduled</h3>
                <p class="text-gray-400 mt-1">Check back later for updates</p>
            </div>

            <!-- Sessions Content -->
            <div v-else class="space-y-8">
                <!-- Group Sessions Section -->
                <section v-if="sessions.length" class="animate-fadeIn">
                    <h2 class="text-xl font-semibold text-gray-700 mb-5 px-2">Group Classes</h2>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div v-for="session in sessions" :key="'group-' + session.id"
                            class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all p-5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-800">{{ session.day }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ session.schedule_time }} - {{ formatEndTime(session.schedule_time) }}
                                    </p>
                                </div>
                                <span :class="session.color"
                                    class="text-xs font-medium px-2 py-1 rounded-full capitalize">
                                    {{ session.status }}
                                </span>
                            </div>

                            <div class="mt-5 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-gray-600">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                    <span>{{ session.instructor_name }}</span>
                                </div>
                                <button @click="joinNewSession(session)"
                                    class="bg-lime-600 hover:bg-lime-700 text-white px-3 py-1.5 rounded-md text-sm font-medium transition-colors">
                                    Join
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Private Sessions Section -->
                <section v-if="privateSchedule.length" class="animate-fadeIn">
                    <h2 class="text-xl font-semibold text-gray-700 mb-5 px-2">Private Sessions</h2>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div v-for="session in privateSchedule" :key="'private-' + session.id"
                            class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all p-5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-800">{{ session.day }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ session.schedule_time }} - {{ formatEndTime(session.schedule_time) }}
                                    </p>
                                </div>
                                <span :class="session.color"
                                    class="text-xs font-medium px-2 py-1 rounded-full capitalize">
                                    {{ session.status }}
                                </span>
                            </div>

                            <div class="mt-5 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-gray-600">
                                    <i class="fa-solid fa-user-tie"></i>
                                    <span>{{ session.instructor_name }}</span>
                                </div>
                                <button @click="joinNewSession(session)"
                                    class="bg-lime-600 hover:bg-lime-700 text-white px-3 py-1.5 rounded-md text-sm font-medium transition-colors">
                                    Join
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>