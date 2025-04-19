<script setup>
import Axios from "axios";
import { ref } from "vue";
import { storeToRefs } from "pinia";

import { useRouter } from "vue-router";
import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from '@/store/useAuthStore';

const router = useRouter();
const appStore = useAppStore();
const AuthStore = useAuthStore();

const { showLoginForm, showRegistrationForm, } = storeToRefs(AuthStore);
const { frontLang, facebook, google } = storeToRefs(appStore);

const verifyOtpNav = ref(false);
const name = ref("");
const email = ref("");
const password = ref("");
const showPassword = ref(false);
const loading = ref(false);
const agreeTerms = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

// OTP screen fields
const otp = ref(["", "", "", "", "", ""]); // 6-digit OTP input
const otpLoading = ref(false);
const otpError = ref("");
const otpSuccess = ref("");
const registeredEmail = ref(""); // Store email for OTP screen
const otpMethod = ref("EMAIL"); // Since OTP is sent via email

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

// Handle registration
function handleRegister() {
    if (!agreeTerms.value) {
        errorMessage.value = frontLang.value.lang.termsAndConditions;
        setTimeout(() => (errorMessage.value = ""), 2000);
        return;
    }
    loading.value = true;
    const data = {
        full_name: name.value,
        email: email.value,
        password: password.value,
    };
    Axios.post("/register", data)
        .then((res) => {
            successMessage.value = res.data.message;
            registeredEmail.value = email.value;
            otpMethod.value = res.data.otp_method;
            verifyOtpNav.value = true;  
        })
        .catch((err) => {
            errorMessage.value = err.response?.data?.message;
            setTimeout(() => (errorMessage.value = ""), 2000);
        })
        .finally(() => (loading.value = false));
}

// Handle OTP input navigation
const focusNext = (index, event) => {
    if (event.target.value.length === 1 && index < 5) {
        document.getElementById(`otp-${index + 1}`).focus();
    }
};

// Handle OTP submission
const handleOtpSubmit = () => {
    const otpCode = otp.value.join("");
    if (otpCode.length !== 6) {
        otpError.value = "Please enter a 6-digit OTP";
        setTimeout(() => (otpError.value = ""), 2000);
        return;
    }
    otpLoading.value = true;
    Axios.post("/api/verify-otp", {
        email: registeredEmail.value,
        otp: otpCode,
    })
        .then((res) => {
            otpSuccess.value = res.data.message;
            appStore.setAuthToken(response.data.token);
            appStore.changeLoginStatus(true);
            showRegistrationForm.value = false;
            setTimeout(() => {
                otpSuccess.value = "";
                router.push({
                    name: 'student'
                });
            }, 1500);
        })
        .catch((err) => {
            otpError.value = err.response?.data?.message || "Invalid OTP";
            setTimeout(() => (otpError.value = ""), 2000);
        })
        .finally(() => (otpLoading.value = false));
};

// Resend OTP (optional feature)
const resendOtp = () => {
    otpLoading.value = true;
    Axios.post("/api/resend-otp", {
        email: registeredEmail.value,
    })
        .then((res) => {
            otpSuccess.value = "OTP resent successfully!";
            setTimeout(() => (otpSuccess.value = ""), 2000);
        })
        .catch((err) => {
            otpError.value = err.response?.data?.message || "Failed to resend OTP";
            setTimeout(() => (otpError.value = ""), 3000);
        })
        .finally(() => (otpLoading.value = false));
};

const socialLogin = (provider) => {
    window.location.href = `http://127.0.0.1:8000/auth/${provider}/redirect`;
    appStore.changeLoginStatus(true);
    console.log('provider');
};

function closeRegistrationinForm() {
    showRegistrationForm.value = false;
}

function roteToLogin() {
    showRegistrationForm.value = false;
    showLoginForm.value = true;
}
</script>

