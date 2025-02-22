                
        <script>
        import { ref } from "vue";
        import { storeToRefs } from "pinia";
        import axios from "axios";
        import { useAppStore } from "@/store/useAppStore";

        export default {
        name: "ChangePassword",
        setup() {
            // Access the Pinia store
            const appStore = useAppStore();
            const { frontLang } = storeToRefs(appStore); // Make frontLang reactive

            // Reactive data
            const passwords = ref({
            oldPassword: "",
            newPassword: "",
            confirmPassword: "",
            });
            const errorMessage = ref("");
            const successMessage = ref("");

            // Methods
            const clearMessagesAfterDelay = () => {
            setTimeout(() => {
                errorMessage.value = "";
                successMessage.value = "";
            }, 2000); // Clears messages after 2 seconds
            };

            const changePassword = async () => {
            errorMessage.value = "";
            successMessage.value = "";

            if (passwords.value.newPassword !== passwords.value.confirmPassword) {
                errorMessage.value = "Passwords do not match!";
                clearMessagesAfterDelay();
                return;
            }

            try {
                const response = await axios.post("/api/password-reset", {
                old_password: passwords.value.oldPassword,
                new_password: passwords.value.newPassword,
                new_password_confirmation: passwords.value.confirmPassword,
                });

                successMessage.value = response.data.message;
                passwords.value = {
                oldPassword: "",
                newPassword: "",
                confirmPassword: "",
                };
                clearMessagesAfterDelay();
            } catch (error) {
                if (error.response && error.response.data) {
                errorMessage.value = error.response.data.message;
                } else {
                errorMessage.value = "An unexpected error occurred.";
                }
                clearMessagesAfterDelay();
            }
            };

            return {
            frontLang,
            passwords,
            errorMessage,
            successMessage,
            changePassword,
            };
        },
        };
        </script>

<template>
    <div class=" max-w-3xl h-auto flex items-center justify-center p-4">
      <div class=" bg-white rounded-lg p-6 space-y-6">
        <!-- Header -->
        <h2 class="text-xl font-bold text-center text-lime-700">
          {{ frontLang.lang.changePassword }}
        </h2>
  
        <!-- Alert Messages -->
        <div v-if="errorMessage" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg">
          {{ errorMessage }}
        </div>
        <div v-if="successMessage" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg">
          {{ successMessage }}
        </div>
  
        <!-- Change Password Form -->
        <form @submit.prevent="changePassword" class="space-y-6">
          <div>
            <label for="current-password" class="block font-semibold text-gray-700 mb-1">
              {{ frontLang.lang.currentPassword }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="passwords.oldPassword"
              type="password"
              id="current-password"
              placeholder="Enter current password"
              required
              class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
            />
          </div>
  
          <div class="flex gap-4">
            <div class="flex-1">
              <label for="new-password" class="block font-semibold text-gray-700 mb-1">
                {{ frontLang.lang.newPassword }} <span class="text-red-500">*</span>
              </label>
              <input
                v-model="passwords.newPassword"
                type="password"
                id="new-password"
                placeholder="Enter new password"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
              />
            </div>
            <div class="flex-1">
              <label for="confirm-password" class="block font-semibold text-gray-700 mb-1">
                {{ frontLang.lang.confirmPassword }} <span class="text-red-500">*</span>
              </label>
              <input
                v-model="passwords.confirmPassword"
                type="password"
                id="confirm-password"
                placeholder="Re-enter new password"
                required
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
              />
            </div>
          </div>
  
          <button
            type="submit"
            class="w-full bg-lime-700 text-white py-3 rounded-lg hover:bg-lime-800 transition flex justify-center"
          >
            {{ frontLang.lang.updatePassword }}
          </button>
        </form>
      </div>
    </div>
  </template>

<style scoped>
/* Add scoped styles for change password */
</style>
