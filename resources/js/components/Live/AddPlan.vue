<template>
    <div class="max-w-4xl mx-auto p-6 md:p-8 bg-white rounded-lg shadow-md">
        <h2
            class="text-2xl md:text-3xl font-bold text-center text-lime-700 mb-6"
        >
            Add Subscription Plan
        </h2>

        <!-- Add Plan Form -->
        <form @submit.prevent="addPlan" class="grid grid-cols-1 gap-4">
            <!-- Plan Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-700"
                    >Plan Name</label
                >
                <input
                    v-model="planForm.name"
                    type="text"
                    placeholder="Enter plan name"
                    class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                />
                <p v-if="errors.name" class="text-red-600 text-sm mt-1">
                    {{ errors.name }}
                </p>
            </div>

            <!-- Price -->
            <div>
                <label class="block text-sm font-semibold text-gray-700"
                    >Price (Birr)</label
                >
                <input
                    v-model="planForm.price"
                    type="number"
                    placeholder="Enter price"
                    class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                />
                <p v-if="errors.price" class="text-red-600 text-sm mt-1">
                    {{ errors.price }}
                </p>
            </div>

            <!-- Duration -->
            <div>
                <label class="block text-sm font-semibold text-gray-700"
                    >Duration</label
                >
                <select
                    v-model="planForm.duration"
                    class="w-full mt-2 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-500"
                >
                    <option value="">Select duration</option>
                    <option value="1 Month">1 Month</option>
                    <option value="3 Months">3 Months</option>
                    <option value="6 Months">6 Months</option>
                </select>
                <p v-if="errors.duration" class="text-red-600 text-sm mt-1">
                    {{ errors.duration }}
                </p>
            </div>

            <!-- Add Button -->
            <button
                type="submit"
                class="w-full bg-lime-700 hover:bg-lime-800 text-white font-medium px-4 py-3 rounded-lg shadow-lg transition-all duration-300"
            >
                Add Plan
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from "vue";

const planForm = ref({
    name: "",
    price: "",
    duration: "",
});

const errors = ref({});

const addPlan = () => {
    errors.value = {};

    if (!planForm.value.name) {
        errors.value.name = "Plan name is required.";
    }
    if (!planForm.value.price) {
        errors.value.price = "Price is required.";
    } else if (planForm.value.price <= 0) {
        errors.value.price = "Price must be greater than zero.";
    }
    if (!planForm.value.duration) {
        errors.value.duration = "Duration is required.";
    }

    if (Object.keys(errors.value).length === 0) {
        alert("Plan added successfully!");
        planForm.value = { name: "", price: "", duration: "" };
    }
};
</script>
