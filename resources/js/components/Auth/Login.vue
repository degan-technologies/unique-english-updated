<script setup>
    import Axios from 'axios';
    import { onMounted, ref } from 'vue';
    import { storeToRefs } from 'pinia';

    import {useAppStore} from '@/store/useAppStore';

    const appStore = useAppStore();

    const emailInput = ref("");
    const passwordInput = ref("");

    const showpasswordInput = ref(false);
    const loading = ref(false);

    const loggingIn = ref(false);
    const loginMessage = ref('');

    const { lang, frontLang } = storeToRefs(appStore);

    const toggleShowPassword = () => showpasswordInput.value = !showpasswordInput.value;

    lang.value = 'en';

    function tryLogin() {
        if(loggingIn.value) return;

        let formData = new FormData();
        formData.append('email', emailInput.value);
        formData.append('password', passwordInput.value);

        loggingIn.value = true;
        Axios
            .post('/login', formData)
            .then(response => {
                appStore.setAuthToken(response.data.token);
                appStore.changeLoginStatus(true);
            })
            .catch(error => {
                appStore.setAuthToken('');
                loginMessage.value = error.response.data.message;

                setTimeout(() => loginMessage.value = '', 2000);
            })
            .finally(() => loggingIn.value = false)
    }

    onMounted(() => {
        appStore.fetchFrontLanguages();
    })
</script>
<template>
    <div class="flex justify-center overflow-hidden items-center w-screen absolute top-0 left-0 h-screen bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h1 class="text-2xl font-bold text-center mb-4">Welcome Back!</h1>
            <p class="text-gray-600 text-center mb-6">
                Please log in to your account
            </p>
            <div class="px-10 w-full text-xs text-rose-500 h-2 text-center">
                {{ loginMessage }}
            </div>
            <div class="space-y-4">
                <div>
                    <label
                        for="emailInput"
                        class="block text-sm font-medium text-gray-700">
                        emailInput
                    </label>
                    <input
                        type="email"
                        id="emailInput"
                        v-model="emailInput"
                        placeholder="Enter your emailInput"
                        required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                </div>
                <div>
                    <label
                        for="passwordInput"
                        class="block text-sm font-medium text-gray-700"
                        >password</label>
                    <div class="relative">
                        <input
                            :type="showpasswordInput ? 'text' : 'password'"
                            id="passwordInput"
                            v-model="passwordInput"
                            placeholder="Enter your passwordInput"
                            required
                            class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        <button
                            type="button"
                            @click="toggleShowPassword"
                            class="absolute right-3 top-3 text-gray-500 focus:outline-none" >
                            <span v-if="showpasswordInput">🙈</span>
                            <span v-else>👁️</span>
                        </button>
                    </div>
                </div>
                 <div class="px-10 w-full">
                <div 
                    @click="tryLogin()"
                    class="flex justify-center gap-1 w-full font-bold py-1.5 rounded-md cursor-pointer text-white text-center bg-blue-500">
                        <div 
                            v-if="loggingIn"
                            class="text-sm self-center">
                            <i class="fa-solid fa-spinner animate-spin"></i>
                        </div>
                        <div class="self-center">{{ frontLang.lang?.login }}</div>
                    </div>
                </div>
            </div>

            <!-- Social Login -->
            <div class="mt-6 text-center">
                <p class="text-gray-600 mb-2">Or log in with</p>
                <div class="flex justify-center space-x-4">
                    <button
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300">
                        Google
                    </button>
                    <button
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300" >
                        Facebook
                    </button>
                </div>
            </div>

            <!-- Additional Links -->
            <div class="mt-6 text-center text-sm">
                <a href="/forgot-passwordInput" class="text-blue-500 hover:underline"
                    >Forgot passwordInput?</a>
                <span class="text-gray-500 mx-2">|</span>
                <a href="/register" class="text-blue-500 hover:underline"
                    >Sign Up</a>
            </div>
        </div>
    </div>
</template>