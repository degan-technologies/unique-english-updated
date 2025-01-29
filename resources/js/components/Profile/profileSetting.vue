        <script setup>
        import { ref, onMounted } from 'vue';
        import { storeToRefs } from 'pinia';
        import { useAppStore } from '@/store/useAppStore';
        import Axios from 'axios';

        // Access the app store
        const appStore = useAppStore();
        const { authUser, frontLang } = storeToRefs(appStore); // Destructure reactive properties
        const fetchUserInfo = appStore.fetchUserInfo; // Access methods directly

        // Form data and reactive states
        const form = ref({
            first_name: '',
            middle_name: '',
            last_name: '',
            email: '',
            phone: '',
            gender: '',
            profile: '',
            bg_image: '',
        });

        const errors = ref({});
        const message = ref('');

        // Fetch user profile on component mount
        onMounted(async () => {
            await fetchProfile();
        });

        // Fetch user profile data
        const fetchProfile = async () => {
        try {
        await fetchUserInfo(); // Call the store function directly
        console.log('authUser:', JSON.stringify(authUser.value, null, 2)); // Logs the full authUser data with proper indentation

        if (authUser.value) {
            form.value.first_name = authUser.value.first_name || '';
            form.value.middle_name = authUser.value.middle_name || '';
            form.value.last_name = authUser.value.last_name || '';
            form.value.email = authUser.value.email || '';
            form.value.phone = authUser.value.phone || '';
            form.value.gender = authUser.value.gender || '';
            form.value.profile = authUser.value.profile || '';
            form.value.bg_image = authUser.value.bg_image || '';
        } else {
            console.warn('authUser is null or undefined.');
        }
        } catch (error) {
        console.error('Failed to fetch profile:', error);
        showAlertAndClearMessage('Error fetching user data.');
        }
        };

        const submitForm = async (event) => {
        event.preventDefault();

        errors.value = {};
        message.value = '';

        // Create FormData object
        const formData = new FormData();

        // Collect form data from form.value
        formData.append('email', form.value.email);
        formData.append('first_name', form.value.first_name);
        formData.append('middle_name', form.value.middle_name);
        formData.append('last_name', form.value.last_name);
        formData.append('phone', form.value.phone);
        formData.append('gender', form.value.gender);

        // Append files only if they are selected
        if (form.value.profile) {
        formData.append('profile', form.value.profile);
        }
        if (form.value.bg_image) {
        formData.append('bg_image', form.value.bg_image);
        }

        try {
        const response = await Axios.post('/api/update-profile', formData, {
            headers: {
                'Content-Type': 'multipart/form-data', // Required for file uploads
            },
        });

        if (response.status === 200) {
            // Show success message in the UI
            message.value = response.data.message;
            clearMessageAfterDelay(); // Clear message after 2 seconds
        }
        } catch (error) {
        console.error("Failed to update profile:", error);

        if (error.response) {
            // Handle validation errors from the backend
            if (error.response.data.errors) {
                errors.value = error.response.data.errors;
            }
            // Show a generic error message
            message.value = "Failed to update profile. Please try again.";
        } else {
            message.value = "An unexpected error occurred. Please try again later.";
        }
        showAlertAndClearMessage(); // Clear message after 2 seconds
        }
        };

        // Helper method to clear the message after a delay
        const showAlertAndClearMessage = (message) => {
        alert(message); // Display the alert

        // Clear the message after 2 seconds
        setTimeout(() => {
        console.log('Alert cleared and message cleared after 2 seconds');
        message.value = ''; // Assuming message is a reactive variable in your Vue app
        }, 2000); // Delay of 2 seconds
        };

        const onFileChange = (field, event) => {
            const file = event.target.files[0];
            if (file) {
                form.value[field] = file;
            }
        };
        </script>

    <template>
        <div class="flex justify-center items-center min-h-screen bg-gray-100 p-4">
        <div class="w-full max-w-2xl bg-white rounded-lg shadow-lg p-6 space-y-6">
            <!-- Header -->
            <h1 class="text-2xl font-bold text-gray-800 text-center">{{frontLang.lang.updateProfile}}</h1>
    
            <!-- Profile and Background Images -->
            <div class="flex flex-col md:flex-row md:space-x-6 space-y-4 md:space-y-0">
            <!-- Profile Image -->
            <div class="flex flex-col items-center space-y-2 w-full md:w-1/2">
                <label class="block text-gray-700 font-medium">{{frontLang.lang.profile}}</label>
                <div v-if="authUser.profile">
                <img
                    :src="authUser.profile"
                    alt="Profile Image"
                    class="w-24 h-24 rounded-full border"
                />
                </div>
                <input
                type="file"
                @change="onFileChange('profile', $event)"
                class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-red-500 text-sm" v-if="errors.profile">{{ errors.profile }}</span>
            </div>
    
            <!-- Background Image -->
            <div class="flex flex-col items-center space-y-2 w-full md:w-1/2">
                <label class="block text-gray-700 font-medium">{{frontLang.lang.backgroundImage}}</label>
                <div v-if="form.bg_image">
                <img
                    :src="form.bg_image"
                    alt="Background Image"
                    class="w-full max-h-24 object-cover border rounded-lg"
                />
                </div>
                <input
                type="file"
                @change="onFileChange('bg_image', $event)"
                class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-red-500 text-sm" v-if="errors.bg_image">{{ errors.bg_image }}</span>
            </div>
            </div>
    
            <!-- Form Fields -->
            <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- First Name -->
                <div>
                <label class="block text-gray-700 font-medium mb-1">{{frontLang.lang.firstName}}:</label>
                <input
                    v-model="form.first_name"
                    type="text"
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-red-500 text-sm" v-if="errors.first_name">{{ errors.first_name }}</span>
                </div>
    
                <!-- Middle Name -->
                <div>
                <label class="block text-gray-700 font-medium mb-1">{{frontLang.lang.middleName}}:</label>
                <input
                    v-model="form.middle_name"
                    type="text"
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-red-500 text-sm" v-if="errors.middle_name">{{ errors.middle_name }}</span>
                </div>
    
                <!-- Last Name -->
                <div>
                <label class="block text-gray-700 font-medium mb-1">{{frontLang.lang.lastName}}:</label>
                <input
                    v-model="form.last_name"
                    type="text"
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-red-500 text-sm" v-if="errors.last_name">{{ errors.last_name }}</span>
                </div>
    
                <!-- Email -->
                <div>
                <label class="block text-gray-700 font-medium mb-1">{{frontLang.lang.email}}:</label>
                <input
                    v-model="form.email"
                    type="email"
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-red-500 text-sm" v-if="errors.email">{{ errors.email }}</span>
                </div>
    
                <!-- Phone -->
                <div>
                <label class="block text-gray-700 font-medium mb-1">{{frontLang.lang.phone}}:</label>
                <input
                    v-model="form.phone"
                    type="text"
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-red-500 text-sm" v-if="errors.phone">{{ errors.phone }}</span>
                </div>
    
                <!-- Gender -->
                <div>
                <label class="block text-gray-700 font-medium mb-1">{{frontLang.lang.gender}}:</label>
                <select
                    v-model="form.gender"
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">{{frontLang.lang.selectGender}}</option>
                    <option value="1">{{frontLang.lang.male}}</option>
                    <option value="2">{{frontLang.lang.female}}</option>
                </select>
                <span class="text-red-500 text-sm" v-if="errors.gender">{{ errors.gender }}</span>
                </div>
            </div>
    
            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-600 transition"
            >
            {{frontLang.lang.UpdateProfile}}
            </button>
    
            <!-- Success Message -->
            <p
                v-if="message"
                class="text-green-500 text-center text-sm font-medium mt-2"
            >
                {{ message }}
            </p>
            </form>
        </div>
        </div>
    </template>
            

        
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
        