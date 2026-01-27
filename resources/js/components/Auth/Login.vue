<script setup>
import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";
import Axios from "axios";
import { storeToRefs } from "pinia";
import { onMounted, ref } from "vue";

const appStore = useAppStore();
const AuthStore = useAuthStore();

const emailInput = ref("");
const passwordInput = ref("");
const forgotPasswordEmail = ref("");
const otp = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const phoneInput = ref("");
const loginType = ref("phone"); // 'phone' or 'email'
const showOTPVerification = ref(false);
const activeInput = ref(0);

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

    if (loginType.value === "phone") {
        formData.append("phone", phoneInput.value);
    } else {
        formData.append("email", emailInput.value);
    }
    formData.append("password", passwordInput.value);

    loggingIn.value = true;
    Axios.post("/login", formData, { withCredentials: true })
        .then((response) => {
            if (response.data.requires_verification) {
                // Email is not verified, show OTP verification for email verification
                appStore.setOtpEmail(response.data.email, true); // Set isEmailVerification to true
                loginMessage.value = response.data.message;
                showLoginForm.value = false;
            } else {
                // For phone login or verified email users, login directly
                appStore.setAuthToken(response.data.token);
                appStore.changeLoginStatus(true);
                showLoginForm.value = false;
            }
        })
        .catch((error) => {
            appStore.setAuthToken("");
            loginMessage.value = error.response.data.message;
            setTimeout(() => (loginMessage.value = ""), 2000);
        })
        .finally(() => (loggingIn.value = false));
}

function verifyOTP() {
    if (loggingIn.value) return;

    const otpCode = otp.value.join("");

    if (otpCode.length !== 6) {
        loginMessage.value = "Please enter a complete 6-digit OTP";
        setTimeout(() => (loginMessage.value = ""), 2000);
        return;
    }

    loggingIn.value = true;
    Axios.post("/api/verify-login-otp", {
        email: emailInput.value,
        otp: otpCode,
    })
        .then((response) => {
            appStore.setAuthToken(response.data.token);
            appStore.changeLoginStatus(true);
            showLoginForm.value = false;
            showOTPVerification.value = false;
        })
        .catch((error) => {
            loginMessage.value = error.response?.data?.message || "Invalid OTP";
            setTimeout(() => (loginMessage.value = ""), 2000);
        })
        .finally(() => (loggingIn.value = false));
}

