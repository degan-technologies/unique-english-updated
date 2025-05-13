<script setup>
import Axios from "axios";
import { ref, computed, nextTick, onMounted } from "vue";

const announcements = ref([]);

const newAnnouncement = ref({
    subject: "",
    message: "",
});

function submitAnnouncement() {
    const data = {
        subject: newAnnouncement.value.subject,
        message: newAnnouncement.value.message,
    };

    Axios.post("/api/email-notification", data).then((res) => {
        newAnnouncement.value = {
            title: "",
            message: "",
            scheduledAt: "",
        };
    });
}

function getCreatedAnnouncements() {
    Axios.get("/api/created-announcements").then((res) => {
        announcements.value = res.data.data;
    });
}

function updateAnnouncement(announcement) {
    newAnnouncement.value = { ...announcement };
}

onMounted(() => {
    getCreatedAnnouncements();
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Header -->
        <header
            class="bg-white shadow px-6 py-4 flex justify-between items-center"
        >
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Notifications & Messaging
                </h1>
                <p class="text-sm text-gray-500">
                    Communicate seamlessly with students and staff
                </p>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 p-4">
            <!-- Announcements Tab -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Announcements History -->
                <div class="bg-white rounded col-span-1 shadow p-4">
                    <h2 class="text-xl font-semibold mb-4">
                        Created Announcements
                    </h2>
                    <ul
                        class="divide-y divide-gray-200 max-h-96 overflow-y-auto"
                    >
                        <li
                            v-for="(announcement, index) in announcements"
                            :key="index"
                            @click="updateAnnouncement(announcement)"
                            class="py-3 flex justify-between items-center"
                        >
                            <div>
                                <p class="font-medium">
                                    {{ announcement.subject }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ announcement.date }}
                                </p>
                            </div>
                            <span
                                :class="{
                                    'bg-green-100 text-green-700':
                                        announcement.status === 'sent',
                                    'bg-yellow-100 text-yellow-700':
                                        announcement.status === 'pending',
                                    'bg-red-100 text-red-700':
                                        announcement.status === 'failed',
                                }"
                                class="px-3 py-1 rounded-full text-xs font-medium"
                            >
                                {{ announcement.status }}
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- New Announcement Form -->
                <div class="bg-white col-span-2 rounded shadow p-4">
                    <h2 class="text-xl font-semibold mb-4">
                        Create New Announcement
                    </h2>
                    <form>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-1"
                                >Subject</label
                            >
                            <input
                                type="text"
                                v-model="newAnnouncement.subject"
                                placeholder="Enter announcement title"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-lime-300"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-1"
                                >Message</label
                            >
                            <textarea
                                v-model="newAnnouncement.message"
                                placeholder="Enter your message"
                                class="w-full border border-gray-300 rounded px-3 py-2 h-32 focus:outline-none focus:ring focus:border-blue-300"
                                required
                            ></textarea>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button
                                @click="submitAnnouncement()"
                                class="bg-lime-600 text-white px-4 py-2 rounded hover:bg-lime-700 transition"
                            >
                                Send Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for chat window */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}
</style>
