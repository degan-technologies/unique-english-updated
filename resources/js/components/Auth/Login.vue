<script setup>
import Axios from "axios";
import { onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";

const appStore = useAppStore();
const AuthStore = useAuthStore();

const emailInput = ref("");
const passwordInput = ref("");
const forgotPasswordEmail = ref("");
const otp = ref("");
const newPassword = ref("");
const confirmPassword = ref("");

const showpasswordInput = ref(false);
const loggingIn = ref(false);
const loginMessage = ref("");
const showForgotPassword = ref(false);
const showOTPForm = ref(false);
const resetMessage = ref("");
const resetLoading = ref(false);

const { lang, frontLang } = storeToRefs(appStore);
const { showLoginForm, showRegistrationForm } = storeToRefs(AuthStore);

const toggleShowPassword = () => {
    showpasswordInput.value = !showpasswordInput.value;
};

lang.value = "en";

function tryLogin() {
    if (loggingIn.value) return;

    let formData = new FormData();
    formData.append("email", emailInput.value);
    formData.append("password", passwordInput.value);

    loggingIn.value = true;
    Axios.post("/login", formData, { withCredentials: true })
        .then((response) => {
            appStore.setAuthToken(response.data.token);
            appStore.changeLoginStatus(true);
            showLoginForm.value = false;
        })
        .catch((error) => {
            appStore.setAuthToken("");
            loginMessage.value = error.response.data.message;

            setTimeout(() => (loginMessage.value = ""), 2000);
        })
        .finally(() => (loggingIn.value = false));
}


function sendOTP() {
    if (resetLoading.value) return;
    if (!forgotPasswordEmail.value) {
        resetMessage.value = "Please enter your email address";
        setTimeout(() => (resetMessage.value = ""), 2000);
        return;
    }

    resetLoading.value = true;
    Axios.post("/forgot-password", { email: forgotPasswordEmail.value })
        .then((response) => {
            resetMessage.value = "OTP has been sent to your email";
            showOTPForm.value = true;
        })
        .catch((error) => {
            resetMessage.value =
                error.response?.data?.message || "Failed to send OTP";
        })
        .finally(() => (resetLoading.value = false));
}

function resetPassword() {
    if (resetLoading.value) return;
    if (newPassword.value !== confirmPassword.value) {
        resetMessage.value = "Passwords do not match";
        setTimeout(() => (resetMessage.value = ""), 2000);
        return;
    }

    resetLoading.value = true;
    Axios.post("/reset-password", {
        email: forgotPasswordEmail.value,
        otp: otp.value,
        password: newPassword.value,
        password_confirmation: confirmPassword.value,
    })
        .then((response) => {
            resetMessage.value = "Password reset successfully!";
            setTimeout(() => {
                showForgotPassword.value = false;
                showOTPForm.value = false;
                resetMessage.value = "";
            }, 2000);
        })
        .catch((error) => {
            resetMessage.value =
                error.response?.data?.message || "Failed to reset password";
        })
        .finally(() => (resetLoading.value = false));
}


function closeLoginForm() {
    showLoginForm.value = false;
}

function roteToLogin() {
    showLoginForm.value = false;
    showRegistrationForm.value = true;
}

function toggleForgotPassword() {
    showForgotPassword.value = !showForgotPassword.value;
    forgotPasswordEmail.value = emailInput.value;
    resetMessage.value = "";
}

onMounted(() => {
    appStore.fetchFrontLanguages();
});

const socialLogin = (provider) => {
    window.location.href = `http://127.0.0.1:8000/auth/${provider}/redirect`;
    appStore.changeLoginStatus(true);
};
</script>

<template>
    <div @click="closeLoginForm()"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 p-4">
        <!-- Login Form Section -->
        <div @click.stop class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md my-25">
            <!-- Close Icon -->
            <button @click="closeLoginForm" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>

            <div class="p-8">
                <!-- Forgot Password Form -->
                <div v-if="showForgotPassword">
                    <p class="text-lime-600 text-left font-bold mb-6 text-lg">
                        {{
                            showOTPForm
                                ? "Reset Your Password"
                                : "Forgot Your Password?"
                        }}
                    </p>

                    <div v-if="resetMessage" class="mb-4" :class="{
                        'text-green-600':
                            resetMessage.includes('successfully') ||
                            resetMessage.includes('sent'),
                        'text-rose-500': !(
                            resetMessage.includes('successfully') ||
                            resetMessage.includes('sent')
                        ),
                    }">
                        {{ resetMessage }}
                    </div>

                    <!-- OTP Form -->
                    <div v-if="showOTPForm" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">OTP</label>
                            <input type="text" v-model="otp" placeholder="Enter 6-digit OTP" required maxlength="6" autocomplete="off"
                                class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Password</label>
                            <input type="password" v-model="newPassword" placeholder="Enter new password" required
                                minlength="8" autocomplete="off"
                                class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" v-model="confirmPassword" placeholder="Confirm new password" required
                                minlength="8" autocomplete="off"
                                class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700" />
                        </div>

                        <button @click="resetPassword" :disabled="resetLoading"
                            class="w-full py-3 bg-lime-700 text-white rounded-lg font-semibold hover:bg-lime-800 active:scale-95 transition-transform">
                            {{
                                resetLoading ? "Resetting..." : "Reset Password"
                            }}
                        </button>

                        <div class="text-center">
                            <span @click="showOTPForm = false"
                                class="text-lime-700 font-semibold hover:underline cursor-pointer">Back to Email</span>
                        </div>
                    </div>

                    <!-- Email Form -->
                    <div v-else class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" v-model="forgotPasswordEmail" placeholder="Enter your email" required
                                class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700" />
                        </div>
                        
                        <button @click="sendOTP" :disabled="resetLoading"
                            class="w-full py-3 bg-lime-700 text-white rounded-lg font-semibold hover:bg-lime-800 active:scale-95 transition-transform">
                            <span class="flex flex-1 gap-2 items-center justify-center" v-if="resetLoading">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Sending ...
                            </span>
                            <span v-else>
                                Send OTP
                            </span>
                        </button>

                        <div class="text-center">
                            <span @click="toggleForgotPassword"
                                class="text-lime-700 font-semibold hover:underline cursor-pointer">Back to Login</span>
                        </div>
                    </div>
                </div>

                <!-- login form -->
                <div v-else>
                    <p class="text-lime-600 text-left font-bold mb-6 text-lg">
                        {{ frontLang.lang?.login_to_your_account }}
                    </p>
                    <div v-if="loginMessage" class="text-rose-500 text-center mb-4">
                        {{ loginMessage }}
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label for="emailInput" class="block text-sm font-medium text-gray-700">{{ frontLang.lang?.email }}</label>
                            <input type="email" id="emailInput" v-model="emailInput" placeholder="Enter your email"
                                required
                                class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700" />
                        </div>

                        <div>
                            <label for="passwordInput" class="block text-sm font-medium text-gray-700">{{ frontLang.lang?.password }}</label>
                            <div class="relative">
                                <input :type="showpasswordInput ? 'text' : 'password'" id="passwordInput"
                                    v-model="passwordInput" placeholder="Enter your password" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700" />
                                <button type="button" @click="toggleShowPassword"
                                    class="absolute p-3 px-0 right-3 top-3 text-gray-500">
                                    <span v-if="showpasswordInput"><i class="fas fa-eye-slash"></i></span>
                                    <span v-else><i class="fas fa-eye"></i></span>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <span @click="toggleForgotPassword"
                                class="text-lime-700 text-sm font-semibold hover:underline cursor-pointer">{{ frontLang.lang?.forgot_password }}</span>
                        </div>

                        <button @click="tryLogin" :disabled="loggingIn"
                            class="w-full py-3 bg-lime-700 text-white rounded-lg font-semibold hover:bg-lime-800 active:scale-95 transition-transform">
                            <span class="flex flex-1 gap-2 items-center justify-center" v-if="loggingIn">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                {{ frontLang.lang?.login_log }}
                            </span>
                            <span v-else>
                                {{ frontLang.lang?.login }}
                            </span>
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="mt-5 text-center">
                        <span @click="roteToLogin()"
                            class="text-lime-700 font-semibold hover:underline cursor-pointer">{{frontLang.lang?.d_have_account }}
                            {{ frontLang.lang?.register }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.social-btn {
    background: rgba(255, 255, 255, 0.4);
    padding: 12px;
    transition: transform 0.3s;
    color: #4b5563;
}
</style>
