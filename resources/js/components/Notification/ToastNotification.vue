<template>
    <div class="p-6">
      <!-- Header -->
      <h1 class="text-2xl font-bold mb-6">Toast Notification Example</h1>
  
      <!-- Buttons to Trigger Toasts -->
      <div class="space-x-4 mb-8">
        <button
          class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          @click="showToast('success', 'Success!', 'Your action was completed successfully.')"
        >
          Show Success Toast
        </button>
        <button
          class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
          @click="showToast('error', 'Error!', 'An error occurred during the operation.')"
        >
          Show Error Toast
        </button>
        <button
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          @click="showToast('info', 'Info', 'This is an informational message.')"
        >
          Show Info Toast
        </button>
        <button
          class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
          @click="showToast('warning', 'Warning!', 'Proceed with caution.')"
        >
          Show Warning Toast
        </button>
      </div>
  
      <!-- Toast Notifications Container -->
      <div class="fixed top-5 right-5 space-y-4 z-50">
        <div
          v-for="(toast, index) in toasts"
          :key="index"
          class="flex items-center max-w-sm p-4 rounded-lg shadow-lg text-white"
          :class="getToastClass(toast.type)"
        >
          <!-- Icon -->
          <span class="material-icons mr-4">{{ getToastIcon(toast.type) }}</span>
          <!-- Message -->
          <div class="flex-grow">
            <h4 class="font-bold">{{ toast.title }}</h4>
            <p class="text-sm">{{ toast.message }}</p>
          </div>
          <!-- Close Button -->
          <button
            class="ml-4 text-white hover:text-gray-300"
            @click="removeToast(index)"
          >
            ✕
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "ToastNotificationWithSample",
    data() {
      return {
        toasts: [], // Array to hold toast notifications
      };
    },
    methods: {
      /**
       * Show a toast notification
       * @param {String} type - Type of the toast ('success', 'error', 'info', 'warning')
       * @param {String} title - Title of the toast
       * @param {String} message - Message of the toast
       */
      showToast(type, title, message) {
        const toast = { type, title, message };
        this.toasts.push(toast);
  
        // Automatically remove the toast after 5 seconds
        setTimeout(() => {
          this.toasts.shift();
        }, 5000);
      },
      /**
       * Remove a toast manually
       * @param {Number} index - Index of the toast to remove
       */
      removeToast(index) {
        this.toasts.splice(index, 1);
      },
      /**
       * Get the CSS class for the toast based on its type
       * @param {String} type - Type of the toast
       * @returns {String} - CSS class
       */
      getToastClass(type) {
        switch (type) {
          case "success":
            return "bg-green-500";
          case "error":
            return "bg-red-500";
          case "info":
            return "bg-blue-500";
          case "warning":
            return "bg-yellow-500";
          default:
            return "bg-gray-500";
        }
      },
      /**
       * Get the icon for the toast based on its type
       * @param {String} type - Type of the toast
       * @returns {String} - Material Icons name
       */
      getToastIcon(type) {
        switch (type) {
          case "success":
            return "check_circle";
          case "error":
            return "error";
          case "info":
            return "info";
          case "warning":
            return "warning";
          default:
            return "notifications";
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .material-icons {
    font-size: 24px;
  }
  </style>
  