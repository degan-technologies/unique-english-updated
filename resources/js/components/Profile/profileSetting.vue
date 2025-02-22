<template>
    <div class="min-h-screen flex items-center justify-center p-4">
      <div class="w-full max-w-2xl bg-white rounded-lg p-4 space-y-8">
        <!-- Header -->
        <h1 class="text-3xl font-bold text-center text-lime-700">
          {{ frontLang.lang.updateProfile }}
        </h1>
  
    <!-- Profile Banner with Background Image -->
    <div class="relative w-full h-64 rounded-lg overflow-hidden border border-gray-200 shadow-md">
    <!-- Background Image (as CSS background) -->
    <div
      v-if="authUser.bg_image"
      :style="{ backgroundImage: `url(${authUser.bg_image})` }"
      class="absolute inset-0 bg-cover bg-center"
    ></div>
    <!-- Optional overlay for better contrast -->
    <div class="absolute inset-0 bg-black opacity-30"></div>
    <!-- Profile Image positioned at the bottom center -->
    <div class="relative flex items-center justify-center h-full">
      <div class="absolute bottom-0 transform translate-y-0.6">
        <div class="w-32 h-32 rounded-full border-4 border-white overflow-hidden shadow-lg">
          <img
            v-if="authUser.profile"
            :src="authUser.profile"
            alt="Profile Image"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center bg-gray-300 text-gray-700">
            No Image
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- File Inputs for Updating Images -->
  <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Profile Image Input -->
    <div class="flex flex-col items-center">
      <label class="block text-lg font-medium text-gray-700 mb-2">
        {{ frontLang.lang.profile }}
      </label>
      <input
        type="file"
        @change="onFileChange('profile', $event)"
        class="w-full p-2 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-lime-700"
      />
      <span v-if="errors.profile" class="text-red-500 text-sm mt-1">
        {{ errors.profile }}
      </span>
    </div>
    <!-- Background Image Input -->
    <div class="flex flex-col items-center">
      <label class="block text-lg font-medium text-gray-700 mb-2">
        {{ frontLang.lang.backgroundImage }}
      </label>
      <input
        type="file"
        @change="onFileChange('bg_image', $event)"
        class="w-full p-2 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-lime-700"
      />
      <span v-if="errors.bg_image" class="text-red-500 text-sm mt-1">
        {{ errors.bg_image }}
      </span>
    </div>
  </div>
  
        <!-- Profile Form -->
        <form @submit.prevent="submitForm" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- First Name -->
            <div>
              <label class="block text-gray-700 font-medium mb-1">
                {{ frontLang.lang.firstName }}:
              </label>
              <input
                v-model="form.first_name"
                type="text"
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
              />
              <span v-if="errors.first_name" class="text-red-500 text-sm">
                {{ errors.first_name }}
              </span>
            </div>
            <!-- Middle Name -->
            <div>
              <label class="block text-gray-700 font-medium mb-1">
                {{ frontLang.lang.middleName }}:
              </label>
              <input
                v-model="form.middle_name"
                type="text"
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
              />
              <span v-if="errors.middle_name" class="text-red-500 text-sm">
                {{ errors.middle_name }}
              </span>
            </div>
            <!-- Last Name -->
            <div>
              <label class="block text-gray-700 font-medium mb-1">
                {{ frontLang.lang.lastName }}:
              </label>
              <input
                v-model="form.last_name"
                type="text"
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
              />
              <span v-if="errors.last_name" class="text-red-500 text-sm">
                {{ errors.last_name }}
              </span>
            </div>
            <!-- Email -->
            <div>
              <label class="block text-gray-700 font-medium mb-1">
                {{ frontLang.lang.email }}:
              </label>
              <input
                v-model="form.email"
                type="email"
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
              />
              <span v-if="errors.email" class="text-red-500 text-sm">
                {{ errors.email }}
              </span>
            </div>
            <!-- Phone -->
            <div>
              <label class="block text-gray-700 font-medium mb-1">
                {{ frontLang.lang.phone }}:
              </label>
              <input
                v-model="form.phone"
                type="text"
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
              />
              <span v-if="errors.phone" class="text-red-500 text-sm">
                {{ errors.phone }}
              </span>
            </div>
            <!-- Gender -->
            <div>
              <label class="block text-gray-700 font-medium mb-1">
                {{ frontLang.lang.gender }}:
              </label>
              <select
                v-model="form.gender"
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
              >
                <option value="">{{ frontLang.lang.selectGender }}</option>
                <option value="1">{{ frontLang.lang.male }}</option>
                <option value="2">{{ frontLang.lang.female }}</option>
              </select>
              <span v-if="errors.gender" class="text-red-500 text-sm">
                {{ errors.gender }}
              </span>
            </div>
          </div>
  
          <!-- Submit Button -->
          <button
            type="submit"
            class="w-full bg-lime-700 text-white p-3 rounded-lg hover:bg-lime-800 transition flex justify-center"
          >
            {{ frontLang.lang.UpdateProfile }}
          </button>
  
          <!-- Success Message -->
          <p v-if="message" class="text-center text-lime-700 text-sm font-medium mt-3">
            {{ message }}
          </p>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, computed } from 'vue';
  import { storeToRefs } from 'pinia';
  import { useAppStore } from '@/store/useAppStore';
  import Axios from 'axios';
  
  // Access the app store
  const appStore = useAppStore();
  const { authUser, frontLang } = storeToRefs(appStore);
  const fetchUserInfo = appStore.fetchUserInfo;
  
  // Form data and reactive states
  const form = ref({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    phone: '',
    gender: '',
    profile: null, // For file upload
    bg_image: null, // For file upload
  });
  
  const originalData = ref({}); // Store the original data
  const errors = ref({});
  const message = ref('');
  
  // Fetch user profile on component mount
  onMounted(async () => {
    await fetchProfile();
  });
  
  // Fetch user profile data
  const fetchProfile = async () => {
    try {
      await fetchUserInfo(); // Fetch from store
  
      if (authUser.value) {
        // Store the original values for comparison
        originalData.value = { ...authUser.value };
  
        // Populate the form with existing user data
        form.value.first_name = authUser.value.first_name || '';
        form.value.middle_name = authUser.value.middle_name || '';
        form.value.last_name = authUser.value.last_name || '';
        form.value.email = authUser.value.email || '';
        form.value.phone = authUser.value.phone || '';
        form.value.gender = authUser.value.gender || '';
        form.value.profile = null; // Files should not be pre-filled
        form.value.bg_image = null;
      }
    } catch (error) {
      console.error('Failed to fetch profile:', error);
    }
  };
  
  // Computed property to check if form data has changed
  const isFormChanged = computed(() => {
    return Object.keys(originalData.value).some(key => {
      return key !== 'profile' && key !== 'bg_image' && form.value[key] !== originalData.value[key];
    }) || form.value.profile || form.value.bg_image;
  });
  
  // Handle form submission
  const submitForm = async (event) => {
    event.preventDefault();
  
    if (!isFormChanged.value) {
      message.value = 'No changes detected.';
      setTimeout(() => (message.value = ''), 2000);
      return;
    }
  
    errors.value = {};
    message.value = '';
  
    const formData = new FormData();
  
    // Only append changed fields
    Object.keys(form.value).forEach(key => {
      if (key !== 'profile' && key !== 'bg_image' && form.value[key] !== originalData.value[key]) {
        formData.append(key, form.value[key]);
      }
    });
  
    // Append files only if changed
    if (form.value.profile) {
      formData.append('profile', form.value.profile);
    }
    if (form.value.bg_image) {
      formData.append('bg_image', form.value.bg_image);
    }
  
    try {
      const res = await Axios.post('/api/update-profile', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
  
      message.value = res.data.message;
      await fetchProfile(); // Refresh profile after update
  
      setTimeout(() => (message.value = ''), 2000);
    } catch (err) {
      errors.value = err.response?.data?.message || 'An error occurred';
      setTimeout(() => (errors.value = ''), 2000);
    }
  };
  
  // Handle file input changes
  const onFileChange = (field, event) => {
    const file = event.target.files[0];
    if (file) {
      form.value[field] = file;
    }
  };
  </script>
  
  <style>
  .error {
    color: red;
    font-size: 0.9rem;
  }
  
  .success {
    color: green;
    font-size: 1rem;
    margin-top: 10px;
  }
  </style>
  