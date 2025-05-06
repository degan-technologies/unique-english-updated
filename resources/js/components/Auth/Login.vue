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

const showpasswordInput = ref(false);
const loggingIn = ref(false);
const loginMessage = ref("");

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

function closeLoginForm() {
    showLoginForm.value = false;
}

function roteToLogin() {
    showLoginForm.value = false;
    showRegistrationForm.value = true;
}

onMounted(() => {
    appStore.fetchFrontLanguages();
});

const socialLogin = (provider) => {
    window.location.href = `http://127.0.0.1:8000/auth/${provider}/redirect`;
    appStore.changeLoginStatus(true);
    console.log("provider");
};
</script>

<template>
    <div
        @click="closeLoginForm()"
        class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 p-4"
    >
        <!-- Login Form Section -->
        <div
            @click.stop
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md my-25"
        >
            <!-- Close Icon -->
            <button
                @click="closeLoginForm"
                class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
            >
                <i class="fas fa-times text-xl"></i>
            </button>

            <div class="p-8">
                <p class="text-lime-600 text-left font-bold mb-6 text-lg">
                    Login To Your Account
                </p>

                <div v-if="loginMessage" class="text-rose-500 text-center mb-4">
                    {{ loginMessage }}
                </div>

                <div class="space-y-5">
                    <div>
                        <label
                            for="emailInput"
                            class="block text-sm font-medium text-gray-700"
                            >Email</label
                        >
                        <input
                            type="email"
                            id="emailInput"
                            v-model="emailInput"
                            placeholder="Enter your email"
                            required
                            class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700"
                        />
                    </div>

                    <div>
                        <label
                            for="passwordInput"
                            class="block text-sm font-medium text-gray-700"
                            >Password</label
                        >
                        <div class="relative">
                            <input
                                :type="showpasswordInput ? 'text' : 'password'"
                                id="passwordInput"
                                v-model="passwordInput"
                                placeholder="Enter your password"
                                required
                                class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700"
                            />
                            <button
                                type="button"
                                @click="toggleShowPassword"
                                class="absolute p-3 px-0 right-3 top-3 text-gray-500"
                            >
                                <span v-if="showpasswordInput">🙈</span>
                                <span v-else>👁️</span>
                            </button>
                        </div>
                    </div>

                    <button
                        @click="tryLogin"
                        :disabled="loggingIn"
                        class="w-full py-3 bg-lime-700 text-white rounded-lg font-semibold hover:bg-lime-800 active:scale-95 transition-transform"
                    >
                        {{
                            loggingIn
                                ? "Logging in..."
                                : frontLang.lang?.login || "Login"
                        }}
                    </button>
                </div>

                <!-- Social Login Section -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600 mb-2">Or log in with</p>
                    <div class="flex justify-center space-x-4">
                        <button
                            @click="socialLogin('google')"
                            class="social-btn"
                        >
                            <i class="fab fa-google"></i>
                        </button>
                        <button
                            @click="socialLogin('facebook')"
                            class="social-btn"
                        >
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button
                            @click="socialLogin('linkedin')"
                            class="social-btn"
                        >
                            <i class="fab fa-linkedin-in"></i>
                        </button>
                        <button
                            @click="socialLogin('twitter')"
                            class="social-btn"
                        >
                            <i class="fab fa-twitter"></i>
                        </button>
                    </div>
                </div>

                <!-- Register Link -->
                <div class="mt-5 text-center">
                    <span
                        @click="roteToLogin()"
                        class="text-lime-700 font-semibold hover:underline cursor-pointer"
                        >Don't have an account? Register</span
                    >
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
