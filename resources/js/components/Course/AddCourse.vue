<template>
  <div class="flex justify-center items-center min-h-screen p-4 ">
    <div class="w-full max-w-3xl bg-white rounded-xl shadow-lg p-6">
      <!-- Title -->
      <h2 class="text-3xl font-bold text-center text-lime-700 mb-6">
        📚 Add a New Course
      </h2>

      <!-- Success Message -->
      <transition name="fade">
        <div v-if="successMessage" class="mb-6 p-4 bg-green-100 text-green-700 border border-green-400 rounded-lg text-center">
          ✅ {{ successMessage }}
        </div>
      </transition>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Course Name -->
        <div class="form-group">
          <label class="block text-gray-700 font-semibold mb-1">
            Course Name <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="course.course_name" 
            type="text"
            class="form-input focus:ring-2 focus:ring-lime-700"
            :class="{'border-red-500': errors.course_name}" 
            placeholder="Enter course name" />
          <p v-if="errors.course_name" class="error-text">{{ errors.course_name }}</p>
        </div>

        <!-- Overview -->
        <!-- Overview (Replaced Textarea with PrimeVue Editor) -->
        <div class="form-group">
          <label class="block text-gray-700 font-semibold mb-1">Overview</label>
          <Editor 
            v-model="course.overview"
            editorStyle="height: 200px"
            :class="{'border-red-500': errors.overview}"
          />
          <p v-if="errors.overview" class="error-text">{{ errors.overview }}</p>
        </div>

        <!-- Tags -->
        <div class="form-group">
          <label class="block text-gray-700 font-semibold mb-1">Tags</label>
          <input 
            v-model="course.tag" 
            type="text"
            class="form-input focus:ring-2 focus:ring-lime-700"
            :class="{'border-red-500': errors.tag}" 
            placeholder="e.g. Programming, AI, Web Dev" />
          <p v-if="errors.tag" class="error-text">{{ errors.tag }}</p>
        </div>

        <!-- Skill Level -->
        <div>
          <label class="block text-md font-medium text-gray-600 mb-1">
            Skill Level <span class="text-red-500">*</span>
          </label>
          <select 
            v-model="course.skill_level"
            class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700"
            required
          >
            <option :value="1">BEGINNER</option>
            <option :value="2">INTERMEDIATE</option>
            <option :value="3">ADVANCE</option>
            <option :value="4">FULL PACKAGE</option>
          </select>
          <p v-if="errors.skill_level" class="error-text">{{ errors.skill_level }}</p>
        </div>

        <!-- Price & Discount -->
        <div class="grid grid-cols-2 gap-6">
          <div class="form-group">
            <label class="block text-gray-700 font-semibold mb-1">Price ($)</label>
            <input 
              v-model="course.price" 
              type="number" min="0" step="0.01"
              class="form-input focus:ring-2 focus:ring-lime-700"
              :class="{'border-red-500': errors.price}" 
              placeholder="e.g. 99.99" />
            <p v-if="errors.price" class="error-text">{{ errors.price }}</p>
          </div>

          <div class="form-group">
            <label class="block text-gray-700 font-semibold mb-1">Discount (%)</label>
            <input 
              v-model="course.discount" 
              type="number" min="0" max="100" step="0.01"
              class="form-input focus:ring-2 focus:ring-lime-700"
              :class="{'border-red-500': errors.discount}" 
              placeholder="e.g. 10" />
            <p v-if="errors.discount" class="error-text">{{ errors.discount }}</p>
          </div>
        </div>

        <!-- Credit Hour -->
        <div class="form-group">
          <label class="block text-gray-700 font-semibold mb-1">Credit Hour</label>
          <input 
            v-model="course.credit_hour" 
            type="number" min="1"
            class="form-input focus:ring-2 focus:ring-lime-700"
            :class="{'border-red-500': errors.credit_hour}" 
            placeholder="e.g. 3" />
          <p v-if="errors.credit_hour" class="error-text">{{ errors.credit_hour }}</p>
        </div>

        <!-- Upload Thumbnail -->
        <div>
          <label class="block text-md font-medium text-gray-600 mb-1">Upload Thumbnail</label>
          <input
            type="file"
            @change="onFileChange('thumbnail_url', $event)"
            class="w-full p-2 border rounded-md text-md focus:outline-none focus:ring-2 focus:ring-lime-700"
          />
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
          <button 
            type="submit"
            :disabled="loading"
            class="w-fit bg-gradient-to-r from-lime-700 to-lime-600 text-white py-2 px-4 rounded-lg hover:opacity-90 transition duration-300 text-md mr-5"
          >
            <span v-if="loading">⏳ Adding Course...</span>
            <span v-else> Add Course</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Axios from 'axios';
import Editor from 'primevue/editor';
const course = ref({
  course_name: '',
  overview: '',
  tag: '',
  skill_level: '',
  price: '',
  discount: '',
  credit_hour: '',
  thumbnail_url: null
});

const errors = ref({});
const successMessage = ref('');
const loading = ref(false);
const onFileChange = (field, event) => {
    const file = event.target.files[0];
    if (file) {
        course.value[field] = file;
    }
};

const validateForm = () => {
  errors.value = {};
  let isValid = true;

  if (!course.value.course_name.trim()) {
    errors.value.course_name = 'Course name is required.';
    isValid = false;
  }

  if (!course.value.skill_level || course.value.skill_level <= 0) {
    errors.value.skill_level = 'Skill level must be at least 1.';
    isValid = false;
  }

  if (course.value.price && course.value.price < 0) {
    errors.value.price = 'Price must be a positive number.';
    isValid = false;
  }

  if (course.value.discount && (course.value.discount < 0 || course.value.discount > 100)) {
    errors.value.discount = 'Discount must be between 0 and 100.';
    isValid = false;
  }

  if (!course.value.credit_hour || course.value.credit_hour < 1) {
    errors.value.credit_hour = 'Credit hour must be at least 1.';
    isValid = false;
  }

  return isValid;
};

const submitForm = async () => {
  if (!validateForm()) {
    return;
  }

  loading.value = true;
  errors.value = {};
  successMessage.value = '';



const formData = new FormData();

formData.append("course_name", course.value.course_name);
formData.append("overview", course.value.overview);
formData.append("tag", course.value.tag);

if (course.value.skill_level) {
    formData.append("skill_level", Number(course.value.skill_level)); // Ensure it's a number
}

if (course.value.price) {
    formData.append("price", Number(course.value.price)); // Ensure it's a number
}

if (course.value.discount) {
    formData.append("discount", Number(course.value.discount)); // Ensure it's a number
}

if (course.value.credit_hour) {
    formData.append("credit_hour", Number(course.value.credit_hour)); // Ensure it's a number
}

if (course.value.thumbnail_url) {
        formData.append('thumbnail_url', course.value.thumbnail_url);
    }


  for (let [key, value] of formData.entries()) {
    console.log(`${key}:`, value);
}

  try {
    const response = await Axios.post('/api/courses/course', formData, {
      headers: { 
        'Content-Type': 'multipart/form-data' 
      }
    });

    successMessage.value = response.data.message;
    course.value = { course_name: '', overview: '', tag: '', skill_level: '', price: '', discount: '', credit_hour: '', thumbnail_url: '' };
  } catch (error) {
    if (error.response && error.response.data.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.form-group {
  @apply flex flex-col gap-1;
}

.form-group label {
  @apply font-semibold text-gray-700;
}

.form-input {
  @apply w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 transition duration-200;
}

.error-text {
  @apply text-red-600 text-sm mt-1;
}
</style>