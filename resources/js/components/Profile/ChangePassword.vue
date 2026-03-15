<script setup>
import { useAppStore } from "@/store/useAppStore";
import Axios from "axios";
import { storeToRefs } from "pinia";
import { ref } from "vue";

const appStore = useAppStore();
const { frontLang } = storeToRefs(appStore);

const passwords = ref({
    oldPassword: "",
    newPassword: "",
    confirmPassword: "",
});

const errors = ref({
    oldPassword: "",
    newPassword: "",
    confirmPassword: "",
    general: "",
});

const isLoading = ref(false);
const showPassword = ref({
    old: false,
    new: false,
    confirm: false,
});

const strongPasswordPattern =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;

const validateForm = () => {
    let isValid = true;
    errors.value = {
        oldPassword: "",
        newPassword: "",
        confirmPassword: "",
        general: "",
    };

    if (!passwords.value.oldPassword) {
        errors.value.oldPassword = "Current password is required";
        isValid = false;
    }

    if (!passwords.value.newPassword) {
        errors.value.newPassword = "New password is required";
        isValid = false;
    } else if (!strongPasswordPattern.test(passwords.value.newPassword)) {
        errors.value.newPassword =
            "Password must be at least 8 characters and include uppercase, lowercase, number, and special character";
        isValid = false;
    }

    if (!passwords.value.confirmPassword) {
        errors.value.confirmPassword = "Please confirm your new password";
        isValid = false;
    } else if (
        passwords.value.newPassword !== passwords.value.confirmPassword
    ) {
        errors.value.confirmPassword = "Passwords do not match";
        isValid = false;
    }

    return isValid;
};

const changePassword = async (e) => {
    e.preventDefault();

    if (!validateForm()) return;

    isLoading.value = true;
    errors.value.general = "";

    try {
        const response = await Axios.post("/api/password-reset", {
            old_password: passwords.value.oldPassword,
            new_password: passwords.value.newPassword,
            new_password_confirmation: passwords.value.confirmPassword,
        });

        // Success case
        passwords.value = {
            oldPassword: "",
            newPassword: "",
            confirmPassword: "",
        };

        errors.value.general =
            response.data.message || "Password changed successfully";
        setTimeout(() => (errors.value.general = ""), 3000);
    } catch (error) {
        if (error.response?.data?.errors) {
            // Handle field-specific errors
            for (const [field, message] of Object.entries(
                error.response.data.errors,
            )) {
                errors.value[field] = message[0];
            }
        } else {
            errors.value.general =
                error.response?.data?.message ||
                "An error occurred while changing password";
        }
    } finally {
        isLoading.value = false;
    }
};

const togglePasswordVisibility = (field) => {
    showPassword.value[field] = !showPassword.value[field];
};
</script>

<template>
    <div class="w-full bg-white overflow-hidden space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800">
                {{ frontLang?.lang?.changePassword || "Change Password" }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{
                    frontLang?.lang?.passwordChangeDesc ||
                    "Secure your account with a new password"
                }}
            </p>
        </div>

        <!-- Error/Success Messages -->
        <div
            v-if="errors.general"
            :class="{
                'bg-green-50 text-green-800': !errors.general.includes('error'),
                'bg-red-50 text-red-800': errors.general.includes('error'),
            }"
            class="p-3 rounded-lg text-sm"
        >
            {{ errors.general }}
        </div>

        <form @submit.prevent="changePassword" class="space-y-5">
            <!-- Current Password -->
            <div>
                <label
                    for="current-password"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    {{ frontLang?.lang?.currentPassword || "Current Password" }}
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        v-model="passwords.oldPassword"
                        :type="showPassword.old ? 'text' : 'password'"
                        id="current-password"
                        autocomplete="current-password"
                        placeholder="Enter current password"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                        :class="{
                            'border-gray-300': !errors.oldPassword,
                            'border-red-500': errors.oldPassword,
                        }"
                    />
                    <button
                        type="button"
                        @click="togglePasswordVisibility('old')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                    >
                        <i
                            :class="
                                showPassword.old
                                    ? 'fas fa-eye-slash'
                                    : 'fas fa-eye'
                            "
                        ></i>
                    </button>
                </div>
                <p v-if="errors.oldPassword" class="mt-1 text-sm text-red-600">
                    {{ errors.oldPassword }}
                </p>
            </div>

            <!-- New Password -->
            <div>
                <label
                    for="new-password"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    {{ frontLang?.lang?.newPassword || "New Password" }}
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        v-model="passwords.newPassword"
                        :type="showPassword.new ? 'text' : 'password'"
                        id="new-password"
                        autocomplete="new-password"
                        placeholder="Enter new password"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                        :class="{
                            'border-gray-300': !errors.newPassword,
                            'border-red-500': errors.newPassword,
                        }"
                    />
                    <button
                        type="button"
                        @click="togglePasswordVisibility('new')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                    >
                        <i
                            :class="
                                showPassword.new
                                    ? 'fas fa-eye-slash'
                                    : 'fas fa-eye'
                            "
                        ></i>
                    </button>
                </div>
                <p v-if="errors.newPassword" class="mt-1 text-sm text-red-600">
                    {{ errors.newPassword }}
                </p>
                <p v-else class="mt-1 text-xs text-gray-500">
                    Use at least 8 characters with uppercase, lowercase, number,
                    and special character
                </p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label
                    for="confirm-password"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    {{ frontLang?.lang?.confirmPassword || "Confirm Password" }}
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        v-model="passwords.confirmPassword"
                        :type="showPassword.confirm ? 'text' : 'password'"
                        id="confirm-password"
                        autocomplete="new-password"
                        placeholder="Confirm new password"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                        :class="{
                            'border-gray-300': !errors.confirmPassword,
                            'border-red-500': errors.confirmPassword,
                        }"
                    />
                    <button
                        type="button"
                        @click="togglePasswordVisibility('confirm')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                    >
                        <i
                            :class="
                                showPassword.confirm
                                    ? 'fas fa-eye-slash'
                                    : 'fas fa-eye'
                            "
                        ></i>
                    </button>
                </div>
                <p
                    v-if="errors.confirmPassword"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ errors.confirmPassword }}
                </p>
            </div>

            <!-- Submit Button -->
            <div>
                <button
                    type="submit"
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transition-colors"
                    :disabled="isLoading"
                >
                    <span v-if="isLoading" class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        {{ frontLang?.lang?.updating || "Updating..." }}
                    </span>
                    <span v-else>
                        {{
                            frontLang?.lang?.updatePassword || "Update Password"
                        }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Password toggle button styling */
button[type="button"] {
    background: none;
    border: none;
    cursor: pointer;
}

/* Loading spinner animation */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.fa-spin {
    animation: spin 1s linear infinite;
}

/* Smooth transitions for hover effects */
input,
button {
    transition: all 0.2s ease;
}

/* Better focus styles */
input:focus {
    outline: none;
    box-shadow: 0 0 0 1px rgba(240, 124, 42, 0.8);
}
</style>
