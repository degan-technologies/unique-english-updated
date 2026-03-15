<script setup>
import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";
import Axios from "axios";
import { storeToRefs } from "pinia";
import { ref } from "vue";
import { useRouter } from "vue-router";

const appStore = useAppStore();
const AuthStore = useAuthStore();
const router = useRouter();

const { showLoginForm, showRegistrationForm } = storeToRefs(AuthStore);
const { frontLang, otpEmail } = storeToRefs(appStore);

const name = ref("");
const email = ref("");
const password = ref("");
const showPassword = ref(false);
const loading = ref(false);
const agreeTerms = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

const strongPasswordPattern =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

// Handle registration
async function handleRegister() {
    if (!agreeTerms.value) {
        errorMessage.value = "Please agree to the terms and conditions";
        setTimeout(() => (errorMessage.value = ""), 3000);
        return;
    }

    if (!email.value) {
        errorMessage.value = "Please enter a valid email address";
        setTimeout(() => (errorMessage.value = ""), 3000);
        return;
    }

    if (!name.value || !password.value) {
        errorMessage.value = "Please fill in all required fields";
        setTimeout(() => (errorMessage.value = ""), 3000);
        return;
    }

    if (!strongPasswordPattern.test(password.value)) {
        errorMessage.value =
            "Password must be at least 8 characters and include uppercase, lowercase, number, and special character";
        setTimeout(() => (errorMessage.value = ""), 3000);
        return;
    }

    loading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    try {
        const data = {
            full_name: name.value,
            password: password.value,
            email: email.value,
        };

        const response = await Axios.post("/api/register", data);

        // Set otp email globally for VerifyOtp component
        appStore.setOtpEmail(email.value);
        // Close registration form to show VerifyOtp component
        showRegistrationForm.value = false;
        successMessage.value =
            "Please check your email for the OTP verification code.";
    } catch (err) {
        errorMessage.value =
            err.response?.data?.message ||
            "Registration failed. Please try again.";
        setTimeout(() => (errorMessage.value = ""), 3000);
    } finally {
        loading.value = false;
    }
}

function closeRegistrationForm() {
    showRegistrationForm.value = false;
}

function routeToLogin() {
    showRegistrationForm.value = false;
    showLoginForm.value = true;
}
</script>

