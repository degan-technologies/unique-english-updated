<script setup>
import { ref } from "vue";
const email = ref("");
const password = ref("");
const showPassword = ref(false);
const loading = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const handleLogin = () => {
    loading.value = true;
    setTimeout(() => {
        if (
            email.value === "test@example.com" &&
            password.value === "password"
        ) {
            alert("Login successful!");
        } else {
            alert("Invalid credentials, please try again.");
        }
        loading.value = false;
    }, 1500);
};
</script>

<template>
    <div class="flex justify-center items-center h-screen bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <!-- Title -->
            <h1 class="text-2xl font-bold text-center mb-4">Welcome Back!</h1>
            <p class="text-gray-600 text-center mb-6">
                Please log in to your account
            </p>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin" class="space-y-4">
                <!-- Email Input -->
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700"
                        >Email</label
                    >
                    <input
                        type="email"
                        id="email"
                        v-model="email"
                        placeholder="Enter your email"
                        required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Password Input -->
                <div>
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700"
                        >Password</label
                    >
                    <div class="relative">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            v-model="password"
                            placeholder="Enter your password"
                            required
                            class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <button
                            type="button"
                            @click="togglePassword"
                            class="absolute right-3 top-3 text-gray-500 focus:outline-none"
                        >
                            <span v-if="showPassword">🙈</span>
                            <span v-else>👁️</span>
                        </button>
                    </div>
                </div>

                <!-- Login Button -->
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition duration-300"
                >
                    <span v-if="loading">Logging in...</span>
                    <span v-else>Login</span>
                </button>
            </form>

            <!-- Social Login -->
            <div class="mt-6 text-center">
                <p class="text-gray-600 mb-2">Or log in with</p>
                <div class="flex justify-center space-x-4">
                    <button
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300"
                    >
                        Google
                    </button>
                    <button
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300"
                    >
                        Facebook
                    </button>
                </div>
            </div>

            <!-- Additional Links -->
            <div class="mt-6 text-center text-sm">
                <a href="/forgot-password" class="text-blue-500 hover:underline"
                    >Forgot Password?</a
                >
                <span class="text-gray-500 mx-2">|</span>
                <a href="/register" class="text-blue-500 hover:underline"
                    >Sign Up</a
                >
            </div>
        </div>
    </div>
</template>
