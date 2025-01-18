<template>
    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 min-h-screen flex items-center justify-center px-4 py-8">
      <div class="bg-white p-8 rounded-lg shadow-lg max-w-3xl w-full">
        <!-- Header Section -->
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-4">Contact Us</h1>
        <p class="text-center text-gray-600 mb-6">Have any questions or feedback? We'd love to hear from you. Please fill out the form below, and we'll get back to you as soon as possible!</p>
  
        <!-- Contact Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Name Input -->
          <div>
            <label for="name" class="block text-lg font-medium text-gray-700">Your Name</label>
            <input
              type="text"
              id="name"
              v-model="name"
              placeholder="Enter your name"
              required
              class="mt-2 w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
            />
          </div>
  
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-lg font-medium text-gray-700">Your Email</label>
            <input
              type="email"
              id="email"
              v-model="email"
              placeholder="Enter your email"
              required
              class="mt-2 w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
            />
          </div>
  
          <!-- Message Textarea -->
          <div>
            <label for="message" class="block text-lg font-medium text-gray-700">Your Message</label>
            <textarea
              id="message"
              v-model="message"
              rows="6"
              placeholder="Enter your message"
              required
              class="mt-2 w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
            ></textarea>
          </div>
  
          <!-- Submit Button -->
          <button
            type="submit"
            class="w-full py-3 text-white font-semibold bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none transition duration-300"
          >
            Send Message
          </button>
        </form>
  
        <!-- Contact Details Section -->
        <div class="mt-8 text-center">
          <p class="text-lg text-gray-700 mb-4">Or connect with us through our social channels:</p>
          <div class="flex justify-center gap-6">
            <!-- Facebook -->
            <a href="#" class="text-blue-500 hover:text-blue-600">
              <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 8c0 2.21-1.79 4-4 4S8 10.21 8 8s1.79-4 4-4 4 1.79 4 4zM12 14c2.97 0 6 1.5 6 3v2H6v-2c0-1.5 3.03-3 6-3z"/>
              </svg>
            </a>
  
            <!-- Telegram -->
            <a href="#" class="text-blue-400 hover:text-blue-500">
              <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18"/>
              </svg>
            </a>
  
            <!-- Instagram -->
            <a href="#" class="text-pink-500 hover:text-pink-600">
              <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V3H7v6H2v12h20V9h-5zM12 2v7h7v7H5V9h7V2z"/>
              </svg>
            </a>
  
            <!-- Twitter -->
            <a href="#" class="text-blue-300 hover:text-blue-400">
              <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 6.25c-.77.35-1.6.58-2.47.68a4.5 4.5 0 0 0 1.98-2.48A9.58 9.58 0 0 1 18.47 5a4.5 4.5 0 0 0-8.95 2.1 4.47 4.47 0 0 0 .12 1.03A12.9 12.9 0 0 1 3 4.6a4.48 4.48 0 0 0-.61 2.27A4.5 4.5 0 0 0 4 11.23a4.5 4.5 0 0 1-2-.56v.06A4.5 4.5 0 0 0 4.1 15a4.53 4.53 0 0 1-2.05.07 4.5 4.5 0 0 0 4.2 3.13A9.07 9.07 0 0 1 2 20.75a9.11 9.11 0 0 0 6.8 2.27c8.14 0 12.63-6.67 12.63-12.46v-.56a9.04 9.04 0 0 0 2.37-2.28c-.04-.03-.08-.07-.13-.1z"/>
              </svg>
            </a>
  
            <!-- LinkedIn -->
            <a href="#" class="text-blue-700 hover:text-blue-800">
              <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 3.5A1.5 1.5 0 1 1 6 5a1.5 1.5 0 0 1-1.5-1.5zM3 3h3c1.5 0 3 1.5 3 3v12c0 1.5-1.5 3-3 3H3c-1.5 0-3-1.5-3-3V6c0-1.5 1.5-3 3-3zM5 9h2v10H5V9zm4-2h2v12H9V7z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref } from 'vue';
  
  export default {
    name: 'ContactUs',
    setup() {
      const name = ref('');
      const email = ref('');
      const message = ref('');
  
      const handleSubmit = () => {
        if (!name.value || !email.value || !message.value) {
          alert('Please fill out all fields.');
          return;
        }
        alert(`Thank you, ${name.value}! We will get back to you soon.`);
        // Handle sending the message logic here (e.g., send an email, save to database)
      };
  
      return {
        name,
        email,
        message,
        handleSubmit,
      };
    },
  };
  </script>
  
  <style scoped>
  /* Input Field Styling */
  input,
  textarea {
    transition: all 0.3s ease;
  }
  
  input:focus,
  textarea:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 5px rgba(79, 70, 229, 0.6);
  }
  
  /* Hover effects for social media icons */
  a:hover svg {
    transform: scale(1.1);
    transition: transform 0.2s;
  }
  </style>
  