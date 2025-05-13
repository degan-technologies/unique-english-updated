<script setup>
import Axios from "axios";
import { storeToRefs } from "pinia";
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";

import { useThemeStore } from "@/store/theme";
import { useAppStore } from "@/store/useAppStore";
import { useSidebarStore } from "@/store/useSidebarStore";

const router = useRouter();
const appStore = useAppStore();
const sidebarStore = useSidebarStore();
const { authUser, unreadNotifications, readNotifications, isLoggedIn } =
    storeToRefs(appStore);
const { sideBarOpen, selectedContent } = storeToRefs(sidebarStore);

const searchOpen = ref(false);
const mobileMenuOpen = ref(false);
const notificationOpen = ref(false);
const profileOpen = ref(false);
const showAllNotifications = ref(false);
const currentNotification = ref(null);
const showNotificationModal = ref(false);

const themeStore = useThemeStore();
let intervalId = null;
let echoListener = null;

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
    if (notificationOpen.value) {
        appStore.fetchUnreadNotifications();
        if (showAllNotifications.value) {
            appStore.fetchReadNotifications();
        }
    }
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

async function markAsRead(notificationId) {
    try {
        await appStore.markNotificationAsRead(notificationId);
        await appStore.fetchUnreadNotifications();
        if (showAllNotifications.value) {
            await appStore.fetchReadNotifications();
        }
    } catch (error) {
        console.error("Error marking notification as read:", error);
    }
}

async function markAsUnread(notificationId) {
    try {
        await appStore.markNotificationAsUnread(notificationId);
        await appStore.fetchUnreadNotifications();
        if (showAllNotifications.value) {
            await appStore.fetchReadNotifications();
        }
    } catch (error) {
        console.error("Error marking notification as unread:", error);
    }
}

function showNotificationDetails(notification) {
    currentNotification.value = notification;
    showNotificationModal.value = true;
    notificationOpen.value = false; // Close dropdown when opening modal
}

async function handleNotificationAction(notification) {
    // Mark as read if unread
    if (!notification.read_at) {
        await markAsRead(notification.id);
    }

    // Close the modal
    showNotificationModal.value = false;

    // Handle the notification action
    switch (notification.type) {
        case "App\\Notifications\\NewMessage":
            router.push(`/messages/${notification.data.conversation_id}`);
            break;
        case "App\\Notifications\\NewComment":
            router.push(`/posts/${notification.data.post_id}`);
            break;
        case "App\\Notifications\\NewLike":
            router.push(`/posts/${notification.data.post_id}`);
            break;
        default:
            console.log("Notification action:", notification);
            break;
    }
}

function closeNotificationModal() {
    showNotificationModal.value = false;
}

function toggleShowAllNotifications() {
    showAllNotifications.value = !showAllNotifications.value;
    if (showAllNotifications.value) {
        appStore.fetchReadNotifications();
    } else {
        appStore.fetchUnreadNotifications();
    }
}

onMounted(() => {
    appStore.fetchUnreadNotifications();

    // Setup real-time notifications with Echo
    if (window.Echo && authUser.value?.id) {
        echoListener = window.Echo.private(
            `App.Models.User.${authUser.value.id}`
        ).notification((notification) => {
            console.log("New notification received:", notification);
            appStore.fetchUnreadNotifications();
            if (showAllNotifications.value) {
                appStore.fetchReadNotifications();
            }
        });
    }
});

onUnmounted(() => {
    clearInterval(intervalId);
    if (echoListener && authUser.value?.id) {
        window.Echo.leave(`App.Models.User.${authUser.value.id}`);
    }
});
</script>

