<template>
    <div class="flex justify-center items-center h-screen bg-gray-100">
      <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <!-- Title -->
        <h1 class="text-2xl font-bold text-center mb-4">Multi-Factor Authentication</h1>
        <p class="text-gray-600 text-center mb-6">Please enter the 6-digit code sent to your email.</p>
  
        <!-- MFA Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Input Fields for MFA Code -->
          <div class="flex justify-between">
            <input
              type="text"
              maxlength="1"
              v-for="(code, index) in code"
              :key="index"
              v-model="code[index]"
              @input="handleInput(index)"
              class="w-12 h-12 text-center text-xl font-bold bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none focus:border-gold-500"
              style="color: gold"
              :style="inputStyle"
              required
            />
          </div>
  
          <!-- Submit Button -->
          <button
            type="submit"
            class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition duration-300"
          >
            Verify Code
          </button>
        </form>
  
        <!-- Resend Link -->
        <div class="mt-4 text-center text-sm">
          <p>If you didn't receive the code, <a href="#" class="text-blue-500 hover:underline">Resend Code</a></p>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref } from 'vue';
  
  export default {
    name: 'MFAForm',
    setup() {
      const code = ref(["", "", "", "", "", ""]); // 6 inputs for MFA code
      const inputStyle = {
        borderColor: "gold",
        color: "gold",
      };
  
      // Handle input focus for auto navigation
      const handleInput = (index) => {
        if (code.value[index].length === 1 && index < code.value.length - 1) {
          document.getElementById(`code-${index + 1}`).focus();
        }
      };
  
      // Handle form submission
      const handleSubmit = () => {
        if (code.value.join("") === "123456") {  // Example: Replace with actual validation
          alert("MFA successful!");
        } else {
          alert("Invalid code, please try again.");
        }
      };
  
      return {
        code,
        handleInput,
        handleSubmit,
        inputStyle
      };
    },
  };
  </script>
  
  <style scoped>
  input:focus {
    border-color: gold;
    outline: none;
  }
  </style>
  