function toggleLoginType() {
    loginType.value = loginType.value === "phone" ? "email" : "phone";
    loginMessage.value = "";
    showOTPVerification.value = false;
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

const focusNext = (index, event) => {
    if (event.target.value && index < 5) {
        activeInput.value = index + 1;
        document.getElementById(`otp-${index + 1}`).focus();
    }
};

const handlePaste = (e) => {
    e.preventDefault();
    const pasteData = e.clipboardData.getData("text/plain").trim();
    if (/^\d{6}$/.test(pasteData)) {
        otp.value = pasteData.split("").slice(0, 6);
        activeInput.value = 5;
        document.getElementById(`otp-5`).focus();
    }
};

const handleKeyDown = (index, e) => {
    if (e.key === "Backspace" && !otp.value[index] && index > 0) {
        activeInput.value = index - 1;
        document.getElementById(`otp-${index - 1}`).focus();
    } else if (e.key === "ArrowLeft" && index > 0) {
        activeInput.value = index - 1;
        document.getElementById(`otp-${index - 1}`).focus();
    } else if (e.key === "ArrowRight" && index < 5) {
        activeInput.value = index + 1;
        document.getElementById(`otp-${index + 1}`).focus();
    }
};

onMounted(() => {
    appStore.fetchFrontLanguages();
});

// No change needed
</script>

<template>
    <div
        @click="closeLoginForm()"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-3 sm:p-4 md:p-6 lg:p-8"
    >
        <!-- Login Form Section -->
        <div
            @click.stop
            class="relative bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-[90vw] sm:max-w-lg md:max-w-xl lg:max-w-2xl xl:max-w-md transform transition-all mx-3 sm:mx-4 md:mx-6 lg:mx-8 max-h-[90vh] overflow-y-auto"
        >
            <!-- Close Icon -->
            <button
                @click="closeLoginForm"
                class="absolute top-4 right-4 sm:top-5 sm:right-5 text-gray-400 hover:text-gray-600 transition-colors z-10 p-1"
            >
                <i class="fas fa-times text-lg sm:text-xl md:text-2xl"></i>
            </button>

            <div
                class="p-4 pt-12 sm:p-6 sm:pt-14 md:p-8 md:pt-16 lg:p-10 lg:pt-16"
            >
                <!-- Forgot Password Form -->
                <div v-if="showForgotPassword" class="space-y-4 sm:space-y-6">
                    <!-- Header -->
                    <div class="mb-6 sm:mb-8">
                        <h2
                            class="text-2xl sm:text-3xl font-black text-gray-900 mb-2"
                        >
                            {{
                                showOTPForm
                                    ? "Reset Password"
                                    : "Forgot Password?"
                            }}
                        </h2>
                        <p class="text-sm sm:text-base text-gray-600">
                            {{
                                showOTPForm
                                    ? "Enter the OTP and your new password"
                                    : "We'll send you an OTP to reset your password"
                            }}
                        </p>
                    </div>

                    <div
                        v-if="resetMessage"
                        class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-xl text-sm sm:text-base"
                        :class="{
                            'bg-green-50 text-green-700 border border-green-200':
                                resetMessage.includes('successfully') ||
                                resetMessage.includes('sent'),
                            'bg-red-50 text-red-700 border border-red-200': !(
                                resetMessage.includes('successfully') ||
                                resetMessage.includes('sent')
                            ),
                        }"
                    >
                        {{ resetMessage }}
                    </div>

                    <!-- OTP Form -->
                    <div v-if="showOTPForm" class="space-y-4 sm:space-y-6">
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                                >OTP Code</label
                            >
                            <input
                                type="text"
                                v-model="otp"
                                placeholder="Enter 6-digit OTP"
                                required
                                maxlength="6"
                                autocomplete="off"
                                class="w-full px-3 py-3 sm:px-4 sm:py-3.5 border-2 border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                                >New Password</label
                            >
                            <input
                                type="password"
                                v-model="newPassword"
                                placeholder="Enter new password"
                                required
                                minlength="8"
                                autocomplete="off"
                                class="w-full px-3 py-3 sm:px-4 sm:py-3.5 border-2 border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                                >Confirm Password</label
                            >
                            <input
                                type="password"
                                v-model="confirmPassword"
                                placeholder="Confirm new password"
                                required
                                minlength="8"
                                autocomplete="off"
                                class="w-full px-3 py-3 sm:px-4 sm:py-3.5 border-2 border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base"
                            />
                        </div>

                        <button
                            @click="resetPassword"
                            :disabled="resetLoading"
                            class="w-full py-3 sm:py-4 bg-gradient-to-r from-lime-500 to-lime-600 text-white rounded-lg sm:rounded-xl font-bold hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-not-allowed text-sm sm:text-base"
                        >
                            {{
                                resetLoading ? "Resetting..." : "Reset Password"
                            }}
                        </button>

                        <div class="text-center">
                            <button
                                @click="showOTPForm = false"
                                class="text-lime-600 font-semibold hover:text-lime-700 transition-colors text-sm sm:text-base"
                            >
                                ← Back to Email
                            </button>
                        </div>
                    </div>

                    <!-- Email Form -->
                    <div v-else class="space-y-4 sm:space-y-6">
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                                >Email Address</label
                            >
                            <input
                                type="email"
                                v-model="forgotPasswordEmail"
                                placeholder="Enter your email"
                                required
                                class="w-full px-3 py-3 sm:px-4 sm:py-3.5 border-2 border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base"
                            />
                        </div>

                        <button
                            @click="sendOTP"
                            :disabled="resetLoading"
                            class="w-full py-3 sm:py-4 bg-gradient-to-r from-lime-500 to-lime-600 text-white rounded-lg sm:rounded-xl font-bold hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-70 text-sm sm:text-base"
                        >
                            <span
                                class="flex items-center justify-center gap-2"
                                v-if="resetLoading"
                            >
                                <svg
                                    class="animate-spin h-4 w-4 sm:h-5 sm:w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Sending...
                            </span>
                            <span v-else>Send OTP</span>
                        </button>

                        <div class="text-center">
                            <button
                                @click="toggleForgotPassword"
                                class="text-lime-600 font-semibold hover:text-lime-700 transition-colors text-sm sm:text-base"
                            >
                                ← Back to Login
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Login Form -->
                <div
                    v-else-if="!showOTPVerification"
                    class="space-y-4 sm:space-y-5"
                >
                    <!-- Header -->
                    <div class="mb-6 sm:mb-8">
                        <h2
                            class="text-2xl sm:text-3xl font-black text-gray-900 mb-2"
                        >
                            Welcome Back
                        </h2>
                        <p class="text-sm sm:text-base text-gray-600">
                            Sign in to continue your learning journey
                        </p>
                    </div>

                    <div
                        v-if="loginMessage"
                        class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg sm:rounded-xl text-sm sm:text-base"
                    >
                        {{ loginMessage }}
                    </div>

                    <!-- Toggle between phone and email -->
                    <div class="flex justify-center mb-4 sm:mb-6">
                        <div
                            class="inline-flex bg-gray-100 p-1 rounded-lg sm:rounded-xl"
                        >
                            <button
                                @click="loginType = 'phone'"
                                :class="[
                                    'px-4 py-2 sm:px-6 sm:py-2.5 rounded-md sm:rounded-lg text-xs sm:text-sm font-semibold transition-all duration-200',
                                    loginType === 'phone'
                                        ? 'bg-white text-lime-600 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-800',
                                ]"
                            >
                                <i class="fas fa-phone mr-1 sm:mr-2"></i>Phone
                            </button>
                            <button
                                @click="loginType = 'email'"
                                :class="[
                                    'px-4 py-2 sm:px-6 sm:py-2.5 rounded-md sm:rounded-lg text-xs sm:text-sm font-semibold transition-all duration-200',
                                    loginType === 'email'
                                        ? 'bg-white text-lime-600 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-800',
                                ]"
                            >
                                <i class="fas fa-envelope mr-1 sm:mr-2"></i
                                >Email
                            </button>
                        </div>
                    </div>

                    <!-- Input Fields -->
                    <div class="space-y-4 sm:space-y-5">
                        <!-- Phone Input -->
                        <div v-if="loginType === 'phone'">
                            <label
                                for="phoneInput"
                                class="block text-sm font-semibold text-gray-700 mb-2"
                            >
                                Phone Number
                            </label>
                            <input
                                type="tel"
                                id="phoneInput"
                                v-model="phoneInput"
                                placeholder="+251911234567"
                                required
                                class="w-full px-3 py-3 sm:px-4 sm:py-3.5 border-2 border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base"
                            />
                        </div>

                        <!-- Email Input -->
                        <div v-if="loginType === 'email'">
                            <label
                                for="emailInput"
                                class="block text-sm font-semibold text-gray-700 mb-2"
                            >
                                Email Address
                            </label>
                            <input
                                type="email"
                                id="emailInput"
                                v-model="emailInput"
                                placeholder="Enter your email"
                                required
                                class="w-full px-3 py-3 sm:px-4 sm:py-3.5 border-2 border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base"
                            />
                        </div>

                        <div>
                            <label
                                for="passwordInput"
                                class="block text-sm font-semibold text-gray-700 mb-2"
                            >
                                Password
                            </label>
                            <div class="relative">
                                <input
                                    :type="
                                        showpasswordInput ? 'text' : 'password'
                                    "
                                    id="passwordInput"
                                    v-model="passwordInput"
                                    placeholder="Enter your password"
                                    required
                                    class="w-full px-3 py-3 pr-10 sm:px-4 sm:py-3.5 sm:pr-12 border-2 border-gray-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all text-sm sm:text-base"
                                />
                                <button
                                    type="button"
                                    @click="toggleShowPassword"
                                    class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    <i
                                        :class="
                                            showpasswordInput
                                                ? 'fas fa-eye-slash'
                                                : 'fas fa-eye'
                                        "
                                    ></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                @click="toggleForgotPassword"
                                class="text-xs sm:text-sm text-lime-600 font-semibold hover:text-lime-700 transition-colors"
                            >
                                Forgot Password?
                            </button>
                        </div>

                        <button
                            @click="tryLogin"
                            :disabled="loggingIn"
                            class="w-full py-3 sm:py-4 bg-gradient-to-r from-lime-500 to-lime-600 text-white rounded-lg sm:rounded-xl font-bold hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-70 text-sm sm:text-base"
                        >
                            <span
                                class="flex items-center justify-center gap-2"
                                v-if="loggingIn"
                            >
                                <svg
                                    class="animate-spin h-4 w-4 sm:h-5 sm:w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Signing In...
                            </span>
                            <span v-else>Sign In</span>
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="mt-6 sm:mt-8 text-center">
                        <p class="text-sm sm:text-base text-gray-600">
                            Don't have an account?
                            <button
                                @click="roteToLogin()"
                                class="text-lime-600 font-bold hover:text-lime-700 transition-colors ml-1"
                            >
                                Sign Up
                            </button>
                        </p>
                    </div>
                </div>

                <!-- OTP Verification Form for Email Login -->
                <div
                    v-else-if="showOTPVerification"
                    class="space-y-4 sm:space-y-6"
                >
                    <!-- Header -->
                    <div class="mb-6 sm:mb-8">
                        <h2
                            class="text-2xl sm:text-3xl font-black text-gray-900 mb-2"
                        >
                            Verify Your Email
                        </h2>
                        <p class="text-sm sm:text-base text-gray-600">
                            We've sent a 6-digit code to your email
                        </p>
                    </div>

                    <div
                        v-if="loginMessage"
                        class="mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg sm:rounded-xl text-sm sm:text-base"
                    >
                        {{ loginMessage }}
                    </div>

                    <div class="space-y-4 sm:space-y-6">
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-3"
                                >Enter OTP Code</label
                            >
                            <!-- OTP Input Fields -->
                            <div class="flex justify-between gap-1 sm:gap-2">
                                <input
                                    v-for="(digit, index) in otp"
                                    :id="`otp-${index}`"
                                    :key="index"
                                    v-model="otp[index]"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    :class="[
                                        'w-full h-12 sm:h-14 text-center text-lg sm:text-2xl font-bold border-2 rounded-lg sm:rounded-xl focus:outline-none transition-all duration-200',
                                        'border-gray-200 focus:border-lime-500 focus:ring-2 focus:ring-lime-200',
                                        activeInput === index
                                            ? 'ring-2 ring-lime-300 border-lime-500 bg-lime-50'
                                            : 'bg-white',
                                    ]"
                                    @input="focusNext(index, $event)"
                                    @keydown="handleKeyDown(index, $event)"
                                    @focus="activeInput = index"
                                    @paste="handlePaste"
                                />
                            </div>
                        </div>

                        <button
                            @click="verifyOTP"
                            :disabled="loggingIn"
                            class="w-full py-3 sm:py-4 bg-gradient-to-r from-lime-500 to-lime-600 text-white rounded-lg sm:rounded-xl font-bold hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-70 text-sm sm:text-base"
                        >
                            <span
                                class="flex items-center justify-center gap-2"
                                v-if="loggingIn"
                            >
                                <svg
                                    class="animate-spin h-4 w-4 sm:h-5 sm:w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Verifying...
                            </span>
                            <span v-else>Verify & Sign In</span>
                        </button>

                        <div class="text-center">
                            <button
                                @click="
                                    showOTPVerification = false;
                                    loginMessage = '';
                                    otp = ['', '', '', '', '', ''];
                                    activeInput = 0;
                                "
                                class="text-lime-600 font-semibold hover:text-lime-700 transition-colors text-sm sm:text-base"
                            >
                                ← Back to Login
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Add smooth entrance animation */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.relative.bg-white {
    animation: slideUp 0.3s ease-out;
}

/* Thin scrollbar styles */
.max-h-\[90vh\]::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

.max-h-\[90vh\]::-webkit-scrollbar-track {
    background: transparent;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb {
    background: transparent;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb:hover {
    background: transparent;
}

/* For Firefox - hide scrollbar */
.max-h-\[90vh\] {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
</style>
