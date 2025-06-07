<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
 
import { useAppStore } from "@/store/useAppStore";
import { useSidebarStore } from "@/store/useSidebarStore";

const router = useRouter();
const appStore = useAppStore();
const sidebarStore = useSidebarStore();
const { authUser, unreadNotifications, notifications, readNotifications, isLoggedIn } =
    storeToRefs(appStore);
const { sideBarOpen, selectedContent } = storeToRefs(sidebarStore);

const searchOpen = ref(false); 
const notificationsLoading = ref(false);
const notificationOpen = ref(false);
const profileOpen = ref(false);
const showAllNotifications = ref(false);
const currentNotification = ref(null);
const showNotificationModal = ref(false); 

function formatTime(date) {
    const options = {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    };
    return new Date(date).toLocaleDateString(undefined, options);
}

const toggleNotifications = () => {
        notificationOpen.value = !notificationOpen.value;
        profileOpen.value = false; 
    };
    
    const toggleProfile = () => {
        profileOpen.value = !profileOpen.value;
        notificationOpen.value = false;
    };

    const toggleSidebar = () => {
        sidebarStore.toggleSidebar();
    };

    const openProfile = () => {
        selectedContent.value = "profile";
        profileOpen.value = false;
    };

    async function signOut() {
        try {
            await Axios.post("/api/log-out");
            authUser.value = null;
            appStore.setAuthToken("");
            isLoggedIn.value = false;
        } catch (error) {
            console.error("Logout failed:", error);
        }
    } 

    function showNotificationDetails(notification) {
        currentNotification.value = notification;
        showNotificationModal.value = true;
        notificationOpen.value = false;  

        if(notification.data.read_at == null){
            appStore.markNotificationAsRead(notification.id);
        }
    }
 

    function closeNotificationModal() {
        showNotificationModal.value = false;
    }

    function toggleShowAllNotifications() {
        if(notifications.length === 0) return;
        showAllNotifications.value = !showAllNotifications.value; 
    }

    const refreshNOtification = () =>{
        notificationsLoading.value = true;
        appStore.fetchUnreadNotifications();

        notificationsLoading.value = false;
        return;
    }

    onMounted(() => {
        appStore.fetchUnreadNotifications(); 
    }); 
</script>

<template>
    <div class="sticky top-0 z-40">
        <div
            class="w-full h-16 px-6 bg-gray-100 border-b flex items-center justify-between md:justify-end transition-colors duration-300">
            <div class="flex items-center">
                <button class="md:hidden text-gray-600 hover:text-lime-500 transition-colors duration-300"
                    @click="toggleSidebar" title="Toggle Sidebar">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button> 
            </div>
            <div class="flex items-center space-x-4 text-gray-600">
                <!-- Notification Button -->
                <div class="relative">
                    <button @click="toggleNotifications"
                        class="hover:text-lime-500 relative transition-colors duration-300 z-50" title="Notifications">
                        <i class="fa-solid fa-bell text-xl"></i>
                        <span 
                            v-if="unreadNotifications > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                            {{ unreadNotifications }}
                        </span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div v-if="notificationOpen"
                        class="absolute top-12 right-0 bg-white border border-gray-200 shadow-xl text-gray-700 rounded-lg w-80 max-h-96 overflow-y-auto transition-all duration-300 z-50"
                        v-click-outside="() => (notificationOpen = false)">
                        <div class="sticky top-0 bg-white p-2 border-b flex justify-between items-center">
                            <h3 class="font-semibold text-gray-700">
                                Notifications
                            </h3>
                            <div class="flex flex-row gap-4">
                                <button 
                                    @click="refreshNotification"
                                    class="text-gray-500 hover:text-gray-800"
                                    :disabled="notificationsLoading"
                                >
                                    <i
                                        class="fas fa-sync-alt transition-transform"
                                        :class="{ 'animate-spin': notificationsLoading }"
                                    />
                                </button>

                            <button @click="toggleShowAllNotifications"
                                class="text-xs text-lime-600 hover:text-lime-800">
                                {{
                                    showAllNotifications
                                        ? "Show Unread Only"
                                        : "Show All"
                                }}
                            </button>
                        </div>
                        </div> 
                        <!-- No notifications -->
                        <div v-if="
                            notifications.length === 0 &&
                            (!showAllNotifications ||
                                readNotifications.length === 0)
                        " class="p-4 text-gray-500 text-sm">
                            No new notifications.
                        </div>

                        <!-- Unread Notifications -->
                        <ul v-if="notifications.length > 0">
                            <li v-for="notification in notifications" :key="notification.id"
                                :class="{
                                    'hidden': showAllNotifications && notification.data.read_at,
                                }"
                                class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b text-sm leading-snug break-words whitespace-normal"
                                @click="showNotificationDetails(notification)">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">
                                            {{ notification.data.message }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{
                                                formatTime(
                                                    notification.created_at
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <span v-if="currentNotification?.id == notification?.id ? false : !notification.read_at" class="w-2 h-2 bg-lime-500 rounded-full mt-1 flex-shrink-0"></span>
                                </div>
                            </li>
                        </ul> 
                    </div>
                </div>

                <!-- Profile Button -->
                <div class="relative">
                    <img :src="authUser?.profile ||
                        'https://via.placeholder.com/150'
                        " alt="Profile Picture" class="w-10 h-10 rounded-full shadow-lg cursor-pointer"
                        @click="toggleProfile" title="Profile" />

                    <div v-if="profileOpen"
                        class="absolute top-14 right-0 bg-white border border-gray-200 shadow-xl text-gray-700 rounded-lg w-48 transition-all duration-300 cursor-pointer z-50"
                        v-click-outside="() => (profileOpen = false)">
                        <div @click="openProfile" class="block px-4 py-2 hover:bg-gray-200" title="Account">
                            Account
                        </div>
                        <div class="block px-4 py-2 hover:bg-gray-200" title="Settings">
                            Settings
                        </div>
                        <div @click="signOut()" class="block px-4 py-2 hover:bg-gray-200" title="Logout">
                            SignOut
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Custom Notification Modal -->
        <div v-if="showNotificationModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold">Notification Details</h3>
                </div>
                <div class="p-4" v-if="currentNotification">
                    <h1 class="text-gray-600 my-2 font-bold"> {{ currentNotification.data.title }} </h1>
                    <h6 class="mb-3 font-normal">
                        {{ currentNotification.data.message }}
                    </h6>
                    <p class="text-gray-500 text-sm mb-2">
                        Date Received:
                        {{ formatTime(currentNotification.created_at) }}
                    </p>
                    <div v-if="currentNotification.data.details" class="mt-3">
                        <p class="text-sm text-gray-600">
                            {{ currentNotification.data.details }}
                        </p>
                    </div>
                </div>
                <div class="p-4 border-t flex justify-end space-x-2">
                    <button @click="closeNotificationModal" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                        Close
                    </button>  
                </div>
            </div>
        </div>
</template>

<style scoped>
.notification-item.unread {
    background-color: #f8f9fa;
}

.notification-dot {
    width: 8px;
    height: 8px;
    background-color: #28a745;
    border-radius: 50%;
    display: inline-block;
    margin-left: 5px;
}
</style>
