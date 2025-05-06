<script setup>
    import Axios from "axios";
    import { storeToRefs } from 'pinia';
    import { ref, onMounted, onUnmounted } from 'vue';

    import { useThemeStore } from '@/store/theme'; 
    import { useAppStore } from "@/store/useAppStore";
    import { useSidebarStore } from '@/store/useSidebarStore';

    const appStore = useAppStore();
    const sidebarStore = useSidebarStore();
    const { authUser, unreadNotifications, isLoggedIn  } = storeToRefs(appStore);
    const { sideBarOpen, selectedContent } = storeToRefs(sidebarStore);

    const searchOpen = ref(false);
    const mobileMenuOpen = ref(false);
    const notificationOpen = ref(false);
    const profileOpen = ref(false);


    const themeStore = useThemeStore();
    let intervalId = null;
    let echoListener = null; // For storing the Echo listener


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
    }

    const openProfile = () => selectedContent.value = 'profile';

    function signOut() {
        Axios.post('/api/log-out')
            .then(response => {
                authUser.value = null;
                appStore.setAuthToken('');
                isLoggedIn.value = false;
            })
            .catch(error => {
                console.error('Logout failed:', error);
            });
    }

    function markAsRead(notificationId) {
    appStore.markNotificationAsRead(notificationId).then(() => {
        appStore.fetchUnreadNotifications(); // Refresh the list after marking as read
    });
}


    onMounted(() => {
        appStore.fetchUnreadNotifications();  // Fetch notifications initially

        // Polling every 10 seconds to keep notifications updated
        intervalId = setInterval(() => {
            appStore.fetchUnreadNotifications();
        }, 10000);

        // Initialize the Echo listener
        echoListener = window.Echo.channel('some-channel')
            .listen('SomeEvent', (event) => {
                console.log(event.message);  // Handle the event data here
            });
    });

    onUnmounted(() => {
        clearInterval(intervalId); // Clear the interval when component unmounts

        // Leave the Echo channel when component is unmounted
        if (echoListener) {
            window.Echo.leave('some-channel');
        }
    });
</script>


<template>
    <div class="sticky top-0 z-40">
        <div
            class="w-full h-16 px-6 bg-gray-100  border-b flex items-center justify-between transition-colors duration-300"
            >  
            <div class="flex items-center">
             
                <button
                    class="lg:hidden text-gray-600 hover:text-lime-500 transition-colors duration-300"
                    @click="toggleSidebar"
                    title="Toggle Sidebar" >
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <div class="relative ml-4">
                    <div class="hidden md:block">
                        <input
                            type="text"
                            placeholder="Search..."
                            class="bg-white  h-10 w-64 px-5 rounded-lg border text-sm text-gray-700  focus:outline-none transition-colors duration-300" />
                        <button
                            type="submit"
                            class="absolute right-0 top-0 mt-2 mr-4 text-gray-600  hover:text-lime-500 transition-colors duration-300"
                            title="Search"
                            >
                            <i class="fa-solid fa-magnifing-glass text-xl"></i>
                        </button>
                    </div>
                    <div class="block md:hidden">
                        <template v-if="searchOpen">
                            <div class="flex items-center">
                            <button
                                @click="searchOpen = false"
                                class="mr-2 text-gray-600 hover:text-lime-500 transition-colors duration-300"
                                title="Back" >
                                <i class="fa-solid fa-moon text-xl"></i>
                            </button>
                            <input
                                type="text"
                                placeholder="Search..."
                                class="flex-1 h-10 px-4 rounded-lg border text-sm text-gray-700  bg-gray  focus:outline-none transition-colors duration-300"
                                />
                            </div>
                        </template>
                        <template v-else>
                            <button
                                @click="searchOpen = true"
                                class="text-gray-600  hover:text-lime-500 transition-colors duration-300"
                                title="Search"
                                >
                                <i class="fa-solid fa-magnifing-glass text-xl"></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-4 text-gray-600 ">
                <button
                    @click="themeStore.toggleTheme"
                    class="hover:text-lime-500 transition-colors duration-300"
                    title="Toggle Dark Mode" >
                    <i class="fa-solid fa-moon text-xl"></i>
                </button>
                <div class="hidden md:flex items-center space-x-4">
                 <!-- Notification Button -->
