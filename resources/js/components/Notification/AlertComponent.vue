<template>
    <div class="p-6">
      <!-- Header -->
      <h1 class="text-2xl font-bold mb-6">Alert Notification Example</h1>
  
      <!-- Buttons to Trigger Alerts -->
      <div class="space-x-4 mb-8">
        <button
          class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          @click="showAlert('success', 'Success', 'Your operation was successful!')"
        >
          Show Success Alert
        </button>
        <button
          class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
          @click="showAlert('error', 'Error', 'Something went wrong. Please try again.')"
        >
          Show Error Alert
        </button>
        <button
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          @click="showAlert('info', 'Information', 'This is an informational alert.')"
        >
          Show Info Alert
        </button>
        <button
          class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
          @click="showAlert('warning', 'Warning', 'Please be cautious while proceeding.')"
        >
          Show Warning Alert
        </button>
      </div>
  
      <!-- Alert Notifications -->
      <div v-if="alert" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 z-50">
        <div
          class="flex items-center max-w-md p-4 rounded-lg shadow-lg text-white"
          :class="getAlertClass(alert.type)"
        >
          <!-- Icon -->
          <span class="material-icons mr-4">{{ getAlertIcon(alert.type) }}</span>
          <!-- Message -->
          <div class="flex-grow">
            <h4 class="font-bold">{{ alert.title }}</h4>
            <p class="text-sm">{{ alert.message }}</p>
          </div>
          <!-- Close Button -->
          <button
            class="ml-4 text-white hover:text-gray-300"
            @click="clearAlert"
          >
            ✕
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "AlertNotificationWithSample",
    data() {
      return {
        alert: null, // Current alert notification (if any)
      };
    },
    methods: {
      /**
       * Show an alert notification
       * @param {String} type - Type of the alert ('success', 'error', 'info', 'warning')
       * @param {String} title - Title of the alert
       * @param {String} message - Message of the alert
       */
      showAlert(type, title, message) {
        this.alert = { type, title, message };
  
        // Automatically hide the alert after 5 seconds
        setTimeout(() => {
          this.clearAlert();
        }, 5000);
      },
      /**
       * Clear the current alert
       */
      clearAlert() {
        this.alert = null;
      },
      /**
       * Get the CSS class for the alert based on its type
       * @param {String} type - Type of the alert
       * @returns {String} - CSS class
       */
      getAlertClass(type) {
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
       * Get the icon for the alert based on its type
       * @param {String} type - Type of the alert
       * @returns {String} - Material Icons name
       */
      getAlertIcon(type) {
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
  