<script setup>
import { ref } from "vue";

// Form Data
const name = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const showPassword = ref(false);
const loading = ref(false);
const agreeTerms = ref(false);

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const handleRegister = () => {
  if (!agreeTerms.value) {
    alert("You must agree to the Terms & Conditions.");
    return;
  }

  if (password.value !== confirmPassword.value) {
    alert("Passwords do not match.");
    return;
  }

  loading.value = true;
  setTimeout(() => {
    alert(`Welcome, ${name.value}! Registration successful.`);
    loading.value = false;
  }, 1500);
};
</script>

<template>
  <div class="flex justify-center items-center h-screen bg-gray-100 p-8">
    <div class="bg-white rounded-lg shadow-lg flex w-full max-w-4xl overflow-hidden">
      <!-- Left Section -->
      <div class="hidden md:flex flex-col justify-center items-center bg-blue-500 text-white p-8 w-1/2">
        <h1 class="text-4xl font-bold mb-4">Welcome to Our Platform</h1>
        <p class="text-lg">Join us and experience the best services tailored just for you. Sign up now to begin your journey!</p>
      </div>

      <!-- Right Section -->
      <div class="p-8 w-full md:w-1/2">
        <!-- Title -->
        <h1 class="text-2xl font-bold text-center mb-4">Create Your Account</h1>
        <p class="text-gray-600 text-center mb-6">Start your journey with us today!</p>

        <!-- Social Signup -->
        <div class="flex justify-center space-x-4 mb-6">
          <button class="flex items-center justify-center px-4 py-2 border rounded-lg w-full text-gray-700 hover:bg-gray-100">
            <img src="https://img.icons8.com/color/24/google-logo.png" alt="Google" class="mr-2" />
            Sign up with Google
          </button>
          <button class="flex items-center justify-center px-4 py-2 border rounded-lg w-full text-gray-700 hover:bg-gray-100">
            <img src="https://img.icons8.com/color/24/facebook-new.png" alt="Facebook" class="mr-2" />
            Sign up with Facebook
          </button>
        </div>

        <!-- Divider -->
        <div class="relative my-6">
          <hr class="border-gray-300" />
          <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-4 text-gray-500">or</span>
        </div>

        <!-- Registration Form -->
        <form @submit.prevent="handleRegister" class="space-y-4">
          <!-- Name Input -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input
              type="text"
              id="name"
              v-model="name"
              placeholder="Enter your name"
              required
              class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Email Input -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
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
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
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

          <!-- Confirm Password Input -->
          <div>
            <label for="confirm-password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input
              type="password"
              id="confirm-password"
              v-model="confirmPassword"
              placeholder="Re-enter your password"
              required
              class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Terms & Conditions -->
          <div class="flex items-start space-x-2">
            <input
              type="checkbox"
              id="terms"
              v-model="agreeTerms"
              required
              class="mt-1"
            />
            <label for="terms" class="text-sm text-gray-700">
              I agree to the <a href="#" class="text-blue-500 hover:underline">Terms & Conditions</a>
            </label>
          </div>

          <!-- Register Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition duration-300"
          >
            <span v-if="loading">Creating Account...</span>
            <span v-else>Sign Up</span>
          </button>
        </form>

        <!-- Additional Links -->
        <div class="mt-6 text-center text-sm">
          <p>Already have an account? <a href="/login" class="text-blue-500 hover:underline">Log In</a></p>
        </div>
      </div>
    </div>
  </div>
</template>
