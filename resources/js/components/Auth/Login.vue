<script setup>
import Axios from 'axios';
import { onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useAppStore } from '@/store/useAppStore';

const appStore = useAppStore();

const emailInput = ref("");
const passwordInput = ref("");

const showpasswordInput = ref(false);
const loggingIn = ref(false);
const loginMessage = ref('');

const { lang, frontLang } = storeToRefs(appStore);

const toggleShowPassword = () => {
    showpasswordInput.value = !showpasswordInput.value;
};

lang.value = 'en';

function tryLogin() {
    if (loggingIn.value) return;

    let formData = new FormData();
    formData.append('email', emailInput.value);
    formData.append('password', passwordInput.value);

    loggingIn.value = true;
    Axios.post('/login', formData, { withCredentials: true })
        .then(response => {
            appStore.setAuthToken(response.data.token);
            appStore.changeLoginStatus(true);
        })
        .catch(error => {
            appStore.setAuthToken('');
            loginMessage.value = error.response.data.message;

            setTimeout(() => loginMessage.value = '', 2000);
        })
        .finally(() => loggingIn.value = false);
}

onMounted(() => {
    appStore.fetchFrontLanguages();

});

const socialLogin = (provider) => {
    window.location.href = `http://127.0.0.1:8000/auth/${provider}/redirect`;
    appStore.changeLoginStatus(true);
    console.log('provider');
};
</script>

<template>
    <div class="flex h-screen w-screen overflow-hidden bg-lime-200 relative scrollbar-thin scrollbar-thumb-lime-700 scrollbar-track-lime-300">
        <!-- Image Section with Overlay -->
        <div class="relative w-1/2 hidden lg:block">
            <img src="images/signup.jpg" alt="Login" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center">
                <h2 class="text-white text-5xl font-extrabold drop-shadow-lg">Welcome Back to Unique English!</h2>
            </div>
        </div>

        <!-- Login Form Section -->
        <div class="flex items-center justify-center w-full lg:w-1/2 p-8 relative z-10">
            <div class="bg-white/30 backdrop-blur-lg p-10 rounded-2xl shadow-2xl w-full max-w-md">
                <h1 class="text-4xl font-extrabold text-lime-700 text-center mb-6">Welcome Back!</h1>

                <p class="text-gray-600 text-center mb-8 text-lg">Please log in to your account</p>

                <div v-if="loginMessage" class="text-rose-500 text-center mb-4">{{ loginMessage }}</div>

                <div class="space-y-6">
                    <div>
                        <label for="emailInput" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            type="email"
                            id="emailInput"
                            v-model="emailInput"
                            placeholder="Enter your email"
                            required
                            class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700" />
                    </div>

                    <div>
                        <label for="passwordInput" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input
                                :type="showpasswordInput ? 'text' : 'password'"
                                id="passwordInput"
                                v-model="passwordInput"
                                placeholder="Enter your password"
                                required
                                class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-lime-700 focus:border-lime-700" />
                            <button type="button" @click="toggleShowPassword" class="absolute right-3 top-3 text-gray-500">
                                <span v-if="showpasswordInput">🙈</span>
                                <span v-else>👁️</span>
                            </button>
                        </div>
                    </div>

                    <button
                        @click="tryLogin"
                        :disabled="loggingIn"
                        class="w-full py-3 bg-lime-700 text-white rounded-lg font-semibold hover:bg-lime-800 active:scale-95 transition-transform">
                        {{ loggingIn ? 'Logging in...' : frontLang.lang?.login || 'Login' }}
                    </button>
                </div>

                <!-- Social Login Section -->
                <div class="mt-8 text-center">
                    <p class="text-gray-600 mb-4">Or log in with</p>
                    <div class="flex justify-center space-x-4">
                        <button @click="socialLogin('google')" class="social-btn"><i class="fab fa-google"></i></button>
                        <button @click="socialLogin('facebook')" class="social-btn"><i class="fab fa-facebook-f"></i></button>
                        <button @click="socialLogin('linkedin')" class="social-btn"><i class="fab fa-linkedin-in"></i></button>
                        <button @click="socialLogin('twitter')" class="social-btn"><i class="fab fa-twitter"></i></button>
                    </div>
                </div>

                <!-- Register Link -->
                <div class="mt-8 text-center">
                    <a href="/register" class="text-lime-700 font-semibold hover:underline">Don't have an account? Register</a>
                </div>
            </div>
        </div>

        <!-- Bubble Animation Background -->
        <div class="absolute inset-0 z-0 bubble-background"></div>
    </div>
</template>

<style scoped>
.bubble-background {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.bubble-background::before {
    content: '';
    position: absolute;
    top: 20%;
    left: 30%;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, rgba(102, 255, 102, 0.5), transparent);
    border-radius: 50%;
    animation: float 6s infinite ease-in-out;
}

@keyframes float {
    0% { transform: translateY(0); }
    50% { transform: translateY(-50px); }
    100% { transform: translateY(0); }
}

.social-btn {
    background: rgba(255, 255, 255, 0.4);
    padding: 12px;
    border-radius: 50%;
    transition: transform 0.3s;
}

.social-btn:hover {
    transform: scale(1.2);
}
</style>
