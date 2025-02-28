<template>
    <div
        class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-6"
    >
        <!-- Add Plan Form -->
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-lg">
            <h1
                class="text-3xl font-extrabold text-lime-700 mb-6 text-center flex items-center justify-center gap-2"
            >
                Add New Plan
            </h1>

            <form @submit.prevent="validateAndSubmit" class="space-y-4">
                <!-- Name Field -->
                <div>
                    <label class="block text-gray-700 font-medium">Name</label>
                    <input
                        v-model="newPlan.name"
                        type="text"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                        placeholder="Enter plan name"
                    />
                    <p v-if="errors.name" class="text-red-600 text-sm mt-1">
                        {{ errors.name }}
                    </p>
                </div>

                <!-- Price Field -->
                <div>
                    <label class="block text-gray-700 font-medium">
                        Price (in Birr)
                    </label>
                    <input
                        v-model="newPlan.price"
                        type="number"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                        placeholder="Enter price"
                    />
                    <p v-if="errors.price" class="text-red-600 text-sm mt-1">
                        {{ errors.price }}
                    </p>
                </div>

                <!-- Duration Field -->
                <div>
                    <label class="block text-gray-700 font-medium">
                        Duration (in months)
                    </label>
                    <input
                        v-model="newPlan.duration"
                        type="number"
                        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                        placeholder="Enter duration"
                    />
                    <p v-if="errors.duration" class="text-red-600 text-sm mt-1">
                        {{ errors.duration }}
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center">
                    <button
                        type="submit"
                        class="bg-lime-700 text-white px-6 py-2 rounded-lg hover:bg-lime-600 flex items-center justify-center gap-2"
                    >
                        Add Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification"; // Import Vue Toastification

const toast = useToast(); // Initialize toast

// Form Data
const newPlan = ref({
    name: "",
    price: "",
    duration: "",
});

// Validation Errors
const errors = ref({});

// Function to validate and submit form
const validateAndSubmit = async () => {
    // Reset errors
    errors.value = {};

    // Validation Rules
    if (!newPlan.value.name.trim()) {
        errors.value.name = "Plan name is required.";
    }
    if (!newPlan.value.price) {
        errors.value.price = "Price is required.";
    } else if (newPlan.value.price <= 0) {
        errors.value.price = "Price must be a positive number.";
    }
    if (!newPlan.value.duration) {
        errors.value.duration = "Duration is required.";
    } else if (
        newPlan.value.duration <= 0 ||
        !Number.isInteger(Number(newPlan.value.duration))
    ) {
        errors.value.duration = "Duration must be a positive whole number.";
    }

    // If any errors exist, display error toast and stop submission
    if (Object.keys(errors.value).length > 0) {
        toast.error("Please fix validation errors!", { position: "top-right" });
        return;
    }

    // Submit the form if no errors
    try {
        await axios.post("/api/plans", newPlan.value);
        toast.success(" Plan added successfully!", { position: "top-right" });
        // Reset form data
        newPlan.value = { name: "", price: "", duration: "" };
    } catch (err) {
        console.error("Error adding plan:", err);
        toast.error("Failed to add plan. Please try again.", {
            position: "top-right",
        });
    }
};
</script>

<style scoped>
/* Smooth transition effects */
input {
    transition: all 0.3s ease-in-out;
}

input:focus {
    border-color: #84cc16;
    box-shadow: 0 0 10px rgba(132, 204, 22, 0.2);
}
</style>