<template>
    <div
        @click="closeRegistrationForm"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-3 sm:p-4 md:p-6 lg:p-8 z-50 backdrop-blur-sm"
    >
        <div
            @click.stop
            class="bg-white rounded-2xl sm:rounded-3xl shadow-xl w-full max-w-[90vw] sm:max-w-lg md:max-w-xl lg:max-w-2xl xl:max-w-md transform transition-all duration-300 animate-fade-in overflow-hidden mx-3 sm:mx-4 md:mx-6 lg:mx-8 max-h-[95vh] overflow-y-auto relative"
        >
            <!-- Close Icon -->
            <button
                @click="closeRegistrationForm"
                class="absolute top-4 right-4 sm:top-5 sm:right-5 text-gray-400 hover:text-gray-600 transition-colors z-10 p-1"
            >
                <i class="fas fa-times text-lg sm:text-xl md:text-2xl"></i>
            </button>

            <!-- Header with gradient background -->
            <div class="p-4 sm:p-6 text-lime-500 pt-12 sm:pt-14">
                <h2 class="text-xl sm:text-2xl font-bold">
                    {{
                        frontLang?.lang?.startjourneywithus ||
                        "Start your journey with us"
                    }}
                </h2>
            </div>

            <!-- Form content -->
            <div class="p-4 sm:p-6 space-y-3 sm:space-y-4">
                <form
                    @submit.prevent="handleRegister"
                    class="space-y-3 sm:space-y-4"
                >
                    <!-- Name Field -->
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2"
                        >
                            {{ frontLang?.lang?.name || "Full Name" }}
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="name"
                                v-model="name"
                                placeholder="John Doe"
                                required
                                class="w-full p-2.5 sm:p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-lime-400 transition-all duration-200 text-sm sm:text-base pr-8 sm:pr-10"
                            />
                            <div
                                class="absolute inset-y-0 right-2 sm:right-3 flex items-center pointer-events-none"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 008 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2"
                        >
                            {{ frontLang?.lang?.email || "Email Address" }}
                        </label>
                        <div class="relative">
                            <input
                                type="email"
                                id="email"
                                v-model="email"
                                placeholder="john@example.com"
                                required
                                class="w-full p-2.5 sm:p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-lime-400 transition-all duration-200 text-sm sm:text-base pr-8 sm:pr-10"
                            />
                            <div
                                class="absolute inset-y-0 right-2 sm:right-3 flex items-center pointer-events-none"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700 mb-1 sm:mb-2"
                        >
                            {{ frontLang?.lang?.password || "Password" }}
                        </label>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                v-model="password"
                                placeholder="••••••••"
                                required
                                class="w-full p-2.5 sm:p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-lime-400 transition-all duration-200 text-sm sm:text-base pr-8 sm:pr-10"
                            />
                            <button
                                type="button"
                                @click="togglePassword"
                                class="absolute p-2 right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                            >
                                <span v-if="showPassword"
                                    ><i class="fas fa-eye-slash text-sm"></i
                                ></span>
                                <span v-else
                                    ><i class="fas fa-eye text-sm"></i
                                ></span>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Use at least 8 characters with uppercase, lowercase,
                            number, and special character.
                        </p>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5 mt-0.5">
                            <input
                                id="terms"
                                type="checkbox"
                                v-model="agreeTerms"
                                class="focus:ring-lime-400 h-3 w-3 sm:h-4 sm:w-4 text-lime-500 border-gray-300 rounded"
                            />
                        </div>
                        <div class="ml-2 sm:ml-3 text-xs sm:text-sm">
                            <label
                                for="terms"
                                class="font-medium text-gray-700"
                            >
                                {{
                                    frontLang?.lang?.agreeto || "I agree to the"
                                }}
                                <a
                                    href="#"
                                    class="text-lime-500 hover:text-lime-600 hover:underline"
                                >
                                    {{
                                        frontLang?.lang?.termsAndConditions ||
                                        "Terms and Conditions"
                                    }}
                                </a>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full flex justify-center items-center gap-2 bg-lime-500 hover:bg-lime-600 text-white font-medium py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-lime-400 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed shadow-md hover:shadow-lg text-sm sm:text-base"
                    >
                        <span v-if="loading" class="flex items-center">
                            <svg
                                class="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white"
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
                            <span class="ml-2">
                                {{
                                    frontLang?.lang?.CreatingAccount ||
                                    "Creating Account..."
                                }}
                            </span>
                        </span>
                        <span v-else>
                            {{ frontLang?.lang?.signup || "Sign Up" }}
                        </span>
                    </button>
                </form>

                <!-- Feedback Messages -->
                <transition name="fade">
                    <div
                        v-if="successMessage"
                        class="p-2.5 sm:p-3 bg-green-50 text-green-700 text-xs sm:text-sm rounded-lg flex items-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 sm:h-5 sm:w-5 mr-2"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        {{ successMessage }}
                    </div>
                </transition>

                <transition name="fade">
                    <div
                        v-if="errorMessage"
                        class="p-2.5 sm:p-3 bg-red-50 text-red-700 text-xs sm:text-sm rounded-lg flex items-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 sm:h-5 sm:w-5 mr-2"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        {{ errorMessage }}
                    </div>
                </transition>

                <!-- Login Link -->
                <div class="text-center text-xs sm:text-sm pt-2">
                    <p class="text-gray-600">
                        {{
                            frontLang?.lang?.haveAccount ||
                            "Already have an account?"
                        }}
                        <button
                            @click="routeToLogin"
                            class="text-lime-500 hover:text-lime-600 hover:underline font-medium focus:outline-none"
                        >
                            {{ frontLang?.lang?.login || "Log in" }}
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}

/* Thin scrollbar styles */
.max-h-\[95vh\]::-webkit-scrollbar {
    width: 4px;
}

.max-h-\[95vh\]::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.max-h-\[95vh\]::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.max-h-\[95vh\]::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Hide scrollbar while maintaining scroll functionality */
.max-h-\[95vh\]::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

.max-h-\[95vh\]::-webkit-scrollbar-track {
    background: transparent;
}

.max-h-\[95vh\]::-webkit-scrollbar-thumb {
    background: transparent;
}

.max-h-\[95vh\]::-webkit-scrollbar-thumb:hover {
    background: transparent;
}

/* For Firefox - hide scrollbar */
.max-h-\[95vh\] {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
</style>