<template>
    <div class="sticky top-0 z-40">
        <div
            class="w-full h-16 px-6 bg-gray-100 border-b flex items-center justify-between transition-colors duration-300"
        >
            <div class="flex items-center">
                <button
                    class="lg:hidden text-gray-600 hover:text-lime-500 transition-colors duration-300"
                    @click="toggleSidebar"
                    title="Toggle Sidebar"
                >
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <div class="relative ml-4">
                    <div class="hidden md:block">
                        <input
                            type="text"
                            placeholder="Search..."
                            class="bg-white h-10 w-64 px-5 rounded-lg border text-sm text-gray-700 focus:outline-none transition-colors duration-300"
                        />
                        <button
                            type="submit"
                            class="absolute right-0 top-0 mt-2 mr-4 text-gray-600 hover:text-lime-500 transition-colors duration-300"
                            title="Search"
                        >
                            <i class="fa-solid fa-magnifying-glass text-xl"></i>
                        </button>
                    </div>
                    <div class="block md:hidden">
                        <template v-if="searchOpen">
                            <div class="flex items-center">
                                <button
                                    @click="searchOpen = false"
                                    class="mr-2 text-gray-600 hover:text-lime-500 transition-colors duration-300"
                                    title="Back"
                                >
                                    <i
                                        class="fa-solid fa-arrow-left text-xl"
                                    ></i>
                                </button>
                                <input
                                    type="text"
                                    placeholder="Search..."
                                    class="flex-1 h-10 px-4 rounded-lg border text-sm text-gray-700 bg-gray focus:outline-none transition-colors duration-300"
                                />
                            </div>
                        </template>
                        <template v-else>
                            <button
                                @click="searchOpen = true"
                                class="text-gray-600 hover:text-lime-500 transition-colors duration-300"
                                title="Search"
                            >
                                <i
                                    class="fa-solid fa-magnifying-glass text-xl"
                                ></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-4 text-gray-600">
                <!-- Notification Button -->
                <div class="relative">
                    <button
                        @click="toggleNotifications"
                        class="hover:text-lime-500 relative transition-colors duration-300 z-50"
                        title="Notifications"
                    >
                        <i class="fa-solid fa-bell text-xl"></i>
                        <span
                            v-if="unreadNotifications.length > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"
                        >
                            {{ unreadNotifications.length }}
                        </span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div
                        v-if="notificationOpen"
                        class="absolute top-12 right-0 bg-white border border-gray-200 shadow-xl text-gray-700 rounded-lg w-80 max-h-96 overflow-y-auto transition-all duration-300 z-50"
                        v-click-outside="() => (notificationOpen = false)"
                    >
                        <div
                            class="sticky top-0 bg-white p-2 border-b flex justify-between items-center"
                        >
                            <h3 class="font-semibold text-gray-700">
                                Notifications
                            </h3>
                            <button
                                @click="toggleShowAllNotifications"
                                class="text-xs text-lime-600 hover:text-lime-800"
                            >
                                {{
                                    showAllNotifications
                                        ? "Show Unread Only"
                                        : "Show All"
                                }}
                            </button>
                        </div>

                        <!-- No notifications -->
                        <div
                            v-if="
                                unreadNotifications.length === 0 &&
                                (!showAllNotifications ||
                                    readNotifications.length === 0)
                            "
                            class="p-4 text-gray-500 text-sm"
                        >
                            No new notifications.
                        </div>

                        <!-- Unread Notifications -->
                        <ul v-if="unreadNotifications.length > 0">
                            <li
                                v-for="notification in unreadNotifications"
                                :key="notification.id"
                                class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b text-sm leading-snug break-words whitespace-normal"
                                @click="showNotificationDetails(notification)"
                            >
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
                                    <span
                                        class="w-2 h-2 bg-lime-500 rounded-full mt-1 flex-shrink-0"
                                    ></span>
                                </div>
                            </li>
                        </ul>

                        <!-- Read Notifications (when showAll is true) -->
                        <ul
                            v-if="
                                showAllNotifications &&
                                readNotifications.length > 0
                            "
                        >
                            <li
                                v-for="notification in readNotifications"
                                :key="notification.id"
                                class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b text-sm leading-snug break-words whitespace-normal bg-gray-50"
                                @click="showNotificationDetails(notification)"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-gray-600">
                                            {{ notification.data.message }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{
                                                formatTime(
                                                    notification.created_at
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <button
                                        @click.stop="
                                            markAsUnread(notification.id)
                                        "
                                        class="text-xs text-gray-400 hover:text-gray-600 ml-2"
                                        title="Mark as unread"
                                    >
                                        <i class="fa-solid fa-envelope"></i>
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Profile Button -->
                <div class="relative">
                    <img
                        :src="
                            authUser?.profile ||
                            'https://via.placeholder.com/150'
                        "
                        alt="Profile Picture"
                        class="w-10 h-10 rounded-full shadow-lg cursor-pointer"
                        @click="toggleProfile"
                        title="Profile"
                    />

                    <div
                        v-if="profileOpen"
                        class="absolute top-14 right-0 bg-white border border-gray-200 shadow-xl text-gray-700 rounded-lg w-48 transition-all duration-300 cursor-pointer z-50"
                        v-click-outside="() => (profileOpen = false)"
                    >
                        <div
                            @click="openProfile"
                            class="block px-4 py-2 hover:bg-gray-200"
                            title="Account"
                        >
                            Account
                        </div>
                        <div
                            class="block px-4 py-2 hover:bg-gray-200"
                            title="Settings"
                        >
                            Settings
                        </div>
                        <div
                            @click="signOut()"
                            class="block px-4 py-2 hover:bg-gray-200"
                            title="Logout"
                        >
                            SignOut
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Notification Modal -->
        <div
            v-if="showNotificationModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold">Notification Details</h3>
                </div>
                <div class="p-4" v-if="currentNotification">
                    <h6 class="mb-3 font-medium">
                        {{ currentNotification.data.message }}
                    </h6>
                    <p class="text-gray-500 text-sm mb-2">
                        Received:
                        {{ formatTime(currentNotification.created_at) }}
                    </p>
                    <div v-if="currentNotification.data.details" class="mt-3">
                        <p class="text-sm text-gray-600">
                            {{ currentNotification.data.details }}
                        </p>
                    </div>
                </div>
                <div class="p-4 border-t flex justify-end space-x-2">
                    <button
                        @click="closeNotificationModal"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded"
                    >
                        Close
                    </button>
                    <!-- <button
                        @click="handleNotificationAction(currentNotification)"
                        class="px-4 py-2 bg-lime-500 text-white hover:bg-lime-600 rounded"
                    >
                        {{
                            currentNotification?.read_at
                                ? "Open"
                                : "Mark as Read & Open"
                        }}
                    </button> -->
                </div>
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
