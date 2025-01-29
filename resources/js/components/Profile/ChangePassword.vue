                
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
        <div>
                <h2 class="text-xl font-bold mb-5">{{frontLang.lang.changePassword}}</h2>
            <form @submit.prevent="changePassword">
            <div v-if="errorMessage" class="text-red-500 mb-5">
                {{ errorMessage }}
            </div>
            <div v-if="successMessage" class="text-green-500 mb-5">
                {{ successMessage }}
            </div>
            <div class="mb-5">
                <label for="current-password" class="font-bold">{{frontLang.lang.currentPassword}} *</label>
                <input
                v-model="passwords.oldPassword"
                type="password"
                id="current-password"
                placeholder="Enter current password"
                required
                class="border border-gray-300 rounded-lg px-4 py-2 w-full"
                />
            </div>
            <div class="flex gap-5 mb-5">
                <div class="flex-1">
                <label for="new-password" class="font-bold">{{frontLang.lang.newPassword}} *</label>
                <input
                    v-model="passwords.newPassword"
                    type="password"
                    id="new-password"
                    placeholder="Enter new password"
                    required
                    class="border border-gray-300 rounded-lg px-4 py-2 w-full"
                />
                </div>
                <div class="flex-1">
                <label for="confirm-password" class="font-bold">{{frontLang.lang.confirmPassword}} *</label>
                <input
                    v-model="passwords.confirmPassword"
                    type="password"
                    id="confirm-password"
                    placeholder="Re-enter new password"
                    required
                    class="border border-gray-300 rounded-lg px-4 py-2 w-full"
                />
                </div>
            </div>
            <button type="submit" class="bg-blue-500 text-white rounded-lg px-4 py-2">
                {{frontLang.lang.updatePassword}}
            </button>
            </form>
        </div>
        </template>

<style scoped>
/* Add scoped styles for change password */
</style>
