<script setup>
import Axios from "axios";
import { ref } from "vue";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { useAppStore } from "@/store/useAppStore";
import { useAuthStore } from "@/store/useAuthStore";

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

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

// Handle registration
async function handleRegister() {
    if (!agreeTerms.value) return;

    loading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    try {
        const data = {
            full_name: name.value,
            email: email.value,
            password: password.value,
        };

        const response = await Axios.post("/register", data);
        successMessage.value = response.data.message;
        otpEmail.value = email.value;

        // Redirect to OTP verification or home page
        setTimeout(() => {
            showRegistrationForm.value = false;
            router.push("/");
        }, 1500);
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
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 backdrop-blur-sm"
    >
        <div
            @click.stop
            class="bg-white rounded-2xl shadow-xl w-full max-w-md transform transition-all duration-300 animate-fade-in overflow-hidden"
        >
            <!-- Header with gradient background -->
            <div class="p-6 text-orange-500">
                <h2 class="text-2xl font-bold">
                    {{
                        frontLang?.lang?.startjourneywithus ||
                        "Start your journey with us"
                    }}
                </h2>
            </div>

            <!-- Form content -->
            <div class="p-6 space-y-4">
                <form @submit.prevent="handleRegister" class="space-y-4">
                    <!-- Name Field -->
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-gray-700 mb-1"
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
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition-all duration-200"
                            />
                            <div
                                class="absolute inset-y-0 right-3 flex items-center pointer-events-none"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ frontLang?.lang?.email || "Email Address" }}
                        </label>
                        <div class="relative">
                            <input
                                type="email"
                                id="email"
                                v-model="email"
                                placeholder="your@email.com"
                                required
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition-all duration-200"
                            />
                            <div
                                class="absolute inset-y-0 right-3 flex items-center pointer-events-none"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700 mb-1"
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
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition-all duration-200"
                            />
                            <button
                                type="button"
                                @click="togglePassword"
                                class="absolute p-3 px-0 right-3 top-3 text-gray-500"
                            >
                                <span v-if="showpasswordInput"
                                    ><i class="fas fa-eye-slash"></i
                                ></span>
                                <span v-else><i class="fas fa-eye"></i></span>
                            </button>
                        </div>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="terms"
                                type="checkbox"
                                v-model="agreeTerms"
                                class="focus:ring-orange-400 h-4 w-4 text-orange-500 border-gray-300 rounded"
                            />
                        </div>
                        <div class="ml-3 text-sm">
                            <label
                                for="terms"
                                class="font-medium text-gray-700"
                            >
                                {{
                                    frontLang?.lang?.agreeto || "I agree to the"
                                }}
                                <a
                                    href="#"
                                    class="text-orange-500 hover:text-orange-600 hover:underline"
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
                        class="w-full flex justify-center items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed shadow-md hover:shadow-lg"
                    >
                        <span v-if="loading" class="flex items-center">
                            <svg
                                class="animate-spin h-5 w-5 text-white"
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
                            {{
                                frontLang?.lang?.CreatingAccount ||
                                "Creating Account..."
                            }}
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
                        class="p-3 bg-green-50 text-green-700 text-sm rounded-lg flex items-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 mr-2"
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
                        class="p-3 bg-red-50 text-red-700 text-sm rounded-lg flex items-center"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 mr-2"
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
                <div class="text-center text-sm pt-2">
                    <p class="text-gray-600">
                        {{
                            frontLang?.lang?.haveAccount ||
                            "Already have an account?"
                        }}
                        <button
                            @click="routeToLogin"
                            class="text-orange-500 hover:text-orange-600 hover:underline font-medium focus:outline-none"
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
</style>
