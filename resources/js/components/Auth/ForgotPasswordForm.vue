  <script setup>
  import { ref } from "vue";
  
  const email = ref("");
  const loading = ref(false);
  
  const handleReset = () => {
    loading.value = true;
    setTimeout(() => {
      alert(`A reset link has been sent to ${email.value}`);
      loading.value = false;
    }, 1500);
  };
  </script>
<template>
    <div class="flex justify-center items-center h-screen bg-gray-100 px-4">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm mx-auto">
        <!-- Title -->
        <h1 class="text-2xl font-bold text-center mb-4">Forgot Your Password?</h1>
        <p class="text-gray-600 text-center mb-6">Enter your email to reset your password.</p>
  
        <!-- Forgot Password Form -->
        <form @submit.prevent="handleReset" class="space-y-4">
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
  
          <!-- Reset Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition duration-300"
          >
            <span v-if="loading">Sending Reset Link...</span>
            <span v-else>Send Reset Link</span>
          </button>
        </form>
  
        <!-- Back to Login Link -->
        <div class="mt-6 text-center text-sm">
          <p>Remember your password? <a href="/login" class="text-blue-500 hover:underline">Log in here</a></p>
        </div>
      </div>
    </div>
  </template>
  
  <style scoped>
  /* Make the input fields and button more compact for better responsiveness */
  .bg-white {
    max-width: 400px;
    margin: 0 auto;
  }
  
  input, button {
    margin-top: 10px;
  }
  
  @media (max-width: 640px) {
    .bg-white {
      padding: 16px;
    }
  }
  </style>
  