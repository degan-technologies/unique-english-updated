<template>
    <div class="p-6">
      <!-- Notification Bell with Badge -->
      <div class="relative flex items-center cursor-pointer" @click="toggleList">
        <span class="material-icons">notifications</span>
        <span
          v-if="unreadCount > 0"
          class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
        >
          {{ unreadCount }}
        </span>
      </div>
  
      <!-- Notification List -->
      <div v-if="showList" class="bg-white shadow-md rounded-lg mt-4 w-96">
        <div v-if="notifications.length" class="divide-y divide-gray-200">
          <div
            v-for="(notification, index) in notifications"
            :key="index"
            class="p-4 cursor-pointer hover:bg-gray-50"
            :class="{ 'font-bold': !notification.read }"
            @click="openNotification(index)"
          >
            <h3 class="text-sm">{{ notification.title }}</h3>
            <p class="text-xs text-gray-500">
              {{ timeAgo(notification.timestamp) }}
            </p>
          </div>
        </div>
        <div v-else class="p-4 text-center text-gray-500">No Notifications</div>
      </div>
  
      <!-- Notification Details -->
      <div v-if="showDetail" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white w-11/12 sm:w-1/2 p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-bold mb-2">{{ selectedNotification.title }}</h3>
          <p class="text-gray-700 mb-4">{{ selectedNotification.message }}</p>
          <p class="text-sm text-gray-500">Sent: {{ timeAgo(selectedNotification.timestamp) }}</p>
          <button
            class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            @click="closeDetail"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        notifications: [
          {
            title: "Welcome to the App",
            message: "Thank you for signing up! Explore the features we offer.",
            timestamp: Date.now() - 3600000, // 1 hour ago
            read: false,
          },
          {
            title: "Reminder: Meeting Today",
            message: "Don't forget your team meeting at 2 PM today.",
            timestamp: Date.now() - 7200000, // 2 hours ago
            read: true,
          },
          {
            title: "New Feature Launched",
            message: "Check out our new feature: Dark Mode! Enable it in settings.",
            timestamp: Date.now() - 86400000, // 1 day ago
            read: false,
          },
          {
            title: "Account Update",
            message: "Your account details were successfully updated.",
            timestamp: Date.now() - 172800000, // 2 days ago
            read: true,
          },
        ],
        showList: false,
        showDetail: false,
        selectedNotification: null,
      };
    },
    computed: {
      unreadCount() {
        return this.notifications.filter((notification) => !notification.read).length;
      },
    },
    methods: {
      toggleList() {
        this.showList = !this.showList;
      },
      openNotification(index) {
        const notification = this.notifications[index];
        if (!notification.read) {
          this.notifications[index].read = true;
        }
        this.selectedNotification = notification;
        this.showDetail = true;
      },
      closeDetail() {
        this.showDetail = false;
      },
      timeAgo(timestamp) {
        const seconds = Math.floor((Date.now() - timestamp) / 1000);
        const intervals = {
          year: 31536000,
          month: 2592000,
          day: 86400,
          hour: 3600,
          minute: 60,
          second: 1,
        };
        for (const [unit, value] of Object.entries(intervals)) {
          const count = Math.floor(seconds / value);
          if (count > 0) {
            return `${count} ${unit}${count > 1 ? "s" : ""} ago`;
          }
        }
        return "Just now";
      },
    },
  };
  </script>
  
  <style scoped>
  .material-icons {
    font-size: 24px;
    color: #333;
  }
  
  .cursor-pointer:hover {
    color: #555;
  }
  </style>
  