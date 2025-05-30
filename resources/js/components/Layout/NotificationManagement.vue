<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();
const announcements = ref([]);
const isLoading = ref(false);
const isSending = ref(false);
const announcementsList = ref(null); // Ref for announcements list container

const newAnnouncement = ref({
    subject: "",
    message: "",
});

async function submitAnnouncement() {
    if (!newAnnouncement.value.subject || !newAnnouncement.value.message) {
        toast.error("Please fill in all fields");
        return;
    }

    isSending.value = true;

    try {
        const { data } = await axios.post("/api/email-notification", {
            subject: newAnnouncement.value.subject,
            message: newAnnouncement.value.message,
        });

        toast.success("Announcement sent successfully!");
        newAnnouncement.value = { subject: "", message: "" };
        await getCreatedAnnouncements();

        // Scroll to the top of the announcements list after adding new one
        await nextTick();
        if (announcementsList.value) {
            announcementsList.value.scrollTop = 0;
        }
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to send announcement");
    } finally {
        isSending.value = false;
    }
}

async function getCreatedAnnouncements() {
    isLoading.value = true;
    try {
        const { data } = await axios.get("/api/created-announcements");
        announcements.value = data.data;
    } catch (error) {
        toast.error("Failed to load announcements");
    } finally {
        isLoading.value = false;
    }
}

function updateAnnouncement(announcement) {
    newAnnouncement.value = { ...announcement };

    // Scroll to form when selecting an announcement to edit
    const formElement = document.querySelector('.announcement-form');
    if (formElement) {
        formElement.scrollIntoView({ behavior: 'smooth' });
    }
}

onMounted(() => {
    getCreatedAnnouncements();
});
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm px-6 py-4 border-b sticky top-0 z-10">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Announcements Center</h1>
                <nav class="text-gray-500 text-sm mb-4">
                    <ol class="list-reset flex">
                        <li>
                            <a href="#" class="hover:text-blue-500"
                                >Dashboard</a
                            >
                        </li>
                        <li>
                            <span class="mx-2">/</span>
                        </li>
                        <li>Announcements</li>
                    </ol>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 my-8">
            <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Announcements History -->
                <div class="bg-white rounded-lg shadow-sm border col-span-1">
                    <div class="p-5 border-b sticky top-0 bg-white z-10">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Announcement History
                        </h2>
                    </div>
                    <div class="p-4">
                        <div v-if="isLoading" class="flex justify-center py-8">
                            <svg class="animate-spin h-8 w-8 text-lime-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                        <ul v-else ref="announcementsList"
                            class="divide-y divide-gray-100 max-h-[calc(100vh-250px)] overflow-y-auto">
                            <li v-for="(announcement, index) in announcements" :key="index"
                                @click="updateAnnouncement(announcement)"
                                class="py-3 px-2 hover:bg-gray-50 cursor-pointer transition-colors rounded group">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate"
                                            :title="announcement.subject">
                                            {{ announcement.subject }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ announcement.created_at }}
                                        </p>
                                    </div>
                                    <span class="opacity-0 group-hover:opacity-100 text-lime-600 text-xs">
                                        Click to edit
                                    </span>
                                </div>
                            </li>
                            <li v-if="announcements.length === 0" class="py-4 text-center text-gray-500">
                                No announcements yet
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- New Announcement Form -->
                <div class="bg-white rounded-lg shadow-sm border col-span-2 announcement-form">
                    <div class="p-5 border-b sticky top-0 bg-white z-10">
                        <h2 class="text-lg font-semibold text-gray-800">
                            {{ newAnnouncement.id ? 'Edit Announcement' : 'Create New Announcement' }}
                        </h2>
                    </div>
                    <div class="p-5">
                        <form @submit.prevent="submitAnnouncement">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input type="text" v-model="newAnnouncement.subject" placeholder="Announcement title"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                                    required />
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea v-model="newAnnouncement.message"
                                    placeholder="Write your announcement content here..." rows="6"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                                    required></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button v-if="newAnnouncement.id"
                                    @click="newAnnouncement = { subject: '', message: '' }" type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
                                    Cancel Edit
                                </button>
                                <button type="submit" :disabled="isSending"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 disabled:opacity-75 disabled:cursor-not-allowed">
                                    <svg v-if="isSending" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ isSending ? "Sending..." : (newAnnouncement.id ? "Update Announcement" : "Send Announcement") }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Smooth transitions */
.transition-colors {
    transition: background-color 0.2s ease;
}

/* Sticky headers */
.sticky {
    position: sticky;
}
</style>