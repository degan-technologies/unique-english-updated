<script setup>
    import Axios from 'axios';
    import { storeToRefs } from 'pinia';
    import { ref, onMounted, computed } from 'vue';

    import { useAppStore } from '@/store/useAppStore';
    import Spinner from '@/components/Layout/Spinner.vue';
    const appStore = useAppStore();
    const { authUser, frontLang } = storeToRefs(appStore);
    const fetchUserInfo = appStore.fetchUserInfo;


    const form = ref({ 
        first_name: '',
        email: '',
        phone: '',
        gender: '',
        profile: null, 
        bg_image: null, 
    });

    const originalData = ref({});
    const errors = ref({});
    const message = ref('');

    const profileInput = ref(null);
    const bgInput = ref(null);
    const isDataLoaded = ref(false);
    const hasFile = ref(false);

    function onFileChange(field, event) {
        const file = event.target.files[0];

        if (file) {
            form.value[field] = file;
            hasFile.value = true;

            authUser.value[field] = URL.createObjectURL(file);
        }
    };

    function triggerFileInput(field) {
        if (field === 'profile' && profileInput.value) {
        profileInput.value.click();
        } else if (field === 'bg_image' && bgInput.value) {
        bgInput.value.click();
        }
    };

    function fetchProfile() {
        fetchUserInfo();

        if (authUser.value) {
        originalData.value = { ...authUser.value };
        form.value = { ...authUser.value };
        }
    };

    async function removeImage(field) {
        const formData = new FormData(); 

        formData.append('field', field);

        Axios
            .post('/api/remove-image', formData )
            .then(res => {
                authUser.value = res.data.data;  
                message.value = res.data.message; 
                form.value = { ...authUser.value };  
                setTimeout(() => (message.value = ''), 2000);
            })
            .catch(error => {
                errors.value = error.response?.data?.errors;
            });
    }


    function updateProfileImage(field) {
        const formData = new FormData();

        if(!hasFile.value)  return;

        formData.append(field, form.value[field]);
        
        Axios
            .post('/api/profile-image/update', formData )
            .then(res => {
                authUser.value = res.data.data;  
                message.value = res.data.message; 
                form.value = { ...authUser.value };  
                setTimeout(() => (message.value = ''), 2000);
            })
            .catch(error => {
                errors.value = error.response?.data?.errors;
            });
    }

    function submitForm() { 
        errors.value = {};
        message.value = '';

        const formData = new FormData();

        formData.append('full_name', form.value.full_name); 
        formData.append('email', form.value.email);
        formData.append('phone', form.value.phone);
        formData.append('gender', form.value.gender);

        Axios
            .post('/api/update-profile', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            .then(res => {
                authUser.value = res.data.data;  
                message.value = res.data.message; 
                form.value = { ...authUser.value };  
                setTimeout(() => (message.value = ''), 2000);
            })
            .catch(error => {
                errors.value = error.response?.data?.errors || { general: error.response?.data?.message || 'An error occurred' };
            });
    }

// Fetch frontLang and set data loaded status
onMounted(async () => {
  try {
    await appStore.fetchFrontLanguages();  // Assuming this fetches frontLang
    isDataLoaded.value = true; // Data is loaded
  } catch (error) {
    console.error("Failed to fetch frontLang:", error);
  }
  fetchProfile();
});
    
</script>

<template>
    <div v-if="!isDataLoaded" class="flex items-center justify-center min-h-screen">
        <Spinner />
    </div>
    <div v-else class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-2xl bg-white rounded-lg p-4 space-y-8">
            <!-- Use optional chaining to safely access lang property -->
            <h1 v-if="frontLang?.lang" class="text-3xl font-bold text-center text-lime-700"> 
                {{ frontLang?.lang?.updateProfile }} 
            </h1>
            
            <!-- Background Image Container -->
            <div class="relative w-full h-64 rounded-lg overflow-hidden border border-gray-200 shadow-md">
                <div
                    v-if="authUser.bg_image"
                    :style="{ backgroundImage: `url(${ authUser.bg_image})` }"
                    class="absolute inset-0 bg-cover bg-center">
                </div>
                <!-- Group for Background Image Action -->
                <div class="absolute top-4 right-4 z-10">
                    <div class="relative group flex flex-col justify-end items-end">
                        <!-- Camera Button (always visible) -->
                        <button
                            class="bg-black bg-opacity-50 text-white p-2  w-10 h-10 rounded-full hover:bg-opacity-75 transition"
                            @click="triggerFileInput('bg_image')"
                            title="Change Background"
                        >
                            <i class="fas fa-camera"></i>
                        </button>
                        <!-- Cancel (Remove) Button (appears below on hover) -->
                        <button
                            v-if="authUser.bg_image"
                            @click="removeImage('bg_image')"
                            class=" transform  w-10 h-10 top-full mt-2 bg-red-500 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition duration-200"
                            title="Remove Background"
                        >
                            <i class="fas fa-trash-alt"></i>
                        </button>

                        <button
                            v-if="authUser.bg_image"
                            @click="updateProfileImage('bg_image')"
                            class=" transform  w-10 h-10 top-full mt-2 bg-green-500 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition duration-200"
                            title="save Background"
                        >
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                </div>

                <div class="relative flex items-center justify-center h-full">
                    <!-- Profile Image Container -->
                    <div class="absolute bottom-0 transform">
                        <div class="relative w-32 h-32 rounded-full border-4 border-white overflow-hidden shadow-lg">
                            <img
                                v-if="authUser.profile"
                                :src="authUser.profile || 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y'"
                                alt="Profile Image"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center bg-gray-300 text-gray-700"
                            >
                                No Image
                            </div>
                            <!-- Group for Profile Image Action -->
                            <div class="absolute bottom-0 right-0 z-10">
                                <div class="relative group">
                                    <!-- Camera Button (always visible) -->
                                    <button
                                        class="bg-black bg-opacity-50 text-white p-2 rounded-full w-10 h-10 hover:bg-opacity-75 transition"
                                        @click="triggerFileInput('profile')"
                                        title="Change Profile"
                                    >
                                        <i class="fas fa-camera"></i>
                                    </button>
                                    <!-- Cancel (Remove) Button (appears above on hover) -->
                                    <button
                                        v-if="authUser.profile"
                                        @click="removeImage('profile')"
                                        class=" transform w-10 h-10 bottom-full mb-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition duration-200"
                                        title="Remove Profile" >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button
                                        v-if="authUser.profile"
                                        @click="updateProfileImage('profile')"
                                        class=" transform w-10 h-10 bottom-full mb-2 bg-green-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition duration-200"
                                        title="Save Profile"
                                    >
                                        <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input
                type="file"
                accept="image/*"
                capture="user"
                ref="profileInput"
                class="hidden"
                @change="onFileChange('profile', $event)"
            />
            <input
                type="file"
                accept="image/*"
                capture="environment"
                ref="bgInput"
                class="hidden"
                @change="onFileChange('bg_image', $event)"
            />

            <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label v-if="frontLang?.lang" class="block text-gray-700 font-medium mb-1"> 
                            Full Name 
                        </label>
                        <input
                            v-model="form.full_name"
                            type="text"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
                        />
                        <span v-if="errors.first_name" class="text-red-500 text-sm"> {{ errors.first_name }} </span>
                    </div>  
                    <div>
                        <label class="block text-gray-700 font-medium mb-1"> 
                            {{ frontLang?.lang?.email }}: 
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
                        />
                        <span v-if="errors.email" class="text-red-500 text-sm"> {{ errors.email }} </span>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1"> 
                            {{ frontLang?.lang?.phone }}: 
                        </label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
                        />
                        <span v-if="errors.phone" class="text-red-500 text-sm"> {{ errors.phone }} </span>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1"> 
                            {{ frontLang?.lang?.gender }}: 
                        </label>
                        <select
                            v-model="form.gender"
                            class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-700"
                        >
                            <option value="">{{ frontLang?.lang?.selectGender }}</option>
                            <option value="1">{{ frontLang?.lang?.male }}</option>
                            <option value="2">{{ frontLang?.lang?.female }}</option>
                        </select>
                        <span v-if="errors.gender" class="text-red-500 text-sm"> {{ errors.gender }} </span>
                    </div>
                </div>
                <button
                    type="submit"
                    @click="submitForm()"
                    class="w-full bg-lime-700 text-white p-3 rounded-lg hover:bg-lime-800 transition flex justify-center"
                >
                    {{ frontLang?.lang?.updateProfile }}
                </button>
                <p v-if="message" class="text-center text-lime-700 text-sm font-medium mt-3"> {{ message }} </p>
            </form>
        </div>
    </div>
</template>


<style>
    .error {
    color: red;
    font-size: 0.9rem;
    }

    .highlight {
    background-color: yellow;
    }
</style>
