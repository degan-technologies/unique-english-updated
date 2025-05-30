<script setup>
import Axios from 'axios';
import { storeToRefs } from 'pinia';
import { ref, onMounted } from 'vue';
import { useAppStore } from '@/store/useAppStore';
import Spinner from '@/components/Layout/Spinner.vue';

const appStore = useAppStore();
const { authUser, frontLang } = storeToRefs(appStore);

const form = ref({
    first_name: '',
    full_name: '',
    email: '',
    phone: '',
    gender: '',
    profile: null,
    bg_image: null,
});

const errors = ref({});
const message = ref('');
const isLoading = ref(false);
const isDataLoaded = ref(false);
const isUploading = ref(false);
const activeField = ref(null);

const profileInput = ref(null);
const bgInput = ref(null);

const triggerFileInput = (field) => {
    activeField.value = field;
    if (field === 'profile') {
        profileInput.value?.click();
    } else {
        bgInput.value?.click()
    };
};

const onFileChange = async (field, event) => {
    const file = event.target.files[0];
    if (!file) return;

    isUploading.value = true;
    form.value[field] = file;

    try {
        const formData = new FormData();
        formData.append(field, file);

        const response = await Axios.post('/api/profile-image/update', formData);
        authUser.value = response.data.data;
        message.value = response.data.message;
        setTimeout(() => message.value = '', 3000);
    } catch (error) {
        errors.value = error.response?.data?.errors || {};
    } finally {
        isUploading.value = false;
        activeField.value = null;
    }
};

const removeImage = async (field) => { 
    isLoading.value = true;
    try {
        const response = await Axios.post('/api/remove-image', { field });
        authUser.value = response.data.data;
        message.value = response.data.message;
        setTimeout(() => message.value = '', 3000);
    } catch (error) {
        errors.value = error.response?.data?.errors || {};
    } finally {
        isLoading.value = false;
    }
};

const submitForm = async () => {
    errors.value = {};
    isLoading.value = true;

    try {
        const response = await Axios.post('/api/update-profile', {
            full_name: form.value.full_name,
            email: form.value.email,
            phone: form.value.phone,
            gender: form.value.gender
        });

        authUser.value = response.data.data;
        message.value = response.data.message;
        setTimeout(() => message.value = '', 3000);
    } catch (error) {
        errors.value = error.response?.data?.errors || {
            general: error.response?.data?.message || 'An error occurred'
        };
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    try {
        await appStore.fetchFrontLanguages();
        await appStore.fetchUserInfo();

        if (authUser.value) {
            form.value = {
                ...authUser.value,
                full_name: `${authUser.value.first_name} ${authUser.value.middle_name || ''}`.trim()
            };
        }

        isDataLoaded.value = true;
    } catch (error) {
        console.error("Initialization error:", error);
    }
});
</script>

