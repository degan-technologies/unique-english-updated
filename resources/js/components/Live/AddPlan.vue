<script setup>
import Axios from "axios";
import { ref, defineEmits } from "vue";
import { useToast } from "vue-toastification";

const toast = useToast();
const emit = defineEmits(["close", "plan-added"]);

const newPlan = ref({
    name: "",
    one_to_one_price: "",
    group_price: "",
});

const errors = ref({});
const isSubmitting = ref(false);

const validateAndSubmit = async () => {
    // Reset errors
    errors.value = {};

    // Validate name
    if (!newPlan.value.name.trim()) {
        errors.value.name = "Plan name is required.";
    }

    // Validate one-to-one price
    if (!newPlan.value.one_to_one_price) {
        errors.value.one_to_one_price = "One to one price is required.";
    } else if (newPlan.value.one_to_one_price <= 0) {
        errors.value.one_to_one_price = "Price must be a positive number.";
    } else if (isNaN(newPlan.value.one_to_one_price)) {
        errors.value.one_to_one_price = "Please enter a valid number.";
    }

    // Validate group price
    if (!newPlan.value.group_price) {
        errors.value.group_price = "Group price is required.";
    } else if (newPlan.value.group_price <= 0) {
        errors.value.group_price = "Price must be a positive number.";
    } else if (isNaN(newPlan.value.group_price)) {
        errors.value.group_price = "Please enter a valid number.";
    }

    // Check if there are errors
    if (Object.keys(errors.value).length > 0) {
        toast.error("Please fix the validation errors", {
            position: "top-right",
            timeout: 3000
        });
        return;
    }

    // Submit the form
    isSubmitting.value = true;
    try {
        const response = await Axios.post("/api/plans", {
            ...newPlan.value,
            one_to_one_price: parseFloat(newPlan.value.one_to_one_price),
            group_price: parseFloat(newPlan.value.group_price)
        });

        toast.success("Plan added successfully!", {
            position: "top-right",
            timeout: 3000
        });

        // Reset form
        newPlan.value = {
            name: "",
            one_to_one_price: "",
            group_price: "",
        };

        // Emit events
        emit("plan-added");
        emit("close");
    } catch (err) {
        console.error("Error adding plan:", err);

        let errorMessage = "Failed to add plan. Please try again.";
        if (err.response?.data?.message) {
            errorMessage = err.response.data.message;
        }

        toast.error(errorMessage, {
            position: "top-right",
            timeout: 3000
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div class="fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <!-- Modal Header -->
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">
                    <span class="text-lime-600">Create New Plan</span>
                </h3>
                <button @click="emit('close')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4">
                <form @submit.prevent="validateAndSubmit" class="space-y-4">
                    <!-- Plan Name -->
                    <div>
                        <label for="plan-name" class="block text-sm font-medium text-gray-700 mb-1">
                            Plan Name <span class="text-red-500">*</span>
                        </label>
                        <input id="plan-name" v-model="newPlan.name" type="text" :class="{
                            'border-red-300 focus:ring-red-500 focus:border-red-500': errors.name,
                            'border-gray-300 focus:ring-lime-500 focus:border-lime-500': !errors.name
                        }" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500" placeholder="e.g. Premium Plan" />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                            {{ errors.name }}
                        </p>
                    </div>

                    <!-- Prices Section -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Prices (ETB) <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- One to One Price -->
                            <div>
                                <label for="one-to-one" class="block text-xs font-medium text-gray-500 mb-1">
                                    One to One Price
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    </div>
                                    <input id="one-to-one" v-model="newPlan.one_to_one_price" type="number" min="0"
                                        step="0.01" :class="{
                                            'border-red-300 focus:ring-red-500 focus:border-red-500': errors.one_to_one_price,
                                            'border-gray-300 focus:ring-lime-500 focus:border-lime-500': !errors.one_to_one_price
                                        }" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                                        placeholder="0.00" />
                                </div>
                                <p v-if="errors.one_to_one_price" class="mt-1 text-sm text-red-600">
                                    {{ errors.one_to_one_price }}
                                </p>
                            </div>

                            <!-- Group Price -->
                            <div>
                                <label for="group-price" class="block text-xs font-medium text-gray-500 mb-1">
                                    Group Price
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    </div>
                                    <input id="group-price" v-model="newPlan.group_price" type="number" min="0"
                                        step="0.01" :class="{
                                            'border-red-300 focus:ring-red-500 focus:border-red-500': errors.group_price,
                                            'border-gray-300 focus:ring-lime-500 focus:border-lime-500': !errors.group_price
                                        }" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-lime-500"
                                        placeholder="0.00" />
                                </div>
                                <p v-if="errors.group_price" class="mt-1 text-sm text-red-600">
                                    {{ errors.group_price }}
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="border-t border-gray-200 px-6 py-4 flex justify-end space-x-3">
                <button type="button" @click="emit('close')"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
                    Cancel
                </button>
                <button type="submit" @click="validateAndSubmit" :disabled="isSubmitting"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span v-if="!isSubmitting">Create Plan</span>
                    <span v-else class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Creating...
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Input focus styles */
input:focus {
    box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.2);
    transition: all 0.2s ease;
}

/* Button hover effects */
button:not(:disabled):hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* Spinner animation */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>