<template>
    <!-- Registration Screen -->
    <div v-if="!verifyOtpNav"
        @click="closeRegistrationinForm()"
        class="flex h-screen w-screen overflow-hidden  relative scrollbar-thin scrollbar-thumb-lime-700 scrollbar-track-lime-300 items-center justify-center">
        <div v-if="frontLang?.lang"
            @click.stop
            class="flex items-center h-fit justify-center w-full lg:w-1/2 p-8 relative z-1">            

            <!-- Registration Form with Glassmorphism -->
            <div
                class="bg-white backdrop-blur-lg h-fit p-10 rounded-2xl shadow-2xl w-full max-w-md">
                <!-- Social Login Section -->
                <div class="text-center">
                    <p class="text-lime-600 text-lg font-bold mb-4"> {{ frontLang.lang.startjourneywithus }}</p>
                    <div class="flex justify-center space-x-6">
                        <button @click="socialLogin('google')" class="social-btn"><i class="fab fa-google"></i></button>
                        <button @click="socialLogin('facebook')" class="social-btn"><i
                                class="fab fa-facebook-f"></i></button>
                        <button @click="socialLogin('linkedin')" class="social-btn"><i
                                class="fab fa-linkedin-in"></i></button>
                        <button @click="socialLogin('twitter')" class="social-btn"><i
                                class="fab fa-twitter"></i></button>
                    </div>
                </div>

                <div class="relative my-6">
                    <hr class="border-gray-300" />
                    <span
                        class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-4 text-gray-500">
                        {{ frontLang.lang.or }}
                    </span>
                </div>

                <form @submit.prevent="handleRegister" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            {{ frontLang.lang.name }}
                        </label>
                        <input type="text" id="name" v-model="name" placeholder="Enter your name" required
                            class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            {{ frontLang.lang.email }}
                        </label>
                        <input type="email" id="email" v-model="email" placeholder="Enter your email" required
                            class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700" />
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            {{ frontLang.lang.password }}
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="password"
                                placeholder="Enter your password" required
                                class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700" />
                            <button type="button" @click="togglePassword"
                                class="absolute right-3 top-3 text-gray-500 focus:outline-none">
                                <span v-if="showPassword">🙈</span>
                                <span v-else>👁️</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-start space-x-2">
                        <input type="checkbox" id="terms" v-model="agreeTerms" class="mt-1" />
                        <label for="terms" class="text-sm text-gray-700">
                            {{ frontLang.lang.agreeto }}
                            <a href="#" class="text-lime-700 hover:underline">
                                {{ frontLang.lang.termsAndConditions }}
                            </a>
                        </label>
                    </div>
                    <button type="submit" :disabled="loading"
                        class="w-full bg-lime-700 text-white py-3 rounded-lg hover:bg-lime-800 transition duration-300">
                        <span v-if="loading">{{ frontLang.lang.CreatingAccount }}</span>
                        <span v-else>{{ frontLang.lang.signup }}</span>
                    </button>
                </form>

                <div v-if="successMessage" class="mt-4 text-green-500 text-center font-semibold animate-pulse">
                    {{ successMessage }}
                </div>
                <div v-if="errorMessage" class="mt-4 text-red-500 text-center font-semibold">
                    {{ errorMessage }}
                </div>
                <div class="mt-6 text-center text-sm">
                    <p>
                        {{ frontLang.lang.haveAccount }}
                        <span
                            @click="roteToLogin()" 
                            class="text-lime-700 hover:underline">{{ frontLang.lang.login }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Verification Screen -->
    <div v-else
        @click="closeRegistrationinForm()"
        class="flex items-center justify-center min-h-screen">
        <div
            @click.stop
            class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-300 ">
            <h1 class="text-3xl font-bold text-center mb-4 text-lime-700">Verify Your OTP</h1>
            <p class="text-gray-600 text-center mb-6">
                Enter the 6-digit code sent to your email
                <span class="font-semibold">{{ registeredEmail }}</span>.
            </p>

            <!-- OTP Input Fields -->
            <div class="flex justify-center space-x-2 mb-6">
                <input v-for="(digit, index) in otp" :key="index" :id="`otp-${index}`" v-model="otp[index]" type="text"
                    maxlength="1"
                    class="w-12 h-12 text-center text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700 focus:border-lime-700 transition-all duration-200 bg-lime-100"
                    @input="focusNext(index, $event)"
                    @keydown.backspace="index > 0 && !otp[index] ? document.getElementById(`otp-${index - 1}`).focus() : null" />
            </div>

            <!-- Submit Button -->
            <button @click="handleOtpSubmit" :disabled="otpLoading"
                class="w-full bg-lime-700 text-white py-3 rounded-lg hover:bg-lime-800 transition duration-300 flex items-center justify-center">
                <span v-if="otpLoading" class="flex items-center">
                    <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    Verifying...
                </span>
                <span v-else>Verify OTP</span>
            </button>

            <!-- Resend OTP Link -->
            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    Didn't receive the code?
                    <button @click="resendOtp" :disabled="otpLoading"
                        class="text-lime-700 hover:underline focus:outline-none">
                        Resend OTP
                    </button>
                </p>
            </div>

            <!-- Feedback Messages -->
            <div v-if="otpSuccess" class="mt-4 text-green-500 text-center font-semibold animate-pulse">
                {{ otpSuccess }}
            </div>
            <div v-if="otpError" class="mt-4 text-red-500 text-center font-semibold">
                {{ otpError }}
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Glassmorphism effect */
.glass {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Fade-in Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
</style>