<template>
    <div v-if="!isDataLoaded" class="flex items-center justify-center min-h-screen">
        <Spinner />
    </div>

    <div class="w-full mx-auto">
        <div class="bg-white rounded-lg overflow-hidden">
            <!-- Background Image Section -->
            <div class="relative h-48 bg-gray-200 overflow-hidden">
                <div v-if="authUser.bg_image"
                    class="absolute inset-0 bg-cover bg-center transition-opacity duration-300 hover:opacity-90"
                    :style="{ backgroundImage: `url(${authUser.bg_image})` }">
                </div>

                <div v-else
                    class="absolute inset-0 flex items-center justify-center bg-gradient-to-r from-gray-300 to-gray-400">
                    <span class="text-gray-600">Background Image</span>
                </div>

                <!-- Background Image Controls -->
                <div class="absolute z-50 bottom-4 right-4 flex space-x-2">
                    <button @click="triggerFileInput('bg_image')"
                        class="p-2 bg-white bg-opacity-80 rounded-full shadow-md hover:bg-opacity-100 transition-all"
                        :disabled="isUploading && activeField === 'bg_image'"
                        :title="frontLang?.lang?.changeBg || 'Change background'">
                        <i v-if="isUploading && activeField === 'bg_image'" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-camera text-gray-700"></i>
                    </button>

                    <button v-if="authUser.bg_image" @click="removeImage('bg_image')"
                        class="p-2 bg-white bg-opacity-80 rounded-full shadow-md hover:bg-opacity-100 transition-all"
                        :disabled="isLoading" :title="frontLang?.lang?.removeBg || 'Remove background'">
                        <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-trash-alt text-red-500"></i>
                    </button>
                </div>
            </div>

            <!-- Profile Image Section -->
            <div class="relative px-6 -mt-16">
                <div class="flex justify-center">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-gray-200">
                            <img v-if="authUser.profile" :src="authUser.profile" class="w-full h-full object-cover"
                                :alt="form.full_name || 'Profile image'">

                            <div v-else class="w-full h-full flex items-center justify-center text-gray-500">
                                <i class="fas fa-user text-4xl"></i>
                            </div>
                        </div>

                        <!-- Profile Image Controls -->
                        <div class="absolute -bottom-2 -right-2 flex space-x-2">
                            <button @click="triggerFileInput('profile')"
                                class="p-2 bg-white rounded-full shadow-md hover:bg-gray-100 transition-all"
                                :disabled="isUploading && activeField === 'profile'"
                                :title="frontLang?.lang?.changeProfile || 'Change profile'">
                                <i v-if="isUploading && activeField === 'profile'" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-camera text-gray-700"></i>
                            </button>

                            <button v-if="authUser.profile" @click="removeImage('profile')"
                                class="p-2 bg-white rounded-full shadow-md hover:bg-gray-100 transition-all"
                                :disabled="isLoading" :title="frontLang?.lang?.removeProfile || 'Remove profile'">
                                <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-trash-alt text-red-500"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <input type="file" ref="profileInput" class="hidden" accept="image/*"
                    @change="onFileChange('profile', $event)">
                <input type="file" ref="bgInput" class="hidden" accept="image/*"
                    @change="onFileChange('bg_image', $event)">
            </div>

            <!-- Profile Form -->
            <div class="px-6 py-8">
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Success/Error Message -->
                    <div v-if="message || errors.general" :class="{
                        'bg-green-50 text-green-800': message,
                        'bg-red-50 text-red-800': errors.general
                    }" class="p-4 rounded-md">
                        <p>{{ message || errors.general }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ frontLang?.lang?.fullName || 'Full Name' }}
                            </label>
                            <input v-model="form.full_name" type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-lime-500 focus:border-lime-500">
                            <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">
                                {{ errors.first_name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ frontLang?.lang?.email || 'Email' }}
                            </label>
                            <input v-model="form.email" type="email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-lime-500 focus:border-lime-500">
                            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                                {{ errors.email }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ frontLang?.lang?.phone || 'Phone' }}
                            </label>
                            <input v-model="form.phone" type="tel"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-lime-500 focus:border-lime-500">
                            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">
                                {{ errors.phone }}
                            </p>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ frontLang?.lang?.gender || 'Gender' }}
                            </label>
                            <select v-model="form.gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-lime-500 focus:border-lime-500">
                                <option value="">{{ frontLang?.lang?.selectGender || 'Select gender' }}</option>
                                <option value="1">{{ frontLang?.lang?.male || 'Male' }}</option>
                                <option value="2">{{ frontLang?.lang?.female || 'Female' }}</option>
                            </select>
                            <p v-if="errors.gender" class="mt-1 text-sm text-red-600">
                                {{ errors.gender }}
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transition-colors"
                            :disabled="isLoading">
                            <span v-if="isLoading">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                {{ frontLang?.lang?.updating || 'Updating...' }}
                            </span>
                            <span v-else>
                                {{ frontLang?.lang?.updateProfile || 'Update Profile' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions for hover effects */
button,
input,
select {
    transition: all 0.2s ease;
}

/* Better focus styles */
input:focus,
select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(101, 163, 13, 0.2);
}

/* Animation for loading spinner */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.fa-spin {
    animation: spin 1s linear infinite;
}
</style>