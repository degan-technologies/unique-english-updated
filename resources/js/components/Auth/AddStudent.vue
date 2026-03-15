<script>
import { useAppStore } from "@/store/useAppStore";
import Axios from "axios";
import { storeToRefs } from "pinia";
import { ref } from "vue";

export default {
    setup() {
        const appStore = useAppStore();
        const { frontLang } = storeToRefs(appStore);
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
        const strongPasswordPattern =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;

        const clearMessages = () => {
            setTimeout(() => {
                message.value = "";
                errorMessage.value = "";
                errors.value = {}; // Clear all errors after 2000ms (2 seconds)
            }, 2000);
        };

        const validateForm = () => {
            errors.value = {};

            if (!form.value.first_name.trim()) {
                errors.value.first_name = "First name is required";
            } else if (!/^[a-zA-Z0-9_-]+$/.test(form.value.first_name)) {
                errors.value.first_name = "Invalid characters in first name";
            }

            if (
                form.value.middle_name &&
                !/^[a-zA-Z0-9_-]+$/.test(form.value.middle_name)
            ) {
                errors.value.middle_name = "Invalid characters in middle name";
            }

            if (!form.value.email.trim()) {
                errors.value.email = "Email is required";
            } else if (!/\S+@\S+\.\S+/.test(form.value.email)) {
                errors.value.email = "Invalid email format";
            }

            if (!form.value.password.trim()) {
                errors.value.password = "Password is required";
            } else if (!strongPasswordPattern.test(form.value.password)) {
                errors.value.password =
                    "Password must be at least 8 characters and include uppercase, lowercase, number, and special character";
            }

            return Object.keys(errors.value).length === 0;
        };

        const submitForm = async () => {
            if (!validateForm()) {
                return;
            }

            loading.value = true;
            message.value = "";
            errorMessage.value = "";

            try {
                const response = await Axios.post(
                    "/api/add-student",
                    form.value,
                );
                message.value = response.data.message;
                clearMessages(); // Clear message after 2000ms
                form.value = {
                    first_name: "",
                    middle_name: "",
                    email: "",
                    password: "",
                }; // Reset form
            } catch (error) {
                if (error.response && error.response.data.errors) {
                    errorMessage.value = Object.values(
                        error.response.data.errors,
                    )[0][0];
                } else {
                    errorMessage.value = "Something went wrong!";
                }
                clearMessages(); // Clear error message and errors after 2000ms
            }

            loading.value = false;
        };

        return {
            form,
            errors,
            loading,
            message,
            errorMessage,
            submitForm,
            frontLang,
        };
    },
};
</script>
<template>
    <div
        class="flex justify-center items-center min-h-screen bg-white-to-br from-white-100 to-white-300 px-4"
    >
        <div class="w-full max-w-lg bg-white p-6 rounded-2xl shadow-xl">
            <!-- Header -->
            <h6 class="text-xl font-extrabold text-gray-800 text-center mb-6">
                {{ frontLang.lang.register_student }}
            </h6>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-4">
                <!-- First Name -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1"
                        >First Name</label
                    >
                    <input
                        required
                        type="text"
                        v-model="form.first_name"
                        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                    <span
                        class="text-red-500 text-sm"
                        v-if="errors.first_name"
                        >{{ errors.first_name }}</span
                    >
                </div>

                <!-- Middle Name -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1"
                        >Middle Name</label
                    >
                    <input
                        required
                        type="text"
                        v-model="form.middle_name"
                        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                    <span
                        class="text-red-500 text-sm"
                        v-if="errors.middle_name"
                        >{{ errors.middle_name }}</span
                    >
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1"
                        >Email</label
                    >
                    <input
                        required
                        type="email"
                        v-model="form.email"
                        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                    <span class="text-red-500 text-sm" v-if="errors.email">{{
                        errors.email
                    }}</span>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1"
                        >Password</label
                    >
                    <input
                        required
                        type="password"
                        v-model="form.password"
                        class="w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                    <span class="text-red-500 text-sm" v-if="errors.password">{{
                        errors.password
                    }}</span>
                    <p class="text-gray-500 text-xs mt-1">
                        Use at least 8 characters with uppercase, lowercase,
                        number, and special character.
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full bg-blue-500 text-white py-3 rounded-lg text-lg font-semibold shadow-md hover:bg-blue-600 transition-all duration-300"
                    :disabled="loading"
                >
                    {{ loading ? "Registering..." : "Register Student" }}
                </button>
            </form>

            <!-- Success & Error Messages -->
            <transition name="fade">
                <p
                    v-if="message"
                    class="mt-4 text-green-600 font-semibold text-center"
                >
                    {{ message }}
                </p>
            </transition>
            <transition name="fade">
                <p
                    v-if="errorMessage"
                    class="mt-4 text-red-500 font-semibold text-center"
                >
                    {{ errorMessage }}
                </p>
            </transition>
        </div>
    </div>
</template>

<style scoped>
/* Fade animation for messages */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease-in-out;
}
.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>