<div class="relative">
    <button
    @click="toggleNotifications"

        class="hover:text-lime-500 relative transition-colors duration-300 z-50"
        title="Notifications"
    >
        <i class="fa-solid fa-bell text-xl"></i>
        <span v-if="unreadNotifications.length > 0"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
            {{ unreadNotifications.length }}
        </span>
    </button>

   <!-- Notifications Dropdown -->
<!-- Notifications Dropdown -->
<div
  v-if="notificationOpen"
  class="absolute top-14 right-0 bg-white border border-gray-200 shadow-xl text-gray-700 rounded-lg w-80 max-h-96 overflow-y-auto transition-all duration-300"
>
  <!-- Debug: Show current list -->
  <pre class="text-xs text-gray-400 px-2 py-1">Debug: {{ unreadNotifications }}</pre>

  <!-- No notifications -->
  <div v-if="!unreadNotifications || unreadNotifications.length === 0" class="p-4 text-gray-500 text-sm">
    No new notifications.
  </div>

  <!-- Notifications List -->
  <ul v-else>
    <li
      v-for="notification in unreadNotifications"
      :key="notification.id"
      class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b text-sm leading-snug break-words whitespace-normal"
      @click="markAsRead(notification.id)"
    >
      <!-- Accessing the message inside 'data' --> 
      {{ notification.data.message }}
    </li>
  </ul>
</div>


</div>

                    <button
                        class="hover:text-lime-500 transition-colors duration-300"
                        title="Switch Role (Admin ↔ Instructor)" >
                        <i class="fa-solid fa-arrow-right-arrow-left text-xl"></i>
                    </button>
                    <div class="relative">
                        <img
                            :src="authUser?.profile"
                            alt="Profile Picture"
                            class="w-12 h-12 rounded-full shadow-lg cursor-pointer"
                            @click="toggleProfile"
                            title="Profile"
                            />

                        <div
                        v-if="profileOpen"


                            class="absolute top-14 right-0 bg-white border border-gray-200 shadow-xl text-gray-700 rounded-lg w-48 transition-all duration-300 cursor-pointer">
                            <div @click="openProfile" class="block px-4 py-2 hover:bg-gray-200" title="Account">
                                Account
                            </div>
                            <div class="block px-4 py-2 hover:bg-gray-200" title="Settings">
                                Settings
                            </div>
                            <div @click=signOut() class="block px-4 py-2 hover:bg-gray-200" title="Logout">
                                SignOut
                            </div>
                        </div>
                    </div>

                </div>
                <div class="relative md:hidden">
                    <button
                    @click="dropDownOpen = dropDownOpen === 'profile' ? null : 'profile'"

                        class="hover:text-lime-500 transition-colors duration-300"
                        title="More Options" >
                        <i class="fa-solid fa-ellipsis-vertical text-xl"></i>
                    </button>
                    <div
                        v-if="mobileMenuOpen"
                        class="absolute right-0 mt-2 bg-white  border border-gray-200  shadow-xl text-gray-700  rounded-lg w-48 transition-all duration-300"
                        >
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Notifications" >
                            <i class="fa-solid fa-bell text-xl"></i>
                            <span class="ml-2">Notifications</span>
                        </a>
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Quick Messaging" >
                            <i class="fa-solid fa-envelope text-xl"></i>
                            <span class="ml-2">Messaging</span>
                        </a>
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Switch Role" >
                            <i class="fa-solid fa-arrow-right-arrow-left text-xl"></i>
                            <span class="ml-2">Switch Role</span>
                        </a>
                        <a
                            href="#"
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Settings" >
                            <i class="fa-solid fa-gear text-xl"></i>
                            <span class="ml-2">Settings</span>
                        </a>
                        <a
                            @click="openProfile" 
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="Profile"
                            >
                            <i class="fa-solid fa-circle-user text-xl"></i>
                            <span class="ml-2">Profile</span>
                        </a>
                        <a
                            @click=signOut()
                            class="block px-4 py-2 hover:bg-gray-200 "
                            title="signOut"
                            >
                            <i class="fa-solid fa-circle-user text-xl"></i>
                            <span class="ml-2">signOut</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    .material-icons {
    font-size: 24px;
    transition: color 0.3s;
    }
</style>
