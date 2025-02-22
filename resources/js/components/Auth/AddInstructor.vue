                <script>
                import Axios from "axios";
                import { ref } from "vue";

                export default {
                setup() {
                const form = ref({
                    first_name: "",
                    middle_name: "",
                    email: "",
                    password: "",
                });

                const errors = ref({});
                const loading = ref(false);
                const message = ref("");
                const errorMessage = ref("");

                const clearMessages = () => {
                    setTimeout(() => {
                    message.value = "";
                    errorMessage.value = "";
                    errors.value = {}; // Clear all errors after 2000ms (2 seconds)
                    }, 2000);
                };

                // const validateForm = () => {
                //     errors.value = {}; // Reset errors

                //     if (!form.value.first_name.trim()) {
                //     errors.value.first_name = "First name is required";
                //     } else if (!/^[a-zA-Z0-9_-]+$/.test(form.value.first_name)) {
                //     errors.value.first_name = "Invalid characters in first name";
                //     }

                //     if (form.value.middle_name && !/^[a-zA-Z0-9_-]+$/.test(form.value.middle_name)) {
                //     errors.value.middle_name = "Invalid characters in middle name";
                //     }

                //     if (!form.value.email.trim()) {
                //     errors.value.email = "Email is required";
                //     } else if (!/\S+@\S+\.\S+/.test(form.value.email)) {
                //     errors.value.email = "Invalid email format";
                //     }

                //     if (!form.value.password.trim()) {
                //     errors.value.password = "Password is required";
                //     } else if (form.value.password.length < 4) {
                //     errors.value.password = "Password must be at least 4 characters";
                //     }

                //     return Object.keys(errors.value).length === 0;
                // };

                const submitForm = async () => {
                  
                    loading.value = true;
                    message.value = "";
                    errorMessage.value = "";

                    try {
                    const response = await Axios.post("/api/add-instructor", form.value);
                    message.value = response.data.message;
                    clearMessages(); // Clear message after 2000ms
                    form.value = { first_name: "", middle_name: "", email: "", password: "" }; // Reset form
                    } catch (error) {
                    if (error.response && error.response.data.errors) {
                        errorMessage.value = Object.values(error.response.data.errors)[0][0];
                    } else {
                        errorMessage.value = "Something went wrong!";
                    }
                    clearMessages(); // Clear error message and errors after 2000ms
                    }

                    loading.value = false;
                };

                return { form, errors, loading, message, errorMessage, submitForm };
                },
                };
                </script>
            <template>
                <div class="flex justify-center items-center min-h-screen bg-gradient-to-br from-blue-100 to-blue-300 px-4">
                <div class="w-full max-w-lg bg-white p-6 rounded-2xl shadow-xl">
                    <!-- Header -->
                    <h6 class="text-xl font-extrabold text-gray-800 text-center mb-6">Register Instructor</h6>
            
                    <!-- Form -->
                    <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- First Name -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">First Name</label>
                        <input
                        required
                        type="text"
                        v-model="form.first_name"
                        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                        <span class="text-red-500 text-sm" v-if="errors.first_name">{{ errors.first_name }}</span>
                    </div>
            
                    <!-- Middle Name -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Middle Name</label>
                        <input
                        required
                        type="text"
                        v-model="form.middle_name"
                        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                        <span class="text-red-500 text-sm" v-if="errors.middle_name">{{ errors.middle_name }}</span>
                    </div>
            
                    <!-- Email -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Email</label>
                        <input
                        required
                        type="email"
                        v-model="form.email"
                        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                        <span class="text-red-500 text-sm" v-if="errors.email">{{ errors.email }}</span>
                    </div>
            
                    <!-- Password -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Password</label>
                        <input
                        required
                        type="password"
                        v-model="form.password"
                        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                        <span class="text-red-500 text-sm" v-if="errors.password">{{ errors.password }}</span>
                    </div>
            
                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full bg-blue-500 text-white py-3 rounded-lg text-lg font-semibold shadow-md hover:bg-blue-600 transition-all duration-300"
                        :disabled="loading"
                    >
                        {{ loading ? "Registering..." : "Register Instructor" }}
                    </button>
                    </form>
            
                    <!-- Success & Error Messages -->
                    <transition name="fade">
                    <p v-if="message" class="mt-4 text-green-600 font-semibold text-center">{{ message }}</p>
                    </transition>
                    <transition name="fade">
                    <p v-if="errorMessage" class="mt-4 text-red-500 font-semibold text-center">{{ errorMessage }}</p>
                    </transition>
                </div>
                </div>
            </template>


        <style scoped>
        /* Fade animation for messages */
        .fade-enter-active, .fade-leave-active {
        transition: opacity 0.5s ease-in-out;
        }
        .fade-enter, .fade-leave-to {
        opacity: 0;
        }
        </style>